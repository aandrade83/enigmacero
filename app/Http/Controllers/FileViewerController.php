<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileViewerController extends Controller
{
    public function index()
    {
        $clients = Client::orderBy('name')->get();
        $canDelete = auth()->check() && auth()->user()->role === 'admin';

        return view('files.index', compact('clients', 'canDelete'));
    }

    // GET /files/folders?client_id=6
    public function folders(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
        ]);

        $client = Client::findOrFail($request->integer('client_id'));

        if (empty($client->bucket_folder)) {
            return response()->json([
                'folders' => [],
                'client_folder' => null,
            ]);
        }

        $dirs = Storage::disk('gcs')->directories($client->bucket_folder);

        $folders = collect($dirs)
            ->map(function ($dir) use ($client) {
                return trim(Str::after($dir, rtrim($client->bucket_folder, '/') . '/'), '/');
            })
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return response()->json([
            'folders' => $folders,
            'client_folder' => $client->bucket_folder,
        ]);
    }

    // GET /files/list?client_id=6&folder=DIC_2025
    public function list(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'folder'    => ['required', 'string', 'max:80'],
        ]);

        $client = Client::findOrFail($request->integer('client_id'));
        if (empty($client->bucket_folder)) {
            return response()->json(['files' => []]);
        }

        $folder = trim($request->string('folder')->toString(), '/');

        // Este es el path “dentro del disk” (sin path_prefix)
        $diskPath = trim($client->bucket_folder, '/') . '/' . $folder;

        // Para listar con StorageClient necesitamos incluir path_prefix manualmente
        $disk = config('filesystems.disks.gcs');
        $bucketName = $disk['bucket'] ?? null;
        $projectId  = $disk['project_id'] ?? null;
        $pathPrefix = trim((string)($disk['path_prefix'] ?? ''), '/'); // "clientes"

        if (!$bucketName || !$projectId) {
            return response()->json([
                'files' => [],
                'error' => 'Configuración GCS incompleta (bucket/project_id).',
            ], 500);
        }

        $objectPrefix = ($pathPrefix ? ($pathPrefix . '/') : '') . trim($diskPath, '/') . '/';

        try {
            $storage = new StorageClient(['projectId' => $projectId]);
            $bucket = $storage->bucket($bucketName);

            $files = [];

            foreach ($bucket->objects(['prefix' => $objectPrefix]) as $object) {
                $name = (string)$object->name();

                // Solo archivos en el “nivel actual”, no subcarpetas
                $relative = Str::after($name, $objectPrefix);
                if ($relative === '' || str_contains($relative, '/')) {
                    continue;
                }

                // Ignorar marcador
                if ($relative === '.keep') {
                    continue;
                }

                $info = $object->info(); // contiene size, timeCreated, updated

                $sizeBytes = (int)($info['size'] ?? 0);
                $created   = $info['timeCreated'] ?? null; // RFC3339
                $updated   = $info['updated'] ?? null;     // RFC3339

                $files[] = [
                    'name' => $relative,
                    'size_bytes' => $sizeBytes,
                'size' => $this->humanBytes($sizeBytes),
                    'created_at' => $created ? Carbon::parse($created)->toIso8601String() : null,
                    'updated_at' => $updated ? Carbon::parse($updated)->toIso8601String() : null,
                    // Esto es el path “para usar con Storage::disk('gcs')”
                    'disk_path'  => trim($diskPath, '/') . '/' . $relative,
                ];
            }

            usort($files, fn($a, $b) => strcmp($a['name'], $b['name']));

            return response()->json([
                'files' => $files,
            ]);

        } catch (\Throwable $e) {
            Log::error('File list failed', ['error' => $e->getMessage()]);
            return response()->json([
                'files' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // GET /files/preview?client_id=6&folder=DIC_2025&file=algo.txt
    public function preview(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'folder'    => ['required', 'string', 'max:80'],
            'file'      => ['required', 'string', 'max:255'],
        ]);

        $client = Client::findOrFail($request->integer('client_id'));
        if (empty($client->bucket_folder)) {
            return response()->json(['error' => 'Cliente sin bucket_folder'], 400);
        }

        $folder = trim($request->string('folder')->toString(), '/');
        $file   = basename($request->string('file')->toString()); // seguridad básica

        $diskPath = trim($client->bucket_folder, '/') . '/' . $folder . '/' . $file;

        // Solo preview para .txt (puedes ampliar luego)
        if (!str_ends_with(strtolower($file), '.txt')) {
            return response()->json(['error' => 'Preview solo disponible para .txt'], 400);
        }

        try {
            $content = Storage::disk('gcs')->get($diskPath);

            // Limitar tamaño para popup (evitar reventar el browser)
            $max = 200_000; // 200 KB
            $truncated = false;

            if (strlen($content) > $max) {
                $content = substr($content, 0, $max);
                $truncated = true;
            }

            return response()->json([
                'name' => $file,
                'content' => $content,
                'truncated' => $truncated,
            ]);
        } catch (\Throwable $e) {
            Log::error('Preview failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'No se pudo cargar el preview.'], 500);
        }
    }

    // GET /files/download?client_id=6&folder=DIC_2025&file=algo.txt
    public function download(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'folder'    => ['required', 'string', 'max:80'],
            'file'      => ['required', 'string', 'max:255'],
        ]);

        $client = Client::findOrFail($request->integer('client_id'));
        if (empty($client->bucket_folder)) {
            abort(404);
        }

        $folder = trim($request->string('folder')->toString(), '/');
        $file   = basename($request->string('file')->toString());

        $diskPath = trim($client->bucket_folder, '/') . '/' . $folder . '/' . $file;

        // Stream download desde el servidor (no signed URL, funciona con ADC)
        $stream = Storage::disk('gcs')->readStream($diskPath);
        if (!$stream) {
            abort(404);
        }

        return response()->streamDownload(function () use ($stream) {
            fpassthru($stream);
            fclose($stream);
        }, $file);
    }

    // DELETE /files/delete  (JSON)
    public function delete(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'folder'    => ['required', 'string', 'max:80'],
            'file'      => ['required', 'string', 'max:255'],
        ]);

        $client = Client::findOrFail($request->integer('client_id'));
        if (empty($client->bucket_folder)) {
            return response()->json(['ok' => false, 'error' => 'Cliente sin bucket_folder'], 400);
        }

        $folder = trim($request->string('folder')->toString(), '/');
        $file   = basename($request->string('file')->toString());

        $diskPath = trim($client->bucket_folder, '/') . '/' . $folder . '/' . $file;

        try {
            $ok = Storage::disk('gcs')->delete($diskPath);

            return response()->json([
                'ok' => (bool)$ok,
            ]);
        } catch (\Throwable $e) {
            Log::error('Delete file failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'error' => 'No se pudo eliminar.'], 500);
        }
    }

    private function humanBytes(?int $bytes): ?string
    {
        if ($bytes === null) return null;
        $units = ['B','KB','MB','GB','TB'];
        $i = 0;
        $v = (float)$bytes;
        while ($v >= 1024 && $i < count($units)-1) {
            $v /= 1024;
            $i++;
        }
        // 0 decimals for bytes, 1 decimal otherwise
        $dec = $i == 0 ? 0 : 1;
        return number_format($v, $dec) . ' ' . $units[$i];
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileViewerController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $clientsQuery = Client::query();
        if ($user && ($user->role ?? null) === 'client') {
            $clientsQuery->where('id', $user->client_id);
        }

        $clients = $clientsQuery->orderBy('name')->get();

        return view('files.index', compact('clients'));
    }

    public function folders(Request $request)
    {
        $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
        ]);

        $client = Client::findOrFail($request->input('client_id'));

        try {
            $basePath = trim((string) $client->bucket_folder, '/');
            if ($basePath === '') {
                return response()->json([]);
            }

            $dirs = Storage::disk('gcs')->directories($basePath);
            $folders = array_values(array_filter(array_map(function ($dir) {
                $dir = trim((string) $dir, '/');
                return $dir === '' ? null : basename($dir);
            }, $dirs)));
            sort($folders);

            return response()->json($folders);
        } catch (\Throwable $e) {
            Log::error('GCS folders error', [
                'client_id' => $client->id,
                'bucket_folder' => $client->bucket_folder,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'No se pudo cargar la lista de carpetas.'], 500);
        }
    }


    public function list(Request $request)
    {
        $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'folder' => 'nullable|string',
        ]);

        $client = Client::findOrFail($request->input('client_id'));
        $folder = trim((string) $request->input('folder', ''), '/');
        $basePath = trim((string) $client->bucket_folder, '/');

        $diskPath = $basePath;
        if ($folder !== '') {
            $diskPath .= '/' . $folder;
        }

        $diskPath = trim($diskPath, '/');

        $storage = new StorageClient([
            'keyFilePath' => config('filesystems.disks.gcs.key_file'),
        ]);

        $bucket = $storage->bucket(config('filesystems.disks.gcs.bucket'));

        $prefix = $diskPath . '/';
        $files = [];

        foreach ($bucket->objects(['prefix' => $prefix]) as $object) {
            $name = $object->name();
            if ($name === $prefix) {
                continue;
            }

            // Skip "subfolders" (only list current folder level)
            $relative = Str::after($name, $prefix);
            if (Str::contains($relative, '/')) {
                continue;
            }

            $files[] = [
                'name' => basename($name),
                'path' => $name,
                'url' => $object->signedUrl(new \DateTime('+1 hour')),
                'updated' => $object->info()['updated'] ?? null,
                'size' => $object->info()['size'] ?? null,
            ];
        }

        // Sort newest first if we have timestamps
        usort($files, function ($a, $b) {
            return strcmp((string) ($b['updated'] ?? ''), (string) ($a['updated'] ?? ''));
        });

        return response()->json($files);
    }

    public function delete(Request $request)
    {
        // Only admin can delete files
        abort_unless(auth()->user() && auth()->user()->isAdmin(), 403);

        $request->validate([
            'path' => 'required|string',
        ]);

        try {
            Storage::disk('gcs')->delete($request->input('path'));

            return response()->json([
                'success' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error('FileViewerController::delete ERROR', [
                'user_id' => auth()->id(),
                'path' => $request->input('path'),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo borrar el archivo.',
            ], 500);
        }
    }
}

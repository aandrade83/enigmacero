<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    private function effectiveClientId(Request $request): int
    {
        $user = $request->user();
        if ($user && $user->role === 'client' && $user->client_id) {
            return (int)$user->client_id;
        }

        $clientId = $request->integer('client_id') ?: (int)Client::orderBy('name')->value('id');
        return (int)$clientId;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user && $user->role === 'client' && $user->client_id) {
            $clients = Client::whereKey($user->client_id)->get();
        } else {
            $clients = Client::orderBy('name')->get();
        }

        return view('uploads.index', compact('clients'));
    }

    /**
     * Retorna las carpetas existentes dentro del folder del cliente en GCS.
     * GET /uploads/folders?client_id=6
     */
    public function folders(Request $request)
    {
        $clientId = $this->effectiveClientId($request);
        $client = Client::findOrFail($clientId);

        if (empty($client->bucket_folder)) {
            return response()->json([
                'folders' => [],
                'current' => $this->currentMonthFolder(),
                'client_folder' => null,
            ]);
        }

        try {
            // Lista carpetas de primer nivel dentro del folder del cliente
            $dirs = Storage::disk('gcs')->directories($client->bucket_folder);

            $folders = collect($dirs)
                ->map(function ($dir) use ($client) {
                    // Ej: "6_test/DIC_2025" -> "DIC_2025"
                    $relative = Str::after($dir, rtrim($client->bucket_folder, '/') . '/');
                    return trim($relative, '/');
                })
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all();

            return response()->json([
                'folders' => $folders,
                'current' => $this->currentMonthFolder(),
                'client_folder' => $client->bucket_folder,
            ]);
        } catch (\Throwable $e) {
            Log::error('GCS list folders failed (uploads)', [
                'client_id' => $clientId,
                'bucket_folder' => $client->bucket_folder,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'folders' => [],
                'current' => $this->currentMonthFolder(),
                'client_folder' => $client->bucket_folder,
                'error' => 'No se pudo cargar la lista de carpetas.',
            ], 500);
        }
    }

    /**
     * POST /uploads
     */
    public function store(Request $request)
    {
        $clientId = $this->effectiveClientId($request);

        $request->validate([
            // client_id viene en el form para admin/employee, pero para client lo ignoramos
            'target_folder'  => ['required', 'string', 'max:50'],
            'files'          => ['required'],
            'files.*'        => ['file', 'max:20480'], // 20MB por archivo (ajustable)
        ]);

        $client = Client::findOrFail($clientId);

        if (empty($client->bucket_folder)) {
            return back()->withErrors(['files' => 'Este cliente no tiene bucket_folder asignado.'])->withInput();
        }

        $target = $request->string('target_folder')->toString();
        if ($target === '__CURRENT__') {
            $target = $this->currentMonthFolder();
        }

        $basePath = trim($client->bucket_folder, '/') . '/' . trim($target, '/');

        try {
            Storage::disk('gcs')->makeDirectory($basePath);
            Storage::disk('gcs')->put($basePath . '/.keep', 'ok');

            $uploaded = 0;

            foreach ((array)$request->file('files') as $file) {
                if (!$file) continue;

                $originalName = $file->getClientOriginalName();

                $originalName = basename($originalName);
                $originalName = preg_replace('/[^\w\s\.\-\(\)\[\]]+/u', '_', $originalName);
                $originalName = preg_replace('/\s+/', ' ', $originalName);
                $originalName = trim($originalName);
                if ($originalName === '') $originalName = 'archivo.txt';

                $finalName = $originalName;
                $pathCheck = trim($basePath, '/') . '/' . $finalName;

                $counter = 1;
                while (Storage::disk('gcs')->exists($pathCheck)) {
                    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                    $nameOnly = pathinfo($originalName, PATHINFO_FILENAME);

                    $finalName = $nameOnly . " ({$counter})" . ($ext ? ".{$ext}" : "");
                    $pathCheck = trim($basePath, '/') . '/' . $finalName;
                    $counter++;
                }

                Storage::disk('gcs')->putFileAs($basePath, $file, $finalName);
                $uploaded++;
            }

            return redirect()
                ->route('uploads.index')
                ->with('success', "Archivos cargados: {$uploaded}. Destino: {$client->bucket_folder}/{$target}");

        } catch (\Throwable $e) {
            Log::error('Upload failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['files' => 'No se pudo subir archivos. Revisa logs.'])->withInput();
        }
    }

    private function currentMonthFolder(): string
    {
        $m = (int)date('n');
        $y = (int)date('Y');

        $map = [
            1 => 'ENE', 2 => 'FEB', 3 => 'MAR', 4 => 'ABR',
            5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO',
            9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DIC',
        ];

        return ($map[$m] ?? 'MES') . '_' . $y;
    }
}

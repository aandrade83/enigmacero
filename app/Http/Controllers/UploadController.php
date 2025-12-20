<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function index()
    {
        $clients = Client::orderBy('name')->get();
        return view('uploads.index', compact('clients'));
    }

    /**
     * Retorna las carpetas existentes dentro del folder del cliente en GCS.
     * GET /uploads/folders?client_id=6
     */
    public function folders(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
        ]);

        $client = Client::findOrFail($request->integer('client_id'));

        if (empty($client->bucket_folder)) {
            return response()->json([
                'folders' => [],
                'current' => $this->currentMonthFolder(),
                'client_folder' => null,
            ]);
        }

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
    }

    /**
     * POST /uploads
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id'      => ['required', 'integer', 'exists:clients,id'],
            'target_folder'  => ['required', 'string', 'max:50'],
            'files'          => ['required'],
            'files.*'        => ['file', 'max:20480'], // 20MB por archivo (ajustable)
        ]);

        $client = Client::findOrFail($request->integer('client_id'));

        if (empty($client->bucket_folder)) {
            return back()->withErrors(['files' => 'Este cliente no tiene bucket_folder asignado.'])->withInput();
        }

        $target = $request->string('target_folder')->toString();
        if ($target === '__CURRENT__') {
            $target = $this->currentMonthFolder(); // DIC_2025
        }

        // Ruta destino dentro del disk (OJO: el disk ya tiene path_prefix "clientes/")
        $basePath = trim($client->bucket_folder, '/') . '/' . trim($target, '/');

        try {
            // Asegurar “carpeta” con marcador
            Storage::disk('gcs')->makeDirectory($basePath);
            Storage::disk('gcs')->put($basePath . '/.keep', 'ok');

            $uploaded = 0;

            foreach ((array)$request->file('files') as $file) {
                if (!$file) continue;

                $original = $file->getClientOriginalName();
                $ext = strtolower($file->getClientOriginalExtension());
                $nameOnly = pathinfo($original, PATHINFO_FILENAME);

                $safeBase = Str::slug($nameOnly, '_');
                if ($safeBase === '') $safeBase = 'archivo';

                // Evitar colisiones
                $finalName = $safeBase . '_' . date('Ymd_His') . '_' . Str::random(4) . ($ext ? ".{$ext}" : '');

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
        // Formato pedido: DIC_2025
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

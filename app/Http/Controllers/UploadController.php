<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Solo Admin/Employee/Client (por si la ruta no está protegida correctamente)
        abort_unless(in_array($user->role, ['admin', 'employee', 'client'], true), 403);

        $clients = $this->accessibleClients();
        $lockedClientId = ($user->role === 'client') ? (int) $user->client_id : null;

        return view('uploads.index', compact('clients', 'lockedClientId'));
    }

    public function folders(Request $request)
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['admin', 'employee', 'client'], true), 403);

        $clientId = (int) $request->input('client_id');

        // Si es CLIENT, no permitimos consultar carpetas de otro cliente
        if ($user->role === 'client') {
            $clientId = (int) $user->client_id;
        }

        $client = $this->resolveClientOrFail($clientId);

        $disk = Storage::disk('gcs');

        $folders = [];
        try {
            $dirs = $disk->directories($client->bucket_folder);
            // Devolver solo el nombre de la carpeta (sin prefijo)
            foreach ($dirs as $d) {
                $name = trim(str_replace($client->bucket_folder . '/', '', $d), '/');
                if ($name !== '') $folders[] = $name;
            }
            sort($folders);
        } catch (\Throwable $e) {
            Log::warning('Error listando carpetas GCS', ['client_id' => $client->id, 'error' => $e->getMessage()]);
        }

        return response()->json([
            'folders' => $folders,
            'default' => $this->currentMonthFolder(),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['admin', 'employee', 'client'], true), 403);

        // Si es CLIENT, forzamos el client_id (no puede subir a otro cliente)
        if ($user->role === 'client') {
            if (empty($user->client_id)) {
                return back()->withErrors(['client_id' => 'Este usuario no tiene un cliente asociado.']);
            }
            $request->merge(['client_id' => (int) $user->client_id]);
        }

        $validated = $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'folder'    => ['nullable', 'string', 'max:50'],
            'files'     => ['required'],
            'files.*'   => ['file', 'max:51200'], // 50MB por archivo
        ]);

        $client = $this->resolveClientOrFail((int) $validated['client_id']);

        $folder = trim((string) ($validated['folder'] ?? ''));
        if ($folder === '' || $folder === 'MES_ACTUAL') {
            $folder = $this->currentMonthFolder();
        }

        $disk = Storage::disk('gcs');

        // Asegurar "carpeta" en GCS creando marcador .keep
        $basePath = trim($client->bucket_folder . '/' . $folder, '/');
        try {
            if (!$disk->exists($basePath . '/.keep')) {
                $disk->put($basePath . '/.keep', 'ok');
            }
        } catch (\Throwable $e) {
            Log::error('No se pudo crear marcador en GCS', ['path' => $basePath . '/.keep', 'error' => $e->getMessage()]);
            return back()->withErrors(['files' => 'No se pudo crear la carpeta destino en GCS.']);
        }

        $uploaded = 0;

        foreach ((array) $request->file('files', []) as $file) {
            if (!$file) continue;

            // Mantener nombre original, solo sanitizando
            $originalName = $file->getClientOriginalName();
            $info = pathinfo($originalName);
            $name = $info['filename'] ?? 'archivo';
            $ext  = $info['extension'] ?? '';

            $safeBase = Str::slug($name, '_');
            $safeExt  = $ext ? '.' . strtolower($ext) : '';
            $finalName = $safeBase . $safeExt;

            $target = $basePath . '/' . $finalName;

            // Si existe, agregar sufijo incremental
            $i = 1;
            while ($disk->exists($target)) {
                $finalName = $safeBase . '_' . $i . $safeExt;
                $target = $basePath . '/' . $finalName;
                $i++;
            }

            try {
                $disk->put($target, file_get_contents($file->getRealPath()));
                $uploaded++;
            } catch (\Throwable $e) {
                Log::error('Error subiendo archivo a GCS', ['target' => $target, 'error' => $e->getMessage()]);
            }
        }

        if ($uploaded <= 0) {
            return back()->withErrors(['files' => 'No se pudo subir ningún archivo.']);
        }

        return redirect()->route('uploads.index')->with('success', "Archivos subidos: {$uploaded}");
    }

    private function accessibleClients()
    {
        $user = auth()->user();

        if ($user->role === 'client') {
            return Client::query()
                ->where('id', (int) $user->client_id)
                ->orderBy('name')
                ->get();
        }

        return Client::query()->orderBy('name')->get();
    }

    private function resolveClientOrFail(int $clientId): Client
    {
        $client = Client::findOrFail($clientId);

        $user = auth()->user();
        if ($user->role === 'client' && (int) $user->client_id !== (int) $client->id) {
            abort(403);
        }

        return $client;
    }

    private function currentMonthFolder(): string
    {
        $now = Carbon::now('America/Costa_Rica');

        $abbr = [
            1 => 'ENE', 2 => 'FEB', 3 => 'MAR', 4 => 'ABR', 5 => 'MAY', 6 => 'JUN',
            7 => 'JUL', 8 => 'AGO', 9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DIC',
        ];

        return ($abbr[(int) $now->month] ?? strtoupper($now->format('M'))) . '_' . $now->year;
    }
}

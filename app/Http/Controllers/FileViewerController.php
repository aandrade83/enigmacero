<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileViewerController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['admin', 'employee', 'client'], true), 403);

        $clients = $this->accessibleClients();
        $lockedClientId = ($user->role === 'client') ? (int) $user->client_id : null;

        return view('files.index', compact('clients', 'lockedClientId'));
    }

    public function folders(Request $request)
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['admin', 'employee', 'client'], true), 403);

        // Compatibilidad: algunas llamadas viejas enviaban el parametro client en vez de client_id
        $clientId = (int) ($request->input('client_id') ?? $request->input('client'));
        if ($user->role === 'client') {
            $clientId = (int) $user->client_id;
        }

        $client = $this->resolveClientOrFail($clientId);

        $disk = Storage::disk('gcs');

        $folders = [];
        try {
            $dirs = $disk->directories($client->bucket_folder);
            foreach ($dirs as $d) {
                $name = trim(str_replace($client->bucket_folder . '/', '', $d), '/');
                if ($name !== '') $folders[] = $name;
            }
            sort($folders);
        } catch (\Throwable $e) {
            Log::warning('Error listando carpetas GCS (viewer)', ['client_id' => $client->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['folders' => $folders]);
    }

    public function listFiles(Request $request)
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['admin', 'employee', 'client'], true), 403);

        // Compatibilidad: algunas llamadas viejas enviaban el parametro client en vez de client_id
        $clientId = (int) ($request->input('client_id') ?? $request->input('client'));
        $folder   = (string) $request->input('folder', '');

        if ($user->role === 'client') {
            $clientId = (int) $user->client_id;
        }

        $client = $this->resolveClientOrFail($clientId);

        $disk = Storage::disk('gcs');

        $prefix = trim($client->bucket_folder . '/' . trim($folder, '/'), '/');

        $files = [];
        try {
            foreach ($disk->files($prefix) as $path) {
                // Excluir .keep
                if (str_ends_with($path, '/.keep') || str_ends_with($path, '.keep')) continue;

                $name = basename($path);

                $size = null;
                $created = null;
                $updated = null;

                try {
                    $size = $disk->size($path);
                } catch (\Throwable $e) {}

                // En GCS no siempre hay created_at nativo; usamos lastModified (unix)
                try {
                    $last = $disk->lastModified($path);
                    $updated = Carbon::createFromTimestamp($last)->toDateTimeString();
                    $created = $updated;
                } catch (\Throwable $e) {}

                $files[] = [
                    'name' => $name,
                    'path' => $path,
                    'size' => $size,
                    'created_at' => $created,
                    'updated_at' => $updated,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Error listando archivos GCS', ['client_id' => $client->id, 'folder' => $folder, 'error' => $e->getMessage()]);
        }

        return response()->json(['files' => $files]);
    }

    public function preview(Request $request)
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['admin', 'employee', 'client'], true), 403);

        // Compatibilidad: algunas llamadas viejas enviaban el parametro client en vez de client_id
        $clientId = (int) ($request->input('client_id') ?? $request->input('client'));
        $path     = (string) $request->input('path', '');

        if ($user->role === 'client') {
            $clientId = (int) $user->client_id;
        }

        $client = $this->resolveClientOrFail($clientId);

        // Protección extra: el path debe empezar con el folder del cliente
        abort_unless(str_starts_with($path, $client->bucket_folder . '/'), 403);

        $disk = Storage::disk('gcs');

        try {
            $content = $disk->get($path);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'No se pudo leer el archivo.'], 404);
        }

        // Preview amigable (mayoría .txt)
        return response()->json([
            'name' => basename($path),
            'content' => $content,
        ]);
    }

    public function download(Request $request)
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['admin', 'employee', 'client'], true), 403);

        // Compatibilidad: algunas llamadas viejas enviaban el parametro client en vez de client_id
        $clientId = (int) ($request->input('client_id') ?? $request->input('client'));
        $path     = (string) $request->input('path', '');

        if ($user->role === 'client') {
            $clientId = (int) $user->client_id;
        }

        $client = $this->resolveClientOrFail($clientId);
        abort_unless(str_starts_with($path, $client->bucket_folder . '/'), 403);

        $disk = Storage::disk('gcs');

        try {
            $content = $disk->get($path);
        } catch (\Throwable $e) {
            abort(404);
        }

        return response($content, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . basename($path) . '"',
        ]);
    }

    public function delete(Request $request)
    {
        $user = auth()->user();

        // SOLO ADMIN puede borrar archivos
        abort_unless(($user->role ?? '') === 'admin', 403);

        // Compatibilidad: algunas llamadas viejas enviaban el parametro client en vez de client_id
        $clientId = (int) ($request->input('client_id') ?? $request->input('client'));
        $path     = (string) $request->input('path', '');

        $client = $this->resolveClientOrFail($clientId);
        abort_unless(str_starts_with($path, $client->bucket_folder . '/'), 403);

        $disk = Storage::disk('gcs');

        try {
            $disk->delete($path);
            return response()->json(['deleted' => true]);
        } catch (\Throwable $e) {
            Log::error('Error borrando archivo GCS', ['path' => $path, 'error' => $e->getMessage()]);
            return response()->json(['deleted' => false], 500);
        }
    }

    private function accessibleClients()
    {
        $user = auth()->user();

        if (($user->role ?? '') === 'client') {
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
        if (($user->role ?? '') === 'client' && (int) $user->client_id !== (int) $client->id) {
            abort(403);
        }

        return $client;
    }
}

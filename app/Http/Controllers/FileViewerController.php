<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileViewerController extends Controller
{
    /**
     * Resolver el client_id permitido según el rol.
     * - client: siempre su propio client_id
     * - admin/employee: el client_id enviado (o el primero activo)
     */
    private function resolveClientId(Request $request): int
    {
        $user = $request->user();

        if ($user && $user->role === 'client' && $user->client_id) {
            return (int) $user->client_id;
        }

        $clientId = (int) $request->query('client_id', 0);
        if ($clientId > 0) {
            return $clientId;
        }

        return (int) Client::where('is_active', 1)->orderBy('name')->value('id');
    }

    // GET /files
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user && $user->role === 'client') {
            $clients = Client::where('id', (int) $user->client_id)->where('is_active', 1)->get();
        } else {
            $clients = Client::where('is_active', 1)->orderBy('name')->get();
        }

        return view('files.index', compact('clients'));
    }

    // GET /files/folders?client_id=1
    public function folders(Request $request)
    {
        $clientId = $this->resolveClientId($request);

        $client = Client::findOrFail($clientId);
        if (empty($client->bucket_folder)) {
            return response()->json(['folders' => []]);
        }

        try {
            $folders = Storage::disk('gcs')->directories($client->bucket_folder);
            $folders = array_map(fn ($f) => basename(rtrim($f, '/')), $folders);
            sort($folders);

            return response()->json(['folders' => $folders]);
        } catch (\Throwable $e) {
            Log::error('List folders failed', [
                'client_id' => $clientId,
                'bucket_folder' => $client->bucket_folder,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['folders' => [], 'error' => 'No se pudo cargar la lista de carpetas.'], 500);
        }
    }

    // GET /files/list?client_id=1&folder=...
    public function list(Request $request)
    {
        $clientId = $this->resolveClientId($request);

        $request->validate([
            'folder' => ['required', 'string', 'max:80'],
        ]);

        $client = Client::findOrFail($clientId);
        if (empty($client->bucket_folder)) {
            return response()->json(['files' => []]);
        }

        $folder = trim($request->string('folder')->toString(), '/');
        $path = trim($client->bucket_folder, '/') . '/' . $folder;

        try {
            $files = Storage::disk('gcs')->files($path);
            $files = array_map(fn ($f) => basename($f), $files);
            sort($files);

            return response()->json(['files' => $files]);
        } catch (\Throwable $e) {
            Log::error('List files failed', [
                'client_id' => $clientId,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['files' => [], 'error' => 'No se pudo cargar la lista de archivos.'], 500);
        }
    }

    // GET /files/download?client_id=1&folder=...&file=...
    public function download(Request $request)
    {
        $clientId = $this->resolveClientId($request);

        $request->validate([
            'folder' => ['required', 'string', 'max:80'],
            'file'   => ['required', 'string', 'max:255'],
        ]);

        $client = Client::findOrFail($clientId);
        if (empty($client->bucket_folder)) {
            abort(404);
        }

        $folder = trim($request->string('folder')->toString(), '/');
        $file   = basename($request->string('file')->toString());

        $diskPath = trim($client->bucket_folder, '/') . '/' . $folder . '/' . $file;

        $stream = Storage::disk('gcs')->readStream($diskPath);
        if (!$stream) {
            abort(404);
        }

        return response()->streamDownload(function () use ($stream) {
            fpassthru($stream);
            fclose($stream);
        }, $file);
    }

    // DELETE /files/delete (JSON)
    public function delete(Request $request)
    {
        // Seguridad extra: solo admin puede borrar
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['ok' => false, 'error' => 'No autorizado'], 403);
        }

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
                'ok' => (bool) $ok,
            ]);
        } catch (\Throwable $e) {
            Log::error('Delete file failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'error' => 'No se pudo eliminar.'], 500);
        }
    }
}

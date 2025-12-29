<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $clientsQuery = Client::query();

        // Client role: lock to its own client_id
        if ($user && ($user->role ?? null) === 'client') {
            $clientsQuery->where('id', $user->client_id);
        }

        $clients = $clientsQuery->orderBy('name')->get();

        return view('uploads.index', compact('clients'));
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

            // IMPORTANT: Keep the response as a plain JSON array.
            // The front-end expects: ["folder1", "folder2", ...]
            $folders = Storage::disk('gcs')->directories($basePath);

            // Return only folder names (no prefix paths)
            $folders = array_values(array_unique(array_map(function ($path) {
                return basename(trim((string) $path, '/'));
            }, $folders)));
            sort($folders);

            Log::info('Upload folders loaded', [
                'user_id' => auth()->id(),
                'role' => auth()->user()->role ?? null,
                'client_id' => $client->id,
                'count' => count($folders),
            ]);

            return response()->json($folders);
        } catch (\Throwable $e) {
            Log::error('Upload folders error', [
                'user_id' => auth()->id(),
                'role' => auth()->user()->role ?? null,
                'client_id' => $client->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'No se pudo cargar la lista de carpetas.'], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'folder' => 'required|string',
            'files.*' => 'required|file|max:10240',
        ]);

        $client = Client::findOrFail($request->input('client_id'));

        // For client users, force client_id to their own
        $user = auth()->user();
        if ($user && ($user->role ?? null) === 'client') {
            abort_unless((int) $user->client_id === (int) $client->id, 403);
        }

        $folder = trim($request->input('folder'), '/');
        $basePath = trim((string) $client->bucket_folder, '/');
        $diskPath = trim($basePath . '/' . $folder, '/');

        if (!Storage::disk('gcs')->exists($diskPath)) {
            Storage::disk('gcs')->makeDirectory($diskPath);
        }

        foreach ($request->file('files') as $file) {
            $filename = $file->getClientOriginalName();
            Storage::disk('gcs')->putFileAs($diskPath, $file, $filename);
        }

        return redirect()->route('uploads.index')
            ->with('success', 'Archivos subidos correctamente.');
    }

    public function cancel()
    {
        return redirect()->route('uploads.index');
    }
}

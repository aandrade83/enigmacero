<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::orderByDesc('id')->get();
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'internal_email' => ['nullable', 'email', 'max:255'],
            'is_active'      => ['nullable'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        // carpeta: slug + timestamp
        $folder = $this->generateFolder($data['name']);

        DB::beginTransaction();

        try {
            $client = Client::create([
                'name'           => $data['name'],
                'folder'         => $folder,
                'internal_email' => $data['internal_email'] ?? null,
                'is_active'      => $data['is_active'],
            ]);

            // Crear carpeta en GCS (respetando prefix del disk si existe)
            $path = $this->gcsPath($client->folder);
            Storage::disk('gcs')->makeDirectory($path);

            DB::commit();

            return redirect()
                ->route('clients.index')
                ->with('success', 'Cliente creado y carpeta creada en bucket.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Client store failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo crear el cliente: ' . $e->getMessage());
        }
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'internal_email' => ['nullable', 'email', 'max:255'],
            'is_active'      => ['nullable'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        try {
            $client->update([
                'name'           => $data['name'],
                'internal_email' => $data['internal_email'] ?? null,
                'is_active'      => $data['is_active'],
            ]);

            return redirect()
                ->route('clients.index')
                ->with('success', 'Cliente actualizado.');

        } catch (\Throwable $e) {
            Log::error('Client update failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo actualizar: ' . $e->getMessage());
        }
    }

    public function destroy(Client $client)
    {
        DB::beginTransaction();

        try {
            // Borrar carpeta del bucket
            $path = $this->gcsPath($client->folder);
            Storage::disk('gcs')->deleteDirectory($path);

            $client->delete();

            DB::commit();

            return redirect()
                ->route('clients.index')
                ->with('success', 'Cliente eliminado y carpeta borrada del bucket.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Client delete failed', ['error' => $e->getMessage()]);

            return redirect()
                ->route('clients.index')
                ->with('error', 'No se pudo eliminar: ' . $e->getMessage());
        }
    }

    private function generateFolder(string $name): string
    {
        $slug = Str::slug($name, '_');
        $suffix = now()->format('Ymd_His');
        return $slug . '_' . $suffix;
    }

    private function gcsPath(string $folder): string
    {
        $folder = trim($folder, '/');

        $disk = config('filesystems.disks.gcs', []);
        $prefix = trim($disk['path_prefix'] ?? '', '/');

        return $prefix ? ($prefix . '/' . $folder) : $folder;
    }
}

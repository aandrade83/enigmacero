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
        $clients = Client::orderByDesc('created_at')->get();
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
            'is_active'      => ['nullable'], // checkbox
        ]);

        $data['is_active'] = $request->boolean('is_active');

        DB::beginTransaction();

        try {
            // 1) Crear cliente con bucket_folder temporal (evita error NOT NULL)
            $client = Client::create([
                'name'           => $data['name'],
                'internal_email' => $data['internal_email'] ?? null,
                'is_active'      => $data['is_active'],
                'bucket_folder'  => 'pending_' . Str::random(8),
            ]);

            // 2) bucket_folder final: {id}_{slug}
            $folder = $client->id . '_' . Str::slug($client->name, '_');

            // 3) Guardar bucket_folder final en DB
            $client->bucket_folder = $folder;
            $client->save();

	   // 4) Crear "carpeta" en GCS con un marcador .keep
	   $markerPath = $folder . '/.keep';   // <- SIN prefix, el disk ya lo agrega

	  $ok = Storage::disk('gcs')->put($markerPath, 'ok'); // <- no vacío (más confiable)
	 if (!$ok) {
	    throw new \RuntimeException("No se pudo crear marcador en GCS: {$markerPath}");
	}

       }
            DB::commit();

            return redirect()
                ->route('clients.index')
                ->with('success', 'Cliente creado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Client store failed', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['store' => 'No se pudo crear el cliente. Revisa logs (Client store failed).']);
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

        $client->update([
            'name'           => $data['name'],
            'internal_email' => $data['internal_email'] ?? null,
            'is_active'      => $request->boolean('is_active'),
        ]);

        // Nota: NO renombramos bucket_folder aquí porque implicaría mover archivos en el bucket.
        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente actualizado.');
    }

    public function destroy(Client $client)
    {
        try {
            $prefix = (string) env('GCS_PATH_PREFIX', '');
            $prefix = trim($prefix);
            if ($prefix !== '') {
                $prefix = rtrim($prefix, '/') . '/';
            }

            // Borra todo lo que esté bajo clientes/{bucket_folder}/
            if (!empty($client->bucket_folder)) {
                Storage::disk('gcs')->deleteDirectory($prefix . $client->bucket_folder);
            }

            $client->delete();

            return redirect()
                ->route('clients.index')
                ->with('success', 'Cliente eliminado correctamente.');
        } catch (\Throwable $e) {
            Log::error('Client delete failed', ['error' => $e->getMessage()]);

            return redirect()
                ->route('clients.index')
                ->withErrors(['delete' => 'No se pudo eliminar el cliente. Revisa logs.']);
        }
    }
}

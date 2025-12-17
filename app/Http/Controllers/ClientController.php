<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::orderByDesc('created_at')->get();
        return view('clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        // 1) Crear en DB
        $client = Client::create([
            'name' => $data['name'],
            'is_active' => 1,
        ]);

        // 2) Folder seguro (evita espacios raros)
        $folder = 'cliente_' . $client->id . '_' . Str::slug($client->name);

        // 3) Crear prefijo en bucket
        Storage::disk('gcs')->makeDirectory("clientes/{$folder}");

        // 4) Guardar folder en DB
        $client->update(['folder' => $folder]);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente creado correctamente.');
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'admin')->orderBy('name')->get();
        $employees = User::where('role', 'employee')->orderBy('name')->get();
        $clientUsers = User::where('role', 'client')->with('client')->orderBy('name')->get();

        return view('users.index', compact('admins', 'employees', 'clientUsers'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('users.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'      => ['required', 'in:admin,employee,client'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'password'  => ['required', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($data['role'] === 'client' && empty($data['client_id'])) {
            return back()->withInput()->withErrors(['client_id' => 'Debe seleccionar un cliente.']);
        }

        if ($data['role'] !== 'client') {
            $data['client_id'] = null;
        }

        try {
            User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'role'      => $data['role'],
                'client_id' => $data['client_id'] ?? null,
                'password'  => Hash::make($data['password']),
                'is_active' => array_key_exists('is_active', $data) ? (bool)$data['is_active'] : true,
            ]);

            return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
        } catch (\Throwable $e) {
            Log::error('User store failed', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['store' => 'No se pudo crear el usuario.']);
        }
    }

    public function edit(User $user)
    {
        $clients = Client::orderBy('name')->get();
        return view('users.edit', compact('user', 'clients'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role'      => ['required', 'in:admin,employee,client'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'password'  => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($data['role'] === 'client' && empty($data['client_id'])) {
            return back()->withInput()->withErrors(['client_id' => 'Debe seleccionar un cliente.']);
        }

        if ($data['role'] !== 'client') {
            $data['client_id'] = null;
        }

        try {
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->role = $data['role'];
            $user->client_id = $data['client_id'] ?? null;

            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            if (array_key_exists('is_active', $data)) {
                $user->is_active = (bool)$data['is_active'];
            }

            $user->save();

            return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
        } catch (\Throwable $e) {
            Log::error('User update failed', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['update' => 'No se pudo actualizar el usuario.']);
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
        } catch (\Throwable $e) {
            Log::error('User delete failed', ['error' => $e->getMessage()]);
            return redirect()->route('users.index')->withErrors(['delete' => 'No se pudo eliminar el usuario.']);
        }
    }
}

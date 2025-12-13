<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    /**
     * Muestra la pantalla de login.
     */
    public function showLogin()
    {
        // Si ya está logueado, manda directo al dashboard
        if (Session::get('user_authenticated')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * 
     */
public function login(Request $request)
{
    // 1) Registrar en el log que llegó una petición de login
    Log::info('Login request received', [
        'email' => $request->input('email'),
        'ip'    => $request->ip(),
    ]);

    // 2) Validar que el formulario traiga email y password
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    // 3) Buscar el usuario por correo
    $user = User::where('email', $credentials['email'])->first();

    if (! $user) {
        // No existe el correo
        Log::warning('Login failed: user not found', [
            'email' => $credentials['email'],
        ]);

        return back()
            ->withErrors([
                'email' => 'El correo no existe, contacte a administración.',
            ])
            ->withInput(); // deja el email escrito en el form
    }

    // 4) Revisar si está activo
    if (! $user->is_active) {
        Log::warning('Login failed: user inactive', [
            'email' => $credentials['email'],
            'user_id' => $user->id,
        ]);

        return back()
            ->withErrors([
                'email' => 'Su usuario está deshabilitado, favor contactar a administración.',
            ])
            ->withInput();
    }

    // 5) Verificar contraseña usando el hash bcrypt
    if (! Hash::check($credentials['password'], $user->password)) {
        Log::warning('Login failed: invalid password', [
            'email'   => $credentials['email'],
            'user_id' => $user->id,
        ]);

        return back()
            ->withErrors([
                'password' => 'Credenciales inválidas.',
            ])
            ->withInput();
    }

    // 6) Todo bien: iniciar sesión en Laravel
    Auth::login($user);

    Log::info('Login successful', [
        'user_id' => $user->id,
        'email'   => $user->email,
    ]);

    // 7) Redirigir al dashboard
    return redirect()->route('dashboard');
}



    /**
     * Muestra el dashboard (solo si está autenticado).
     */
    public function dashboard()
    {
        if (!Session::get('user_authenticated')) {
            return redirect()->route('login');
        }

        $userName = Session::get('user_name', 'Administrador Enigmacero');

        return view('dashboard', compact('userName'));
    }

    /**
     * Cerrar sesión.
     */
    public function logout(Request $request)
    {
          // Limpiar completamente la sesión
    $request->session()->invalidate();
    $request->session()->regenerateToken();

        return redirect()->route('login.form');
    }
}

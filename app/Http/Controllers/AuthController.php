<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session; 


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
    // 1) Validar datos del formulario
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    // 2) Buscar usuario
    $user = User::where('email', $credentials['email'])->first();

    if (!$user) {
        Log::warning('Login failed: user not found', ['email' => $credentials['email']]);

        return back()
            ->withErrors(['email' => 'El correo no existe, contacte a administración.'])
            ->withInput();
    }

    // 3) Verificar si está activo
    if (!$user->is_active) {
        Log::warning('Login failed: user inactive', ['email' => $user->email]);

        return back()
            ->withErrors(['email' => 'Su usuario está deshabilitado, contacte a administración.'])
            ->withInput();
    }

    // 4) Verificar contraseña
    if (!Hash::check($credentials['password'], $user->password)) {
        Log::warning('Login failed: wrong password', ['email' => $user->email]);

        return back()
            ->withErrors(['password' => 'La contraseña es incorrecta.'])
            ->withInput();
    }

    // 5) Loguear al usuario en Laravel
    Auth::login($user);
    $request->session()->regenerate();

    Log::info('Login successful', ['user_id' => $user->id, 'email' => $user->email]);

    // 6) Ir al dashboard
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

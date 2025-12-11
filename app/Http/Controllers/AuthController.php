<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
     * Procesa el login "simple".
     */
public function login(Request $request)
{
    // Validamos que vengan los campos, solo por orden
    $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    // ⚠️ TEMPORAL: aceptar siempre mientras estamos en desarrollo
    $request->session()->put('user', [
        'email' => $request->input('email'),
        'name'  => 'Administrador Enigmacero',
    ]);

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

    // Antes estaba: return redirect()->route('login.form');
    return redirect()->route('login');
    }
}

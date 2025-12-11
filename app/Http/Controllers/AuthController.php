<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Usuario de prueba (hard-coded).
     * Más adelante esto se moverá a base de datos.
     */
    private array $demoUser = [
        'email'    => 'admin@enigmacero.test',
        'password' => 'Admin1234', // SOLO demo
        'name'     => 'Administrador Enigmacero',
        'role'     => 'ADMIN',
    ];

    /**
     * Muestra el formulario de login.
     */
    public function showLoginForm(Request $request)
    {
        // Si ya está logueado, lo mandamos al dashboard
        if ($request->session()->has('user')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Procesa el login.
     */
    public function login(Request $request)
    {
        // Validación básica de campos
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Comparación con nuestro usuario de prueba
        if (
            $credentials['email'] === $this->demoUser['email']
            && $credentials['password'] === $this->demoUser['password']
        ) {
            // Guardar datos mínimos en sesión
            $request->session()->put('user', [
                'name'  => $this->demoUser['name'],
                'email' => $this->demoUser['email'],
                'role'  => $this->demoUser['role'],
            ]);

            // Regenerar ID de sesión por seguridad
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        // Si fallan las credenciales
        return back()
            ->withErrors(['email' => 'Credenciales inválidas'])
            ->withInput($request->only('email'));
    }

    /**
     * Muestra el dashboard (solo para usuarios logueados).
     */
    public function dashboard(Request $request)
    {
        $user = $request->session()->get('user');

        if (!$user) {
            return redirect()->route('login.form');
        }

        return view('dashboard', ['user' => $user]);
    }

    /**
     * Cierra la sesión.
     */
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.form');
    }
}

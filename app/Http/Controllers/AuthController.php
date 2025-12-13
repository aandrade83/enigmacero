<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;  
use Illuminate\Support\Facades\Hash;


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
      //REGISTRA LOG
    \Log::info('Login attempt', [   
        'email' => $request->input('email'),
        'ip'    => $request->ip(),
    ]);

   // IMPRIME EN PANTALLA Y EXIT
    dd($request->all()); // para depurar

    // 1) Validar datos que vienen del formulario
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ], [
        'email.required'    => 'El correo es obligatorio.',
        'email.email'       => 'Debe ingresar un correo válido.',
        'password.required' => 'La contraseña es obligatoria.',
    ]);

    // 2) Buscar usuario por email
    $user = User::where('email', $credentials['email'])->first();

    // 3) Si no existe -> mensaje "correo no existe"
    if (! $user) {
        return back()
            ->withErrors(['email' => 'El correo no existe. Favor contactar a administración.'])
            ->withInput();
    }

    // 4) Si está desactivado -> mensaje específico
    if (! $user->is_active) {
        return back()
            ->withErrors(['email' => 'Su usuario está deshabilitado. Favor contactar a administración.'])
            ->withInput();
    }

    // 5) Verificar contraseña contra el hash almacenado
    if (! Hash::check($credentials['password'], $user->password)) {
        return back()
            ->withErrors(['password' => 'Credenciales inválidas.'])
            ->withInput();
    }

    // 6) Login "manual": guardar info mínima en sesión
    $request->session()->put('user_id', $user->id);
    $request->session()->put('user_name', $user->name);
    $request->session()->put('user_role', $user->role);

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

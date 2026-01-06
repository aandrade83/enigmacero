<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;




class AuthController extends Controller
{
    /**
     * Muestra la pantalla de login.
     */
    public function showLogin()
    {
        // Si ya está logueado, manda directo al dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Procesa el login.
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

        // 5) Loguear al usuario en Laravel (sistema Auth)
        Auth::login($user);
        $request->session()->regenerate();

        // Opcional: mantener tus flags propios por si los usas en vistas
        Session::put('user_authenticated', true);
        Session::put('user_name', $user->name ?? $user->email);

        Log::info('Login successful', ['user_id' => $user->id, 'email' => $user->email]);

        // 6) Ir al dashboard
        return redirect()->route('dashboard');
    }

    /**
     * Muestra el dashboard (solo si está autenticado).
     */
    public function dashboard()
    {
        // Si NO hay usuario autenticado, mandamos al login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userName = $user->name ?? 'Usuario';

        // Frase por defecto
        $dailyQuote = [
            'text'   => 'La inteligencia de negocios comienza con buenas preguntas.',
            'author' => 'EnigmaCero'
        ];

        /**
         * Extra: cachear quote en sesión para no pegarle al API en cada refresh
         * (especialmente útil en Cloud Run).
         */
        $cachedQuote = Session::get('daily_quote');
        $cachedAt    = Session::get('daily_quote_at');

        // Si existe cache reciente (ej. 6 horas), úsalo
        if ($cachedQuote && $cachedAt && (time() - (int)$cachedAt) < 21600) {
            $dailyQuote = $cachedQuote;
            return view('dashboard', compact('userName', 'dailyQuote'));
        }

        // Intentar traer una frase de un API público
        try {
            $response = Http::timeout(2)->get('https://api.quotable.io/random', [
                'tags' => 'business|wisdom'
            ]);

            if ($response->ok()) {
                $data = $response->json();

                if (!empty($data['content'])) {
                    $dailyQuote['text']   = $data['content'];
                    $dailyQuote['author'] = $data['author'] ?? 'Anónimo';
                }
            }
        } catch (\Throwable $e) {
            // Si falla, simplemente dejamos la frase por defecto
            Log::warning('No se pudo obtener frase inspiradora', [
                'error' => $e->getMessage()
            ]);
        }

        // Guardar cache en sesión
        Session::put('daily_quote', $dailyQuote);
        Session::put('daily_quote_at', time());

        return view('dashboard', compact('userName', 'dailyQuote'));
    }

    /**
     * Cerrar sesión.
     */
    public function logout(Request $request)
    {
        // Borrar tus flags personalizados
        Session::forget('user_authenticated');
        Session::forget('user_name');
        Session::forget('daily_quote');
        Session::forget('daily_quote_at');

        // Cerrar sesión de Laravel
        Auth::logout();

        // Limpiar completamente la sesión
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }


public function showForgot()
{
    return view('auth.forgot-password');
}

public function sendResetLink(Request $request)
{
    $request->validate([
        'email' => ['required', 'email'],
    ]);

    // Importante: por seguridad NO confirmamos si el correo existe o no.
    Password::sendResetLink($request->only('email'));

    return back()->with('success', 'Si el correo existe en la base de datos, enviaremos un email con los pasos para cambiar la contraseña.');
}

public function showReset(string $token, Request $request)
{
    return view('auth.reset-password', [
        'token' => $token,
        'email' => $request->query('email'),
    ]);
}

public function doReset(Request $request)
{
    $request->validate([
        'token' => ['required'],
        'email' => ['required', 'email'],
        'password' => ['required', 'confirmed', PasswordRule::min(8)],
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
        }
    );

    if ($status === Password::PASSWORD_RESET) {
        return redirect()->route('login')->with('success', 'Contraseña actualizada. Ya puedes iniciar sesión.');
    }

    return back()->with('error', 'No se pudo cambiar la contraseña. Verifica el link o intenta de nuevo.');
}










}

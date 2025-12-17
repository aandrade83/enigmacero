<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        // Si no está logueado, mandarlo a login
        if (!Auth::check()) {
            return redirect()->route('login.form');
        }

        $user = Auth::user();

        // Admin por rol (en DB)
        $role = $user->role ?? null;

        if ($role !== 'admin') {
            // Podés cambiar esto a abort(403) si querés
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}

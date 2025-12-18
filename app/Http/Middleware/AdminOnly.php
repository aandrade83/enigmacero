<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form');
        }

        $role = Auth::user()->role ?? null;

        if ($role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'No tiene permisos para acceder a este módulo.');
        }

        return $next($request);
    }
}

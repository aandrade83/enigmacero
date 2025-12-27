<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Usage: ->middleware('role:admin,employee')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        // Normalize roles
        $roles = array_values(array_filter(array_map(fn ($r) => strtolower(trim($r)), $roles)));

        if (!empty($roles) && !in_array(strtolower($user->role ?? ''), $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}

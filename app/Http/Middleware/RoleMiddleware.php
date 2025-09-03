<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    // Usage: ->middleware('role:admin,superadmin')
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // if these routes also use auth:admin, this returns the admin-guard user
        $user = $request->user('admin') ?? $request->user();
        if (! $user) {
            return redirect()->route('admin.login'); // adjust if your route differs
        }

        $current = strtolower((string) ($user->role ?? ''));

        // superadmin bypass
        if ($current === 'superadmin') {
            return $next($request);
        }

        foreach ($roles as $r) {
            if ($current === strtolower($r)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized.');
    }
}

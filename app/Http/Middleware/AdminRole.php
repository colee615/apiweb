<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $adminUser = $request->attributes->get('admin_user');

        if (! $adminUser) {
            abort(403, 'No autorizado.');
        }

        if (empty($roles)) {
            return $next($request);
        }

        $allowed = collect($roles)
            ->map(fn ($role) => trim($role))
            ->filter()
            ->values();

        if (! $allowed->contains($adminUser->role)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}

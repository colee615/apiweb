<?php

namespace App\Http\Middleware;

use App\Services\AuthSessionTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackApiUserSession
{
    public function __construct(
        protected AuthSessionTracker $tracker
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api_users')->user();
        $token = (string) $request->bearerToken();

        if ($user && $token !== '') {
            $this->tracker->markActive(
                $user,
                'api_users',
                hash('sha256', $token),
                $request,
                '/' . ltrim($request->path(), '/')
            );
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\AuthSessionTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionAuth
{
    public function __construct(
        protected AuthSessionTracker $tracker
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->tracker->expireInactive();

        $userId = $request->session()->get('admin_user_id');

        if (! $userId) {
            return redirect()->route('admin.login');
        }

        $user = User::find($userId);

        if (! $user || ! $user->is_active) {
            $request->session()->forget('admin_user_id');

            return redirect()->route('admin.login');
        }

        $this->tracker->markActive(
            $user,
            'admin_web',
            $request->session()->getId(),
            $request
        );

        $request->attributes->set('admin_user', $user);

        view()->share('adminUser', $user);

        return $next($request);
    }
}

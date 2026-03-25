<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->session()->get('admin_user_id');

        if (! $userId) {
            return redirect()->route('admin.login');
        }

        $user = User::find($userId);

        if (! $user || ! $user->is_active) {
            $request->session()->forget('admin_user_id');

            return redirect()->route('admin.login');
        }

        $request->attributes->set('admin_user', $user);

        view()->share('adminUser', $user);

        return $next($request);
    }
}

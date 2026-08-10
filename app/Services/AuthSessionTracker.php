<?php

namespace App\Services;

use App\Models\AuthSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AuthSessionTracker
{
    public function markActive(User $user, string $guardName, string $sessionKey, Request $request, ?string $path = null): AuthSession
    {
        $now = now();

        $session = AuthSession::query()->firstOrNew([
            'guard_name' => $guardName,
            'session_key' => $sessionKey,
        ]);

        if (! $session->exists) {
            $session->logged_in_at = $now;
        }

        $session->fill([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_path' => $path ?? '/' . ltrim($request->path(), '/'),
            'is_active' => true,
            'last_seen_at' => $now,
            'logged_out_at' => null,
        ]);

        $session->save();

        return $session;
    }

    public function markLoggedOut(string $guardName, string $sessionKey): void
    {
        AuthSession::query()
            ->where('guard_name', $guardName)
            ->where('session_key', $sessionKey)
            ->update([
                'is_active' => false,
                'logged_out_at' => now(),
            ]);
    }

    public function expireInactive(int $minutes = 5): void
    {
        $threshold = Carbon::now()->subMinutes($minutes);

        AuthSession::query()
            ->where('is_active', true)
            ->where('last_seen_at', '<', $threshold)
            ->update([
                'is_active' => false,
                'logged_out_at' => null,
            ]);
    }
}

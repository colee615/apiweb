<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthSessionTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function __construct(
        protected AuthSessionTracker $tracker
    ) {
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('admin_user_id')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])
            ->where('is_active', true)
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Credenciales invalidas.'])
                ->withInput();
        }

        $request->session()->regenerate();
        $request->session()->put('admin_user_id', $user->id);
        $this->tracker->markActive($user, 'admin_web', $request->session()->getId(), $request);

        return redirect()->route('admin.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $sessionId = $request->session()->getId();

        $request->session()->forget('admin_user_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $this->tracker->markLoggedOut('admin_web', $sessionId);

        return redirect()->route('admin.login');
    }
}

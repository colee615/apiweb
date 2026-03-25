<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
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

        return redirect()->route('admin.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_user_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}

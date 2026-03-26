<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('created_at', 'desc')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(),
            'mode' => 'create',
            'roles' => User::availableRoles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'job_title' => ['required', 'string', Rule::in(User::availableRoles())],
            'password' => ['required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'job_title' => $data['job_title'],
            'password' => Hash::make($data['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Usuario creado correctamente.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'mode' => 'edit',
            'roles' => User::availableRoles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'job_title' => ['required', 'string', Rule::in(User::availableRoles())],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'job_title' => $data['job_title'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Usuario actualizado correctamente.');
    }
}

@extends('layouts.admin')

@section('content')
<div class="admin-shell stack" x-data="{ openCreate: @json(old('_modal_mode') === 'create' || request()->query('modal') === 'create'), openEdit: @json(old('_modal_mode') === 'edit' ? (int) old('_modal_user_id') : null) }">
    <div class="admin-topbar">
        <div class="admin-brand">
            <h2>Usuarios Administradores</h2>
            <p>Crea y administra accesos para el equipo creativo y de contenido.</p>
        </div>
        <button type="button" class="button button-primary" @click="openCreate = true">Nuevo usuario</button>
    </div>

    <div class="card-grid">
        <div class="spot-card">
            <span>Usuarios</span>
            <strong>{{ $users->count() }}</strong>
            <p>Total de cuentas registradas en el panel.</p>
        </div>
        <div class="spot-card">
            <span>Activos</span>
            <strong>{{ $users->where('is_active', true)->count() }}</strong>
            <p>Cuentas actualmente habilitadas para operar.</p>
        </div>
        <div class="spot-card">
            <span>Administradores</span>
            <strong>{{ $users->filter(fn ($user) => $user->role === 'Administrador')->count() }}</strong>
            <p>Usuarios con acceso completo a todo el sistema.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="notice notice-success">{{ session('status') }}</div>
    @endif

    <div class="table-shell">
        <div class="table-toolbar">
            <div>
                <strong style="font-size:20px;">Equipo del panel</strong>
                <p>Consulta estado, rol y acceso de cada usuario registrado.</p>
            </div>
            <span class="table-note">{{ $users->where('is_active', true)->count() }} activos</span>
        </div>
        <div class="panel-body">
            <table class="page-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role }}</td>
                            <td><span class="pill {{ $user->is_active ? 'pill-ok' : 'pill-off' }}">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                            <td><button type="button" class="button button-secondary" @click="openEdit = {{ $user->id }}">Editar perfil</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-modal-backdrop" x-cloak x-show="openCreate" x-transition.opacity @keydown.escape.window="openCreate = false" @click.self="openCreate = false">
        <div class="admin-modal-card" x-show="openCreate" x-transition>
            <button type="button" class="admin-modal-close" @click="openCreate = false" aria-label="Cerrar">×</button>
            <form method="POST" action="{{ route('admin.users.store') }}" class="stack">
                @csrf
                <input type="hidden" name="_modal_mode" value="create">
                <div>
                    <h3 class="section-title">Nuevo usuario</h3>
                    <p class="section-copy">Completa los datos para crear acceso al panel.</p>
                </div>
                @if ($errors->any() && old('_modal_mode') === 'create')
                    <div class="notice notice-error">{{ $errors->first() }}</div>
                @endif
                <div class="grid grid-2">
                    <div class="field"><label>Nombre</label><input type="text" name="name" value="{{ old('name') }}" required></div>
                    <div class="field"><label>Correo</label><input type="email" name="email" value="{{ old('email') }}" required></div>
                    <div class="field">
                        <label>Rol</label>
                        <select name="job_title" required>
                            @foreach (\App\Models\User::availableRoles() as $role)
                                <option value="{{ $role }}" {{ old('job_title', 'Administrador') === $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label>Contrasena</label><input type="password" name="password" required></div>
                </div>
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}> Usuario activo</label>
                <div class="admin-modal-actions actions">
                    <button type="button" class="button button-ghost" @click="openCreate = false">Cancelar</button>
                    <button type="submit" class="button button-primary">Crear usuario</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($users as $user)
    <div class="admin-modal-backdrop" x-cloak x-show="openEdit === {{ $user->id }}" x-transition.opacity @keydown.escape.window="openEdit = null" @click.self="openEdit = null">
        <div class="admin-modal-card" x-show="openEdit === {{ $user->id }}" x-transition>
            <button type="button" class="admin-modal-close" @click="openEdit = null" aria-label="Cerrar">×</button>
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="stack">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal_mode" value="edit">
                <input type="hidden" name="_modal_user_id" value="{{ $user->id }}">
                <div>
                    <h3 class="section-title">Editar usuario</h3>
                    <p class="section-copy">Actualiza perfil, rol y estado.</p>
                </div>
                @if ($errors->any() && old('_modal_mode') === 'edit' && (int) old('_modal_user_id') === $user->id)
                    <div class="notice notice-error">{{ $errors->first() }}</div>
                @endif
                <div class="grid grid-2">
                    <div class="field"><label>Nombre</label><input type="text" name="name" value="{{ old('_modal_mode') === 'edit' && (int) old('_modal_user_id') === $user->id ? old('name') : $user->name }}" required></div>
                    <div class="field"><label>Correo</label><input type="email" name="email" value="{{ old('_modal_mode') === 'edit' && (int) old('_modal_user_id') === $user->id ? old('email') : $user->email }}" required></div>
                    <div class="field">
                        <label>Rol</label>
                        <select name="job_title" required>
                            @foreach (\App\Models\User::availableRoles() as $role)
                                @php($selectedRole = old('_modal_mode') === 'edit' && (int) old('_modal_user_id') === $user->id ? old('job_title', $user->job_title ?: 'Administrador') : ($user->job_title ?: 'Administrador'))
                                <option value="{{ $role }}" {{ $selectedRole === $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label>Contrasena (opcional)</label><input type="password" name="password"></div>
                </div>
                <label><input type="checkbox" name="is_active" value="1" {{ (old('_modal_mode') === 'edit' && (int) old('_modal_user_id') === $user->id) ? (old('is_active') ? 'checked' : '') : ($user->is_active ? 'checked' : '') }}> Usuario activo</label>
                <div class="admin-modal-actions actions">
                    <button type="button" class="button button-ghost" @click="openEdit = null">Cancelar</button>
                    <button type="submit" class="button button-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endsection

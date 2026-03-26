@extends('layouts.admin')

@section('content')
<div class="admin-shell stack">
    <div class="admin-topbar">
        <div class="admin-brand">
            <h2>{{ $mode === 'create' ? 'Nuevo usuario' : 'Editar usuario' }}</h2>
            <p>Gestiona accesos al panel premium de administracion.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="button button-secondary">Volver</a>
    </div>

    @if ($errors->any())
        <div class="notice notice-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ $mode === 'create' ? route('admin.users.store') : route('admin.users.update', $user) }}" class="panel">
        <div class="panel-body stack">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="toolbar">
                <div>
                    <h3 class="section-title">{{ $mode === 'create' ? 'Alta de usuario' : 'Perfil del usuario' }}</h3>
                    <p class="section-copy">Completa los datos para acceso al panel y control editorial.</p>
                </div>
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}> Usuario activo</label>
            </div>

            <div class="grid grid-2">
                <div class="field"><label>Nombre</label><input type="text" name="name" value="{{ old('name', $user->name) }}" required></div>
                <div class="field"><label>Correo</label><input type="email" name="email" value="{{ old('email', $user->email) }}" required></div>
                <div class="field">
                    <label>Rol</label>
                    <select name="job_title" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ old('job_title', $user->job_title ?: 'Administrador') === $role ? 'selected' : '' }}>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>Contrasena {{ $mode === 'edit' ? '(solo si deseas cambiarla)' : '' }}</label><input type="password" name="password" {{ $mode === 'create' ? 'required' : '' }}></div>
            </div>

            <div class="actions">
                <button type="submit" class="button button-primary">{{ $mode === 'create' ? 'Crear usuario' : 'Guardar cambios' }}</button>
            </div>
        </div>
    </form>
</div>
@endsection

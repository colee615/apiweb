@extends('layouts.admin')

@section('content')
<div class="admin-shell stack">
    <div class="admin-topbar">
        <div class="admin-brand">
            <h2>Usuarios Administradores</h2>
            <p>Crea y administra accesos para el equipo creativo y de contenido.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="button button-primary">Nuevo usuario</a>
    </div>

    <div class="page-hero">
        <div class="page-hero-grid">
            <div class="hero-card">
                <span class="hero-kicker">Accesos y permisos</span>
                <h1 class="hero-title">Controla quién administra y quién solo gestiona diseño.</h1>
                <p class="hero-lead">Los administradores tienen acceso completo al panel. Los gestores trabajan solo sobre diseño, manteniendo una operación más segura y ordenada.</p>
            </div>
            <div class="info-list">
                <div class="info-item">
                    <strong>Administrador</strong>
                    <span>Acceso total a diseño, historial, usuarios y configuración del panel.</span>
                </div>
                <div class="info-item">
                    <strong>Gestor</strong>
                    <span>Acceso limitado al submenú de diseño para edición de contenido y vistas.</span>
                </div>
            </div>
        </div>
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
                            <td><a href="{{ route('admin.users.edit', $user) }}" class="button button-secondary">Editar perfil</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

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

    <div class="stat-grid">
        <div class="stat-card"><span class="muted">Usuarios totales</span><strong>{{ $users->count() }}</strong></div>
        <div class="stat-card"><span class="muted">Activos</span><strong>{{ $users->where('is_active', true)->count() }}</strong></div>
        <div class="stat-card"><span class="muted">Roles visibles</span><strong>{{ $users->pluck('job_title')->filter()->unique()->count() }}</strong></div>
        <div class="stat-card"><span class="muted">Control</span><strong>Web</strong></div>
    </div>

    @if (session('status'))
        <div class="notice notice-success">{{ session('status') }}</div>
    @endif

    <div class="panel">
        <div class="panel-body">
            <table class="page-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Cargo</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->job_title ?: 'Sin cargo' }}</td>
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

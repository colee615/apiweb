@extends('layouts.admin')

@section('content')
<div class="admin-shell stack">
    <div class="admin-topbar">
        <div class="admin-brand">
            <h1>Usuarios y accesos</h1>
            <p>Crea y administra accesos para el equipo editorial.</p>
        </div>
        <a class="button button-primary" href="{{ route('admin.users.create') }}">Nuevo usuario</a>
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
            <div class="field search-field"><label for="user-search" class="sr-only">Buscar usuarios</label><input id="user-search" type="search" data-table-search placeholder="Buscar nombre, correo o rol…"></div>
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
                        <tr data-search-row>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role }}</td>
                            <td><span class="pill {{ $user->is_active ? 'pill-ok' : 'pill-off' }}">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                            <td><a class="button button-secondary" href="{{ route('admin.users.edit', $user) }}">Editar perfil</a></td>
                        </tr>
                    @endforeach
                <tr data-search-empty hidden><td colspan="5" class="empty-note">No se encontraron usuarios.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

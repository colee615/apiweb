@extends('layouts.admin')

@section('content')
<div class="admin-shell stack">
    <div class="admin-topbar">
        <div class="admin-brand">
            <h2>Vistas del Sitio</h2>
            <p>Administra las pantallas publicas con un flujo visual y ordenado.</p>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="button button-secondary">Cerrar sesion</button></form>
    </div>

    <div class="stat-grid">
        <div class="stat-card"><span class="muted">Paginas</span><strong>{{ $pages->count() }}</strong></div>
        <div class="stat-card"><span class="muted">Secciones activas</span><strong>{{ $pages->sum('sections_count') }}</strong></div>
        <div class="stat-card"><span class="muted">Modo de trabajo</span><strong>Visual</strong></div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <table class="page-table">
                <thead>
                    <tr>
                        <th>Pagina</th>
                        <th>Slug</th>
                        <th>Secciones</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pages as $page)
                        <tr>
                            <td><strong>{{ $page->name }}</strong><br><span class="muted">{{ $page->meta_title }}</span></td>
                            <td>{{ $page->slug }}</td>
                            <td>{{ $page->sections_count }}</td>
                            <td><span class="pill {{ $page->is_active ? 'pill-ok' : 'pill-off' }}">{{ $page->is_active ? 'Activa' : 'Inactiva' }}</span></td>
                            <td><a href="{{ route('admin.pages.edit', $page) }}" class="button button-primary">Abrir editor</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

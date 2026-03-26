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

    <div class="page-hero">
        <div class="page-hero-grid">
            <div class="hero-card">
                <span class="hero-kicker">Panel de diseño</span>
                <h1 class="hero-title">Gestiona las vistas del sitio con una experiencia más editorial.</h1>
                <p class="hero-lead">Cada página concentra diseño, bloques, contenidos e historial en un solo espacio. La idea es que el equipo trabaje con claridad, menos ruido visual y mejor control sobre lo publicado.</p>
            </div>
            <div class="info-list">
                <div class="info-item">
                    <strong>Edición centralizada</strong>
                    <span>Abre una vista y gestiona diseño, contenidos, medios e historial desde el mismo flujo.</span>
                </div>
                <div class="info-item">
                    <strong>Control por versiones</strong>
                    <span>Cada guardado mantiene trazabilidad, usuario responsable y capacidad de restauración.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card-grid">
        <div class="spot-card">
            <span>Paginas</span>
            <strong>{{ $pages->count() }}</strong>
            <p>Vistas disponibles para administrar desde el panel.</p>
        </div>
        <div class="spot-card">
            <span>Secciones</span>
            <strong>{{ $pages->sum('sections_count') }}</strong>
            <p>Bloques activos distribuidos entre las páginas del sitio.</p>
        </div>
        <div class="spot-card">
            <span>Modo</span>
            <strong>Visual</strong>
            <p>Interfaz pensada para edición funcional sin tocar código.</p>
        </div>
    </div>

    <div class="table-shell">
        <div class="table-toolbar">
            <div>
                <strong style="font-size:20px;">Vistas disponibles</strong>
                <p>Selecciona una página para entrar al editor completo.</p>
            </div>
            <span class="table-note">{{ $pages->where('is_active', true)->count() }} publicadas</span>
        </div>
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
                            <td><strong>{{ $page->name }}</strong><br><span class="muted">{{ $page->meta_title ?: 'Sin titulo SEO' }}</span></td>
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

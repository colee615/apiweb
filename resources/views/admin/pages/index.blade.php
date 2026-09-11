@extends('layouts.admin')

@section('content')
@php
    $homePage = $pages->firstWhere('slug', 'home');
    $hasEmsEditor = $homePage && (int) ($homePage->ems_sections_count ?? 0) > 0;
    $publishedCount = $pages->where('is_active', true)->count() + ($hasEmsEditor && $homePage->is_active ? 1 : 0);
@endphp

<div class="admin-shell stack">
    <div class="admin-topbar">
        <div class="admin-brand">
            <h2>Panel de administracion</h2>
            <p>Gestiona el contenido del sitio y entra al panel de estadisticas del frontweb.</p>
        </div>
        <a href="{{ route('admin.analytics') }}" class="button button-primary">Ver estadisticas</a>
    </div>

    <div class="card-grid">
        <div class="spot-card">
            <span>Paginas</span>
            <strong>{{ $pages->count() + ($hasEmsEditor ? 1 : 0) }}</strong>
            <p>Vistas disponibles para administrar desde el panel.</p>
        </div>
        <div class="spot-card">
            <span>Secciones</span>
            <strong>{{ $pages->sum('sections_count') }}</strong>
            <p>Bloques activos distribuidos entre las paginas del sitio.</p>
        </div>
        <div class="spot-card">
            <span>Modo</span>
            <strong>Visual</strong>
            <p>Edicion funcional sin tocar codigo.</p>
        </div>
    </div>

    <div class="table-shell">
        <div class="table-toolbar">
            <div>
                <strong style="font-size:20px;">Vistas disponibles</strong>
                <p>Selecciona una pagina para abrir el editor.</p>
            </div>
            <span class="table-note">{{ $publishedCount }} publicadas</span>
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
                    @if ($hasEmsEditor)
                        <tr>
                            <td><strong>EMS</strong><br><span class="muted">Express Mail Service | Correos de Bolivia</span></td>
                            <td>ems</td>
                            <td>{{ $homePage->ems_sections_count }}</td>
                            <td><span class="pill {{ $homePage->is_active ? 'pill-ok' : 'pill-off' }}">{{ $homePage->is_active ? 'Activa' : 'Inactiva' }}</span></td>
                            <td><a href="{{ route('admin.pages.edit', $homePage) }}?tab=ems" class="button button-primary">Abrir editor</a></td>
                        </tr>
                    @endif
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

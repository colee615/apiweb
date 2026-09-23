@extends('layouts.admin')
@section('content')
<div class="admin-shell stack">
    <header class="admin-topbar"><div class="admin-brand"><h1>Contenido del sitio</h1><p>Administra las páginas, sus imágenes y la información de los servicios.</p></div><a href="{{ route('admin.analytics') }}" class="button button-ghost">Ver estadísticas ↗</a></header>
    <div class="card-grid">
        <article class="spot-card"><span>Páginas</span><strong>{{ $pages->count() }}</strong><p>Páginas disponibles para editar.</p></article>
        <article class="spot-card"><span>Publicadas</span><strong>{{ $pages->where('is_active', true)->count() }}</strong><p>Páginas habilitadas en el sitio.</p></article>
        <article class="spot-card"><span>Secciones</span><strong>{{ $pages->sum('sections_count') }}</strong><p>Bloques de contenido administrables.</p></article>
    </div>
    <section class="table-shell">
        <div class="table-toolbar"><div><strong>Páginas del sitio</strong><p>Selecciona una página para editar su contenido.</p></div><div class="field search-field"><label for="page-search" class="sr-only">Buscar páginas</label><input id="page-search" type="search" data-table-search placeholder="Buscar una página…"></div></div>
        <div class="panel-body"><table class="page-table"><thead><tr><th>Página</th><th>Secciones</th><th>Estado</th><th>Último cambio</th><th>Acciones</th></tr></thead><tbody>
            @forelse($pages as $page)
                <tr data-search-row><td><strong>{{ $page->name }}</strong><br><span class="muted">/{{ $page->slug }}</span></td><td>{{ $page->sections_count }}</td><td><span class="pill {{ $page->is_active ? 'pill-ok' : 'pill-off' }}">{{ $page->is_active ? 'Publicada' : 'Inactiva' }}</span></td><td><span class="muted">{{ $page->updated_at?->format('d/m/Y H:i') ?? '—' }}</span></td><td><a class="button button-secondary" href="{{ route('admin.pages.edit', $page) }}">Editar página</a></td></tr>
            @empty<tr><td colspan="5" class="empty-note">Todavía no hay páginas disponibles.</td></tr>@endforelse
            <tr data-search-empty hidden><td colspan="5" class="empty-note">No se encontraron páginas con esa búsqueda.</td></tr>
        </tbody></table></div>
    </section>
    <p class="section-copy">El logo, los colores, el encabezado y el pie se administran desde <a class="back-link" href="{{ route('admin.global.edit') }}">Configuración global</a>. Cada servicio, incluido EMS, se edita en su propia página.</p>
</div>
@endsection

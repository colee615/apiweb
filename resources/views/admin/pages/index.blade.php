@extends('layouts.admin')

@section('content')
<div class="admin-shell stack">
    <div class="card-grid">
        <div class="spot-card">
            <span>Paginas</span>
            <strong>{{ $pages->count() }}</strong>
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

<header class="admin-topbar editor-heading">
    <div class="admin-brand">
        <a class="back-link" href="{{ route('admin.dashboard') }}">← Todas las páginas</a>
        <h1>{{ $page->name }} <span class="pill {{ $page->is_active ? 'pill-ok' : 'pill-off' }}">{{ $page->is_active ? 'Publicada' : 'Inactiva' }}</span></h1>
        <p>Edita el contenido por sección y guarda los cambios al terminar.</p>
    </div>
    <a class="button button-ghost" href="{{ route('admin.pages.edit', ['page' => $page, 'tab' => 'history_overview']) }}">Ver historial</a>
</header>

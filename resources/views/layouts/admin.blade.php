<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? match (true) {
        request()->routeIs('admin.analytics') => 'Estadísticas',
        request()->routeIs('admin.global.*') => 'Configuración global',
        request()->routeIs('admin.dashboard') => 'Contenido del sitio',
        request()->routeIs('admin.users.*') => 'Usuarios y accesos',
        request()->routeIs('admin.login*') => 'Acceso',
        default => ($page->name ?? 'Administración'),
    } }} · Correos de Bolivia</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @if ((request()->routeIs('admin.pages.edit') && !str_starts_with((string) request('tab', ''), 'history')) || request()->routeIs('admin.global.edit'))
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v=2">
</head>
<body>
<a class="skip-link" href="{{ request()->routeIs('admin.login*') ? '#login-content' : '#main-content' }}">Saltar al contenido</a>
@php($isLogin = request()->routeIs('admin.login') || request()->routeIs('admin.login.store'))

@if ($isLogin)
    @yield('content')
@else
    @php($requestedTab = (string) request('tab', ''))
    @php($isPageEdit = request()->routeIs('admin.pages.edit'))
    @php($isHistoryMode = $isPageEdit && str_starts_with($requestedTab, 'history'))
    @php($homePageId = \App\Models\SitePage::query()->where('slug', 'home')->value('id'))
    @php($historyUrl = $isPageEdit
        ? (request()->url() . '?tab=history_overview#history-root')
        : ($homePageId
            ? route('admin.pages.edit', ['page' => $homePageId, 'tab' => 'history_overview']) . '#history-root'
            : route('admin.dashboard')))
    <div class="admin-app" x-data="{ sidebarOpen: false, sidebarCollapsed: false }" :class="{ 'sidebar-open': sidebarOpen, 'sidebar-collapsed': sidebarCollapsed }">
        <button x-cloak x-show="sidebarOpen" class="sidebar-backdrop" @click="sidebarOpen = false" aria-label="Cerrar menú"></button>
        <aside id="admin-navigation" class="sidebar" @keydown.escape.window="sidebarOpen = false">
            <div class="brand">
                <a class="brand-home" href="{{ route('admin.dashboard') }}"><span class="brand-mark">C<span>↗</span></span><strong>Correos de Bolivia</strong></a>
                <p>Panel administrativo</p>
            </div>

            <div class="nav-caption">ADMINISTRACIÓN</div>
            <nav class="nav-group" aria-label="Navegación principal">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') || (request()->routeIs('admin.pages.*') && !$isHistoryMode) ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <span>Contenido del sitio</span>
                    <span aria-hidden="true">▤</span>
                </a>
                <a class="nav-link {{ request()->routeIs('admin.global.*') ? 'active' : '' }}" href="{{ route('admin.global.edit') }}">
                    <span>Configuración global</span>
                    <span aria-hidden="true">⚙</span>
                </a>
                <a class="nav-link {{ request()->routeIs('admin.analytics') ? 'active' : '' }}" href="{{ route('admin.analytics') }}">
                    <span>Estadísticas</span>
                    <span aria-hidden="true">↗</span>
                </a>
                <a class="nav-link {{ $isHistoryMode ? 'active' : '' }}" href="{{ $historyUrl }}">
                    <span>Historial</span>
                    <span aria-hidden="true">◷</span>
                </a>
                @if (($adminUser->role ?? 'Administrador') === 'Administrador')
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <span>Usuarios</span>
                    <span aria-hidden="true">♙</span>
                </a>
                @endif
            </nav>

        </aside>

        <main class="content">
            <div class="topbar">
                <button type="button" class="menu-toggle" @click="window.innerWidth <= 1080 ? sidebarOpen = !sidebarOpen : sidebarCollapsed = !sidebarCollapsed" aria-label="Alternar menú" aria-controls="admin-navigation" :aria-expanded="window.innerWidth <= 1080 ? sidebarOpen : !sidebarCollapsed">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <line x1="4" y1="7" x2="20" y2="7"></line>
                        <line x1="4" y1="12" x2="20" y2="12"></line>
                        <line x1="4" y1="17" x2="20" y2="17"></line>
                    </svg>
                </button>
                <div class="topbar-actions">
                    <span class="account-name">{{ $adminUser->name ?? 'Equipo editorial' }}<small>{{ $adminUser->role ?? '' }}</small></span>
                    <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="topbar-logout">Cerrar sesión</button>
                    </form>
                </div>
            </div>
            <div id="main-content" class="content-inner" tabindex="-1">
                @yield('content')
            </div>
        </main>
    </div>
@endif

    @if ((request()->routeIs('admin.pages.edit') && !$isHistoryMode) || request()->routeIs('admin.global.edit'))
        @include('admin.pages.partials.collections')
        <script src="{{ asset('js/admin-collections.js') }}?v=3" defer></script>
        <script src="{{ asset('js/admin-media.js') }}?v=2" defer></script>
    @endif

    <script src="{{ asset('js/admin.js') }}?v=2" defer></script>
</body>
</html>


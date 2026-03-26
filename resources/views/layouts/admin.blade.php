<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Studio Admin' }}</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #eef3f9;
            --panel: rgba(255,255,255,.88);
            --panel-solid: #ffffff;
            --line: #dfe6f3;
            --line-strong: #cfd8ea;
            --text: #0f172a;
            --muted: #667085;
            --primary: #0f4c81;
            --primary-dark: #0a3559;
            --accent: #123047;
            --accent-soft: #214d68;
            --success: #0c7a58;
            --sidebar: #0d1b2a;
            --sidebar-soft: #18364d;
            --shadow: 0 20px 60px rgba(18, 48, 71, 0.12);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Manrope", Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(15, 76, 129, .10), transparent 24%),
                radial-gradient(circle at top right, rgba(18, 48, 71, .08), transparent 22%),
                var(--bg);
            color: var(--text);
        }
        a { color: inherit; text-decoration: none; }
        .admin-app { min-height: 100vh; display: grid; grid-template-columns: 290px 1fr; }
        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            background:
                radial-gradient(circle at top, rgba(255,255,255,.10), transparent 28%),
                linear-gradient(180deg, #0d1b2a, #123047 52%, #1d516f);
            color: #fff;
            padding: 28px 20px;
        }
        .brand {
            padding: 20px;
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.03));
            backdrop-filter: blur(12px);
            margin-bottom: 22px;
        }
        .brand-badge {
            display: inline-flex;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            background: rgba(255,255,255,.08);
            margin-bottom: 14px;
        }
        .brand h1 { margin: 0; font-size: 24px; font-weight: 800; }
        .brand p { margin: 10px 0 0; color: rgba(255,255,255,.72); line-height: 1.5; font-size: 14px; }
        .nav-group { display: grid; gap: 12px; }
        .nav-link {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 16px 18px; border-radius: 20px;
            color: rgba(255,255,255,.86); border: 1px solid transparent;
            transition: .2s ease;
        }
        .nav-link:hover { background: rgba(255,255,255,.07); transform: translateX(2px); }
        .nav-link.active {
            background: linear-gradient(135deg, rgba(255,255,255,.16), rgba(255,255,255,.07));
            border-color: rgba(255,255,255,.14);
            color: #fff;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.10), 0 12px 24px rgba(0,0,0,.12);
        }
        .nav-link-meta { display:grid; gap:4px; }
        .nav-link-title { font-weight: 800; }
        .nav-link-copy { font-size: 12px; color: rgba(255,255,255,.62); }
        .nav-link.active .nav-link-copy { color: rgba(255,255,255,.82); }
        .nav-link span:last-child {
            width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 12px; background: rgba(255,255,255,.07); font-size: 12px; font-weight: 800;
        }
        .role-chip {
            display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px;
            background: rgba(255,255,255,.10); color:#fff; font-size:12px; font-weight:800; letter-spacing:.04em;
        }
        .sidebar-footer {
            margin-top: 18px; padding: 18px; border-radius: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,.10), rgba(255,255,255,.05)); color: rgba(255,255,255,.86); font-size: 14px;
            border: 1px solid rgba(255,255,255,.10);
        }
        .content { padding: 34px 30px; }
        .admin-shell { max-width: 1380px; margin: 0 auto; }
        .admin-topbar { display: flex; justify-content: space-between; align-items: center; gap: 18px; margin-bottom: 24px; }
        .admin-brand h2 { margin: 0; font-size: 34px; font-weight: 800; }
        .admin-brand p { margin: 8px 0 0; color: var(--muted); }
        .panel {
            background: var(--panel); border: 1px solid rgba(255,255,255,.7); border-radius: 28px;
            box-shadow: var(--shadow); backdrop-filter: blur(14px);
        }
        .panel-solid { background: var(--panel-solid); }
        .panel-body { padding: 24px; }
        .button {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            border: 0; border-radius: 16px; padding: 12px 18px; cursor: pointer; font-weight: 800;
            font-family: inherit; transition: .2s ease;
        }
        .button-primary { background: linear-gradient(135deg, var(--primary), #2c6ec8); color: #fff; box-shadow: 0 10px 24px rgba(32, 83, 154, .22); }
        .button-primary:hover { transform: translateY(-1px); }
        .button-secondary { background: #edf2f8; color: var(--accent); }
        .button-ghost { background: transparent; border: 1px solid var(--line); color: var(--accent); }
        .button-danger { background: #fdeaea; color: var(--primary); }
        .stack { display: grid; gap: 20px; }
        .grid { display: grid; gap: 16px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0,1fr)); }
        .grid-4 { grid-template-columns: repeat(4, minmax(0,1fr)); }
        .field label { display:block; font-size:12px; font-weight:800; margin-bottom:8px; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; }
        .field input, .field textarea, .field select {
            width:100%; border:1px solid var(--line); border-radius:16px; padding:14px 15px; font:inherit; background:#fff;
            transition: .2s ease;
        }
        .field input:focus, .field textarea:focus, .field select:focus {
            outline: none; border-color: #b8c7df; box-shadow: 0 0 0 4px rgba(16, 37, 66, .06);
        }
        .field textarea { min-height: 120px; resize: vertical; }
        .field textarea.field-small { min-height: 90px; }
        .field-help {
            margin-top: 8px;
            font-size: 12px;
            line-height: 1.55;
            color: var(--muted);
        }
        .field-help strong {
            color: var(--accent);
            font-weight: 800;
        }
        .notice { padding:16px 18px; border-radius:18px; margin-bottom:16px; font-weight: 700; }
        .notice-success { background:#eaf9f2; color:var(--success); }
        .notice-error { background:#fdeaea; color:var(--primary); }
        .page-table { width:100%; border-collapse:collapse; }
        .page-table th, .page-table td { padding:18px 12px; border-bottom:1px solid var(--line); text-align:left; vertical-align:top; }
        .page-table th { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .06em; }
        .pill { display:inline-block; padding:7px 11px; border-radius:999px; font-size:12px; font-weight:800; }
        .pill-ok { background:#eaf9f2; color:var(--success); }
        .pill-off { background:#eef2f8; color:var(--muted); }
        .muted { color:var(--muted); }
        .login-wrap {
            min-height:100vh; display:grid; place-items:center; padding:24px;
            background:
                radial-gradient(circle at top, rgba(74, 144, 226, .22), transparent 26%),
                linear-gradient(160deg, #10263a, #0d1b2a 48%, #173b55);
        }
        .login-card { width:min(520px,100%); }
        .toolbar { display:flex; flex-wrap:wrap; gap:12px; justify-content:space-between; align-items:center; }
        .actions { display:flex; gap:10px; flex-wrap:wrap; }
        .section-title { margin:0 0 6px; font-size:24px; font-weight: 800; }
        .section-copy { margin:0; color:var(--muted); line-height: 1.5; }
        .page-hero {
            padding: 28px;
            border-radius: 30px;
            background:
                radial-gradient(circle at top right, rgba(15,76,129,.16), transparent 28%),
                linear-gradient(135deg, #ffffff, #f6f9fc);
            border: 1px solid rgba(255,255,255,.8);
            box-shadow: var(--shadow);
        }
        .page-hero-grid {
            display:grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(320px, .7fr);
            gap: 18px;
            align-items: stretch;
        }
        .hero-card {
            padding: 22px;
            border-radius: 24px;
            background: rgba(255,255,255,.78);
            border: 1px solid rgba(255,255,255,.9);
        }
        .hero-kicker {
            display:inline-flex; padding:8px 12px; border-radius:999px; background:#e8f0f8;
            color:var(--accent); font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.06em;
        }
        .hero-title {
            margin: 16px 0 12px;
            font-size: 42px;
            line-height: .98;
        }
        .hero-lead {
            margin: 0;
            max-width: 760px;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.65;
        }
        .info-list { display:grid; gap:12px; }
        .info-item {
            padding: 16px 18px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: linear-gradient(180deg, #fff, #f8fbff);
        }
        .info-item strong { display:block; margin-bottom:6px; }
        .info-item span { color: var(--muted); font-size: 14px; line-height: 1.5; }
        .spec-grid {
            display:grid;
            grid-template-columns: repeat(3, minmax(0,1fr));
            gap: 12px;
        }
        .spec-card {
            padding: 16px 18px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: linear-gradient(180deg, #fff, #f8fbff);
        }
        .spec-card strong {
            display:block;
            margin-bottom: 6px;
        }
        .spec-card span {
            display:block;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
        }
        .card-grid {
            display:grid;
            grid-template-columns: repeat(3, minmax(0,1fr));
            gap: 16px;
        }
        .spot-card {
            padding: 20px;
            border-radius: 24px;
            border: 1px solid var(--line);
            background: linear-gradient(180deg, #fff, #f9fbff);
        }
        .spot-card strong {
            display:block;
            font-size: 28px;
            margin-top: 10px;
        }
        .spot-card span {
            font-size: 12px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .spot-card p {
            margin: 10px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.55;
        }
        .table-shell {
            overflow: hidden;
            border-radius: 26px;
            background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(247,249,253,.95));
            border: 1px solid rgba(255,255,255,.9);
            box-shadow: var(--shadow);
        }
        .table-toolbar {
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:16px;
            padding: 22px 24px 0;
        }
        .table-toolbar p { margin: 8px 0 0; color: var(--muted); }
        .table-shell .panel-body { padding-top: 12px; }
        .table-note {
            display:inline-flex;
            padding: 8px 12px;
            border-radius: 999px;
            background: #edf3f9;
            color: var(--accent);
            font-size: 12px;
            font-weight: 800;
        }
        .repeater-card {
            border:1px solid var(--line); border-radius:22px; padding:18px; background:#fff;
            box-shadow: 0 10px 30px rgba(16, 37, 66, .06);
        }
        .repeater-card + .repeater-card { margin-top:12px; }
        .thumb {
            width:100%; max-width:240px; border-radius:18px; border:1px solid var(--line);
            background:#f8fafc; display:block; object-fit:cover; aspect-ratio: 16/10;
        }
        .stat-grid { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:16px; }
        .stat-card {
            padding:22px; border-radius:24px; background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(248,250,254,.92));
            border:1px solid rgba(255,255,255,.9); box-shadow: var(--shadow);
        }
        .stat-card strong { display:block; font-size:32px; margin-top:10px; }
        .hero-panel {
            padding: 28px;
            background:
                radial-gradient(circle at top right, rgba(15,76,129,.16), transparent 26%),
                radial-gradient(circle at top left, rgba(18,48,71,.12), transparent 22%),
                linear-gradient(135deg, #ffffff, #f7f9fd);
        }
        .admin-hero-band {
            display:flex; justify-content:space-between; gap:18px; align-items:center;
            padding: 18px 22px; border-radius: 24px;
            background: linear-gradient(135deg, rgba(15,76,129,.08), rgba(255,255,255,.7));
            border: 1px solid rgba(15,76,129,.10);
            margin-bottom: 22px;
        }
        .admin-hero-band strong { display:block; margin-bottom:4px; }
        .admin-hero-band p { margin:0; color: var(--muted); }
        .admin-hero-metrics { display:flex; gap:12px; flex-wrap:wrap; }
        .metric-pill {
            padding: 12px 14px; border-radius: 18px; background:#fff; border:1px solid var(--line);
            min-width: 140px;
        }
        .metric-pill strong { display:block; font-size: 20px; }
        .metric-pill span { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }
        .tab-bar {
            display:flex; gap:10px; flex-wrap:wrap; padding:12px;
            background:#fff; border:1px solid var(--line); border-radius:22px; position: sticky; top: 16px; z-index: 10;
            box-shadow: 0 14px 40px rgba(16,37,66,.08);
        }
        .tab-chip {
            border:0; border-radius:14px; padding:12px 14px; cursor:pointer; font:inherit; font-weight:800;
            background:transparent; color:var(--muted);
        }
        .tab-chip.active { background:linear-gradient(135deg, var(--accent), var(--accent-soft)); color:#fff; }
        .upload-card {
            display:grid; gap:12px; padding:16px; border-radius:20px; border:1px dashed var(--line-strong);
            background: linear-gradient(180deg, #fff, #f9fbff);
        }
        .upload-meta { font-size: 13px; color: var(--muted); }
        .drag-handle {
            display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:12px;
            background:#f4f7fb; color:var(--muted); cursor:grab; font-weight:800;
        }
        .split-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; }
        .preview-grid { display:grid; grid-template-columns: 1.3fr .7fr; gap:20px; }
        .preview-card { padding:18px; border:1px solid var(--line); border-radius:24px; background:linear-gradient(180deg,#fff,#f8fafe); }
        .mini-canvas {
            min-height: 220px; border-radius: 24px; padding: 22px;
            background: linear-gradient(135deg, rgba(16,37,66,.96), rgba(25,43,70,.78)), var(--hero-bg, #102542);
            color:#fff; display:grid; align-content:space-between;
        }
        .editor-layout { display:grid; grid-template-columns: 260px minmax(0, 1fr); gap: 24px; align-items:start; }
        .editor-sidebar, .editor-preview { position: sticky; top: 22px; }
        .editor-nav {
            padding: 18px;
            border-radius: 24px;
            border: 1px solid var(--line);
            background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(247,249,253,.95));
            box-shadow: var(--shadow);
        }
        .editor-nav h3, .editor-preview h3 { margin: 0 0 8px; font-size: 18px; }
        .editor-nav p, .editor-preview p { margin: 0; color: var(--muted); line-height: 1.5; font-size: 14px; }
        .editor-nav-list { display: grid; gap: 10px; margin-top: 18px; }
        .editor-nav-button {
            width: 100%; text-align: left; border: 1px solid var(--line); border-radius: 18px; padding: 14px 15px;
            background: #fff; font: inherit; cursor: pointer; transition: .18s ease;
        }
        .editor-nav-button strong { display:block; font-size: 14px; margin-bottom: 4px; }
        .editor-nav-button span { display:block; font-size: 12px; color: var(--muted); }
        .editor-nav-button.active {
            background: linear-gradient(135deg, var(--accent), var(--accent-soft));
            color: #fff; border-color: transparent; box-shadow: 0 14px 30px rgba(16,37,66,.18);
        }
        .editor-nav-button.active span { color: rgba(255,255,255,.8); }
        .editor-main { display: grid; gap: 18px; }
        .section-card {
            padding: 24px;
            border-radius: 26px;
            background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(247,249,253,.94));
            border: 1px solid rgba(255,255,255,.8);
            box-shadow: var(--shadow);
        }
        .section-card[hidden] { display: none !important; }
        .section-eyebrow {
            display:inline-flex; padding:8px 12px; border-radius:999px; background:#eef3fb; color:var(--accent);
            font-size:12px; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
        }
        .section-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:18px; }
        .section-metrics { display:flex; gap:10px; flex-wrap:wrap; }
        .design-grid { display:grid; grid-template-columns: repeat(12, minmax(0,1fr)); gap: 16px; }
        .span-3 { grid-column: span 3; }
        .span-4 { grid-column: span 4; }
        .span-6 { grid-column: span 6; }
        .span-8 { grid-column: span 8; }
        .span-9 { grid-column: span 9; }
        .span-12 { grid-column: 1 / -1; }
        .subpanel {
            padding: 18px;
            border-radius: 22px;
            border: 1px solid var(--line);
            background: #fff;
        }
        .subpanel h4 { margin: 0 0 6px; font-size: 17px; }
        .subpanel p { margin: 0 0 14px; color: var(--muted); font-size: 14px; line-height: 1.5; }
        .palette-grid { display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 16px; }
        .color-token { display:grid; gap: 10px; }
        .color-swatch-card {
            display:grid; gap:8px; padding: 12px; border-radius: 18px; border: 1px solid var(--line);
            background: linear-gradient(180deg, #fff, #f8fbff);
        }
        .color-swatch {
            width: 100%; height: 54px; border-radius: 14px; border: 1px solid rgba(16, 37, 66, .08);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.45);
        }
        .color-swatch-value {
            font-size: 12px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: var(--muted);
        }
        .image-frame {
            display:grid; gap:12px; padding:16px; border-radius:22px; border:1px dashed var(--line-strong);
            background: linear-gradient(180deg, #fff, #f8fbff);
        }
        .save-dock {
            position: sticky; bottom: 18px; display:flex; justify-content:space-between; align-items:center; gap:14px;
            padding: 16px 18px; border-radius: 22px; background: rgba(15,23,42,.94); color: #fff;
            box-shadow: 0 20px 45px rgba(15,23,42,.28);
        }
        .save-dock p { margin:0; color: rgba(255,255,255,.74); font-size: 14px; }
        .mini-service-grid { display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap:12px; }
        .mini-service { padding:12px; border-radius:18px; background:#fff; border:1px solid var(--line); min-height:110px; }
        .empty-note { padding: 18px; border-radius: 18px; border: 1px dashed var(--line); color: var(--muted); background: #fbfcfe; text-align: center; }
        .form-card { padding: 22px; border-radius: 24px; background: #fff; border: 1px solid var(--line); box-shadow: var(--shadow); }
        @media (max-width: 1320px) { .editor-layout { grid-template-columns: 1fr; } .editor-sidebar, .editor-preview { position: static; } }
        @media (max-width: 1180px) { .preview-grid { grid-template-columns: 1fr; } .stat-grid { grid-template-columns: repeat(2, minmax(0,1fr)); } .page-hero-grid, .card-grid, .spec-grid { grid-template-columns: 1fr; } }
        @media (max-width: 1080px) { .admin-app { grid-template-columns:1fr; } .sidebar { position: static; height: auto; } }
        @media (max-width: 900px) {
            .grid-2, .grid-3, .grid-4, .stat-grid, .mini-service-grid, .design-grid, .palette-grid { grid-template-columns:1fr; }
            .span-3, .span-4, .span-6, .span-8, .span-9, .span-12 { grid-column: auto; }
            .admin-topbar, .toolbar, .actions, .split-header { align-items:flex-start; flex-direction:column; }
            .content { padding: 18px; }
        }
    </style>
</head>
<body>
@php($isLogin = request()->routeIs('admin.login') || request()->routeIs('admin.login.store'))

@if ($isLogin)
    @yield('content')
@else
    @php($requestedTab = (string) request('tab', ''))
    @php($isPageEdit = request()->routeIs('admin.pages.edit'))
    @php($isHistoryMode = $isPageEdit && str_starts_with($requestedTab, 'history'))
    @php($historyUrl = $isPageEdit ? (request()->url() . '?tab=history_overview#history-root') : route('admin.dashboard'))
    <div class="admin-app">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-badge">Creative Control</div>
                <h1>Studio Admin</h1>
                <p>Panel visual para gestionar vistas, usuarios, imagenes y bloques del sitio.</p>
            </div>

            <div class="role-chip" style="margin-bottom:18px;">
                <span>Rol</span>
                <strong>{{ $adminUser->role ?? 'Administrador' }}</strong>
            </div>

            <nav class="nav-group">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') || (request()->routeIs('admin.pages.*') && !$isHistoryMode) ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <span>Diseño</span>
                    <span>01</span>
                </a>
                <a class="nav-link {{ $isHistoryMode ? 'active' : '' }}" href="{{ $historyUrl }}">
                    <span>Historial</span>
                    <span>02</span>
                </a>
                @if (($adminUser->role ?? 'Administrador') === 'Administrador')
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <span>Usuarios</span>
                    <span>03</span>
                </a>
                @endif
            </nav>

            <div class="sidebar-footer">
                <strong style="display:block; margin-bottom:8px;">Sesion activa</strong>
                <div>{{ $adminUser->name ?? 'Administrador' }}</div>
                <div style="margin-top:4px;">{{ $adminUser->role ?? 'Administrador' }}</div>
            </div>
        </aside>

        <main class="content">
            <div class="admin-hero-band">
                <div>
                    <strong>Centro de control editorial</strong>
                    <p>Una interfaz mas clara y agradable para administrar el sitio.</p>
                </div>
                <div class="admin-hero-metrics">
                    <div class="metric-pill">
                        <span>Acceso</span>
                        <strong>{{ $adminUser->role ?? 'Administrador' }}</strong>
                    </div>
                    <div class="metric-pill">
                        <span>Estado</span>
                        <strong>{{ ($adminUser->is_active ?? true) ? 'Activo' : 'Restringido' }}</strong>
                    </div>
                </div>
            </div>
            @yield('content')
        </main>
    </div>
@endif

    <template id="link-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nuevo enlace</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-2" style="margin-top:12px;">
                <div class="field"><label>Texto</label><input type="text" data-field="label"></div>
                <div class="field"><label>URL</label><input type="text" data-field="url" value="#"></div>
            </div>
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="service-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nuevo servicio</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-2" style="margin-top:12px;">
                <div class="field"><label>Titulo</label><input type="text" data-field="title"></div>
                <div class="field"><label>Icono</label><input type="text" data-field="icon" placeholder="plane, truck, mail"></div>
                <div class="field"><label>Imagen actual</label><input type="text" data-field="iconImage"></div>
                <div class="field"><label>Subir imagen</label><input type="file" data-field="iconImage_file" accept="image/*" data-preview-input></div>
                <div class="field" style="grid-column:1/-1;"><label>Descripcion</label><input type="text" data-field="text"></div>
            </div>
            <img class="thumb" data-preview-image style="display:none; margin-top:14px;" alt="Preview">
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="hero-media-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nuevo slide</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-2" style="margin-top:12px;">
                <div class="field"><label>Nombre interno</label><input type="text" data-field="title"></div>
                <div class="field"><label>Tipo</label><select data-field="media_type" data-media-type><option value="image">Imagen</option><option value="video">Video</option></select></div>
                <div class="field"><label>Archivo actual</label><input type="text" data-field="src"></div>
                <div class="field"><label>Subir archivo</label><input type="file" data-field="media_file" accept="image/*,video/*"></div>
                <div class="field" data-poster-field><label>Poster</label><input type="text" data-field="poster"></div>
                <div class="field" data-poster-field><label>Subir poster</label><input type="file" data-field="poster_file" accept="image/*"></div>
            </div>
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="product-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nuevo producto</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-3" style="margin-top:12px;">
                <div class="field"><label>Titulo</label><input type="text" data-field="title"></div>
                <div class="field"><label>Precio</label><input type="text" data-field="price"></div>
                <div class="field"><label>Anio o etiqueta</label><input type="text" data-field="year"></div>
                <div class="field"><label>Serie</label><input type="text" data-field="series"></div>
                <div class="field"><label>Imagen actual</label><input type="text" data-field="image"></div>
                <div class="field"><label>Subir imagen</label><input type="file" data-field="image_file" accept="image/*" data-preview-input></div>
            </div>
            <div class="field" style="margin-top:12px;"><label>Descripcion</label><textarea class="field-small" data-field="description"></textarea></div>
            <img class="thumb" data-preview-image style="display:none; margin-top:14px;" alt="Preview">
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="social-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nueva red social</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-3" style="margin-top:12px;">
                <div class="field"><label>Texto corto</label><input type="text" data-field="label"></div>
                <div class="field"><label>Nombre accesible</label><input type="text" data-field="aria_label"></div>
                <div class="field"><label>URL</label><input type="text" data-field="url" value="#"></div>
            </div>
            <input type="hidden" data-field="id">
        </div>
    </template>

    <script>
        function reindexCollection(collection) {
            if (!collection) return;
            const baseName = collection.getAttribute('data-base');
            collection.querySelectorAll('[data-row]').forEach(function (row, index) {
                row.querySelectorAll('[data-field]').forEach(function (field) {
                    field.name = `${baseName}[${index}][${field.getAttribute('data-field')}]`;
                });
            });
        }

        function initSortable() {
            document.querySelectorAll('[data-rows]').forEach(function (rows) {
                if (rows.dataset.sortableReady) return;
                rows.dataset.sortableReady = '1';
                Sortable.create(rows, {
                    animation: 180,
                    handle: '[data-drag]',
                    onEnd: function () {
                        reindexCollection(rows.closest('[data-collection]'));
                    }
                });
            });
        }

        function bindPreviewInput(root = document) {
            root.querySelectorAll('[data-preview-input]').forEach(function (input) {
                if (input.dataset.previewBound) return;
                input.dataset.previewBound = '1';
                input.addEventListener('change', function (event) {
                    const file = event.target.files && event.target.files[0];
                    const card = event.target.closest('[data-row]');
                    const preview = card ? card.querySelector('[data-preview-image]') : null;
                    if (!file || !preview) return;
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                });
            });
        }

        function syncHeroMediaFields(root = document) {
            root.querySelectorAll('[data-row]').forEach(function (row) {
                const mediaType = row.querySelector('[data-media-type]');
                if (!mediaType || mediaType.dataset.mediaTypeBound) {
                    if (!mediaType) return;
                }

                const updatePosterVisibility = function () {
                    const isVideo = mediaType.value === 'video';
                    row.querySelectorAll('[data-poster-field]').forEach(function (field) {
                        field.style.display = isVideo ? '' : 'none';
                    });
                };

                if (!mediaType.dataset.mediaTypeBound) {
                    mediaType.dataset.mediaTypeBound = '1';
                    mediaType.addEventListener('change', updatePosterVisibility);
                }

                updatePosterVisibility();
            });
        }

        document.addEventListener('click', function (event) {
            if (event.target.matches('[data-remove-row]')) {
                const collection = event.target.closest('[data-collection]');
                event.target.closest('[data-row]').remove();
                reindexCollection(collection);
            }

            if (event.target.matches('[data-add-row]')) {
                const subpanel = event.target.closest('.subpanel');
                let collection = event.target.closest('[data-collection]');

                if (!collection && subpanel) {
                    collection = subpanel.querySelector('[data-collection]');
                }

                if (!collection) return;
                const templateId = collection.getAttribute('data-template');
                const host = collection.querySelector('[data-rows]');
                const fragment = document.querySelector(`#${templateId}`).content.cloneNode(true);
                host.appendChild(fragment);
                reindexCollection(collection);
                initSortable();
                bindPreviewInput(collection);
                syncHeroMediaFields(collection);
            }
        });

        document.querySelectorAll('[data-collection]').forEach(reindexCollection);
        initSortable();
        bindPreviewInput(document);
        syncHeroMediaFields(document);
        document.querySelectorAll('[data-preview-image][src]').forEach(function (img) {
            if (img.getAttribute('src')) img.style.display = 'block';
        });
    </script>
</body>
</html>

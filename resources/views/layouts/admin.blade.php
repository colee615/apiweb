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
            --bg: #f4f7fb;
            --panel: rgba(255,255,255,.88);
            --panel-solid: #ffffff;
            --line: #dfe6f3;
            --line-strong: #cfd8ea;
            --text: #0f172a;
            --muted: #667085;
            --primary: #1e63c6;
            --primary-dark: #154f9f;
            --accent: #0e2344;
            --accent-soft: #355174;
            --success: #0c7a58;
            --sidebar: #ffffff;
            --sidebar-soft: #eef3fb;
            --shadow: 0 16px 36px rgba(19, 45, 86, 0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Manrope", Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        a { color: inherit; text-decoration: none; }
        .admin-app { min-height: 100vh; display: grid; grid-template-columns: 278px 1fr; transition: grid-template-columns .2s ease; }
        .admin-app.sidebar-collapsed { grid-template-columns: 84px 1fr; }
        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            background: #fff;
            color: var(--accent);
            padding: 24px 18px;
            border-right: 1px solid #e5ebf5;
            overflow: hidden;
        }
        .admin-app.sidebar-collapsed .brand,
        .admin-app.sidebar-collapsed .nav-link span:first-child { display: none; }
        .admin-app.sidebar-collapsed .nav-link { justify-content: center; padding: 12px; }
        .brand {
            padding: 8px 6px 16px;
            border-bottom: 1px solid #e8edf6;
            margin-bottom: 16px;
        }
        .brand-badge { display: none; }
        .brand h1 { margin: 0; font-size: 22px; font-weight: 800; }
        .brand p { margin: 6px 0 0; color: #5f7190; line-height: 1.4; font-size: 13px; }
        .nav-group { display: grid; gap: 12px; }
        .nav-link {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 14px 16px; border-radius: 14px;
            color: #2f4668; border: 1px solid transparent;
            transition: .2s ease;
        }
        .nav-link:hover { background: #f3f7fe; transform: translateX(1px); }
        .nav-link.active {
            background: #eaf1ff;
            border-color: #dbe7ff;
            color: #1b4ea1;
            box-shadow: none;
        }
        .nav-link-meta { display:grid; gap:4px; }
        .nav-link-title { font-weight: 800; }
        .nav-link-copy { font-size: 12px; color: #7590b1; }
        .nav-link.active .nav-link-copy { color: #3f6fb7; }
        .nav-link span:last-child {
            width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 10px; background: #f0f4fb; font-size: 12px; font-weight: 800;
        }
        .role-chip { display: none; }
        .sidebar-footer {
            margin-top: 12px; padding: 12px 0 0; border-radius: 0;
            background: transparent; color: #31507a; font-size: 13px;
            border: 0; border-top: 1px solid #e8edf6;
        }
        .content { padding: 0; }
        .topbar {
            height: 64px;
            background: #ffffff;
            border-bottom: 1px solid #e5ebf5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 30;
        }
        .menu-toggle {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid #d8e3f5;
            background: #f8fbff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #1d4fa3;
        }
        .menu-toggle svg { width: 18px; height: 18px; }
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .topbar-icon {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 1px solid #d8e3f5;
            background: #f8fbff;
            color: #1d4fa3;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            line-height: 1;
        }
        .topbar-logout {
            border: 1px solid #d8e3f5;
            color: #0e2344;
            background: #f8fbff;
            border-radius: 12px;
            padding: 8px 12px;
            font-weight: 800;
            cursor: pointer;
            font-family: inherit;
        }
        .content-inner { padding: 26px 30px; }
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
        [x-cloak] { display:none !important; }
        .admin-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1200;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(15, 23, 42, .56);
            backdrop-filter: blur(10px);
        }
        .admin-modal-card {
            position: relative;
            width: min(680px, 100%);
            padding: 28px;
            border-radius: 30px;
            background:
                radial-gradient(circle at top right, rgba(44, 110, 200, .10), transparent 24%),
                linear-gradient(180deg, #ffffff, #f7faff);
            border: 1px solid rgba(255,255,255,.86);
            box-shadow: 0 28px 70px rgba(15, 23, 42, .24);
        }
        .admin-modal-close {
            position: absolute;
            top: 18px;
            right: 18px;
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 999px;
            cursor: pointer;
            font: inherit;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), #2c6ec8);
            box-shadow: 0 14px 28px rgba(32, 83, 154, .24);
        }
        .admin-modal-head {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 16px;
            align-items: start;
            margin-bottom: 20px;
        }
        .admin-modal-icon {
            width: 60px;
            height: 60px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            font-size: 24px;
            font-weight: 800;
            color: #9a3412;
            background: linear-gradient(180deg, #fff1e7, #ffe2cf);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
        }
        .admin-modal-kicker {
            display: inline-flex;
            margin-bottom: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: #edf3fb;
            color: var(--primary);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .admin-modal-copy h3 {
            margin: 0 0 8px;
            font-size: 28px;
            line-height: 1.05;
        }
        .admin-modal-copy p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }
        .admin-modal-list {
            display: grid;
            gap: 10px;
            margin-top: 22px;
        }
        .admin-modal-item {
            padding: 14px 16px;
            border-radius: 18px;
            background: #fff;
            border: 1px solid #e7edf7;
            color: var(--accent);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.85);
        }
        .admin-modal-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 24px;
        }
        @media (max-width: 640px) {
            .admin-modal-card {
                padding: 22px 18px;
                border-radius: 24px;
            }
            .admin-modal-head {
                grid-template-columns: 1fr;
            }
            .admin-modal-close {
                top: 14px;
                right: 14px;
            }
            .admin-modal-copy h3 {
                font-size: 24px;
            }
        }
        .page-table { width:100%; border-collapse:collapse; }
        .page-table th, .page-table td { padding:18px 12px; border-bottom:1px solid var(--line); text-align:left; vertical-align:top; }
        .page-table th { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .06em; }
        .pill { display:inline-block; padding:7px 11px; border-radius:999px; font-size:12px; font-weight:800; }
        .pill-ok { background:#eaf9f2; color:var(--success); }
        .pill-off { background:#eef2f8; color:var(--muted); }
        .muted { color:var(--muted); }
        .login-wrap {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 14% 10%, rgba(255, 199, 0, .24), transparent 26%),
                radial-gradient(circle at 89% 84%, rgba(0, 85, 180, .18), transparent 28%),
                linear-gradient(160deg, #f8fafc, #eef2f7 55%, #e8edf5);
        }
        .login-card {
            width: min(640px, 100%);
            position: relative;
            z-index: 2;
            text-align: center;
        }
        .login-brand {
            display: flex;
            justify-content: center;
            margin-bottom: 26px;
        }
        .login-brand img {
            width: 180px;
            height: auto;
            object-fit: contain;
            background: transparent;
            padding: 0;
            border-radius: 0;
            box-shadow: none;
            filter: drop-shadow(0 10px 18px rgba(0, 43, 121, .18));
        }
        .login-title {
            margin: 0 0 10px;
            font-size: clamp(34px, 4vw, 54px);
            line-height: 1.05;
            color: #111f3d;
        }
        .login-subtitle {
            margin: 0 auto 22px;
            max-width: 560px;
            color: #4f5f79;
            line-height: 1.65;
        }
        .login-panel {
            background: rgba(255,255,255,.88);
            border: 1px solid rgba(255,255,255,.96);
            box-shadow: 0 20px 56px rgba(11, 35, 75, .15);
            border-radius: 18px;
            text-align: left;
        }
        .login-field label { color: #32445f; font-weight: 800; }
        .login-field input {
            border-radius: 12px;
            border: 1px solid #b9c7de;
            min-height: 54px;
            background: #fbfdff;
        }
        .login-field input:focus {
            border-color: #0055b4;
            box-shadow: 0 0 0 3px rgba(0, 85, 180, .18);
            background: #fff;
        }
        .login-button {
            width: 100%;
            border: 0;
            min-height: 54px;
            border-radius: 999px;
            font-size: 21px;
            letter-spacing: .01em;
            color: #0f2346;
            background: linear-gradient(90deg, #ffd100, #ffc300 55%, #ffb400);
            box-shadow: 0 12px 24px rgba(200, 148, 0, .35);
        }
        .login-button:hover {
            filter: brightness(1.04);
            transform: translateY(-1px);
        }
        .login-links {
            margin-top: 18px;
            display: flex;
            justify-content: center;
            gap: 26px;
            flex-wrap: wrap;
        }
        .login-links a {
            color: #0055b4;
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 3px;
        }
        .login-orb {
            position: absolute;
            z-index: 1;
            border-radius: 999px;
            background: linear-gradient(145deg, #ffd100 0%, #f2c90a 35%, #1f7dc8 100%);
            box-shadow: 0 24px 40px rgba(0, 60, 150, .24);
            opacity: .96;
        }
        .login-orb-lg {
            width: 220px;
            height: 220px;
            right: calc(50% - 345px);
            top: calc(50% - 165px);
        }
        .login-orb-sm {
            width: 92px;
            height: 92px;
            left: calc(50% - 390px);
            bottom: calc(50% - 230px);
        }
        @media (max-width: 768px) {
            .login-brand img { width: 140px; }
            .login-orb-lg {
                width: 168px;
                height: 168px;
                right: -34px;
                top: 210px;
            }
            .login-orb-sm {
                width: 72px;
                height: 72px;
                left: -24px;
                bottom: 94px;
            }
        }
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
            width: 100%;
            max-width: 320px;
            max-height: 220px;
            border-radius: 14px;
            border: 1px solid #dbe5f3;
            background:
                linear-gradient(180deg, #f9fbff, #f1f5fb);
            display: block;
            object-fit: contain;
            aspect-ratio: auto;
            padding: 8px;
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
        .editor-layout { display:grid; grid-template-columns: 230px minmax(0, 1fr); gap: 20px; align-items:start; }
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
            border-radius: 16px;
            background: #ffffff;
            border: 1px solid #dbe5f3;
            box-shadow: var(--shadow);
            height: auto;
            min-height: 0;
            overflow: visible;
        }
        .section-card[hidden] { display: none !important; }
        .section-eyebrow {
            display:inline-flex; padding:8px 12px; border-radius:999px; background:#eef3fb; color:var(--accent);
            font-size:12px; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
        }
        .section-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:18px; }
        .section-metrics { display:flex; gap:10px; flex-wrap:wrap; }
        .announcement-header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            align-items: start;
        }
        .announcement-header .section-copy { max-width: 900px; margin-top: 8px; }
        .announcement-header .section-metrics {
            justify-content: flex-end;
            align-items: center;
        }
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
        .announcement-layout { grid-template-columns: 320px minmax(0, 1fr); }
        .announcement-form-grid { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 14px; }
        .announcement-preview-wrap { margin-top: 10px; }
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
        .image-frame img {
            display: block;
            width: 100%;
            max-width: 100%;
            height: auto;
            max-height: 180px;
            object-fit: contain;
        }
        .save-dock {
            position: static;
            display:flex;
            justify-content:space-between;
            align-items:flex-end;
            gap:14px;
            padding: 14px 16px;
            border-radius: 14px;
            background: #ffffff;
            color: var(--text);
            border: 1px solid #dbe5f3;
            box-shadow: 0 8px 20px rgba(15,23,42,.08);
            margin-top: 20px;
            z-index: 1;
        }
        .save-dock p { margin:0; color: var(--muted); font-size: 14px; }
        .save-dock .field { margin-top: 8px !important; }
        .save-dock .field label { margin-bottom: 6px; }
        .save-dock .button-primary { min-width: 240px; }
        @media (max-width: 1400px) {
            .save-dock { flex-direction: column; align-items: stretch; }
            .save-dock .button-primary { width: 100%; min-width: 0; }
        }
        .mini-service-grid { display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap:12px; }
        .mini-service { padding:12px; border-radius:18px; background:#fff; border:1px solid var(--line); min-height:110px; }
        .empty-note { padding: 18px; border-radius: 18px; border: 1px dashed var(--line); color: var(--muted); background: #fbfcfe; text-align: center; }
        .form-card { padding: 22px; border-radius: 24px; background: #fff; border: 1px solid var(--line); box-shadow: var(--shadow); }
        @media (max-width: 1500px) {
            .design-grid { grid-template-columns: 1fr; }
            .span-3, .span-4, .span-6, .span-8, .span-9, .span-12 { grid-column: auto; }
        }
        @media (max-width: 1100px) {
            .announcement-layout { grid-template-columns: 1fr; }
            .announcement-form-grid { grid-template-columns: 1fr; }
            .announcement-header { grid-template-columns: 1fr; }
            .announcement-header .section-metrics { justify-content: flex-start; }
        }
        @media (max-width: 1320px) { .editor-layout { grid-template-columns: 1fr; } .editor-sidebar, .editor-preview { position: static; } }
        @media (max-width: 1180px) { .preview-grid { grid-template-columns: 1fr; } .stat-grid { grid-template-columns: repeat(2, minmax(0,1fr)); } .page-hero-grid, .card-grid, .spec-grid { grid-template-columns: 1fr; } }
        @media (max-width: 1080px) {
            .admin-app { grid-template-columns:1fr; }
            .sidebar {
                position: fixed;
                top: 64px;
                left: 0;
                bottom: 0;
                width: 278px;
                height: auto;
                z-index: 35;
                transform: translateX(-100%);
                transition: transform .2s ease;
            }
            .admin-app.sidebar-open .sidebar { transform: translateX(0); }
        }
        @media (max-width: 900px) {
            .grid-2, .grid-3, .grid-4, .stat-grid, .mini-service-grid, .design-grid, .palette-grid { grid-template-columns:1fr; }
            .span-3, .span-4, .span-6, .span-8, .span-9, .span-12 { grid-column: auto; }
            .admin-topbar, .toolbar, .actions, .split-header { align-items:flex-start; flex-direction:column; }
            .content-inner { padding: 16px; }
            .save-dock { flex-direction: column; align-items: stretch; }
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
    @php($homePageId = \App\Models\SitePage::query()->where('slug', 'home')->value('id'))
    @php($historyUrl = $isPageEdit
        ? (request()->url() . '?tab=history_overview#history-root')
        : ($homePageId
            ? route('admin.pages.edit', ['page' => $homePageId, 'tab' => 'history_overview']) . '#history-root'
            : route('admin.dashboard')))
    <div class="admin-app" x-data="{ sidebarOpen: false, sidebarCollapsed: false }" :class="{ 'sidebar-open': sidebarOpen, 'sidebar-collapsed': sidebarCollapsed }">
        <aside class="sidebar">
            <div class="brand">
                <h1>Correos de Bolivia</h1>
                <p>Panel administrativo</p>
            </div>

            <nav class="nav-group">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') || (request()->routeIs('admin.pages.*') && !$isHistoryMode) ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <span>Diseno</span>
                    <span aria-hidden="true">🎨</span>
                </a>
                <a class="nav-link {{ $isHistoryMode ? 'active' : '' }}" href="{{ $historyUrl }}">
                    <span>Historial</span>
                    <span aria-hidden="true">🕘</span>
                </a>
                @if (($adminUser->role ?? 'Administrador') === 'Administrador')
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <span>Usuarios</span>
                    <span aria-hidden="true">👤</span>
                </a>
                @endif
            </nav>

        </aside>

        <main class="content">
            <div class="topbar">
                <button type="button" class="menu-toggle" @click="window.innerWidth <= 1080 ? sidebarOpen = !sidebarOpen : sidebarCollapsed = !sidebarCollapsed" aria-label="Menu">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <line x1="4" y1="7" x2="20" y2="7"></line>
                        <line x1="4" y1="12" x2="20" y2="12"></line>
                        <line x1="4" y1="17" x2="20" y2="17"></line>
                    </svg>
                </button>
                <div class="topbar-actions">
                    <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="topbar-logout">Cerrar sesion</button>
                    </form>
                </div>
            </div>
            <div class="content-inner">
                @yield('content')
            </div>
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
                <div class="field"><label>TÃ­tulo</label><input type="text" data-field="title"></div>
                <div class="field"><label>Icono</label><input type="text" data-field="icon" placeholder="plane, truck, mail"></div>
                <div class="field"><label>Imagen actual</label><input type="text" data-field="iconImage"></div>
                <div class="field"><label>Subir imagen</label><input type="file" data-field="iconImage_file" accept="image/*" data-preview-input></div>
                <div class="field" style="grid-column:1/-1;"><label>DescripciÃ³n</label><input type="text" data-field="text"></div>
            </div>
            <div style="margin-top:12px;">
                <button type="button" class="button button-secondary" @click="openPreviewFromCard($event.currentTarget)">Ver imagen</button>
                <img class="thumb" data-preview-image data-no-inline-preview="1" style="display:none;" alt="Preview">
            </div>
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
                <div class="field"><label>DuraciÃ³n (segundos)</label><input type="number" min="1" max="300" step="1" data-field="duration_seconds" value="5"></div>
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
                <div class="field"><label>TÃ­tulo</label><input type="text" data-field="title"></div>
                <div class="field"><label>Precio</label><input type="text" data-field="price"></div>
                <div class="field"><label>AÃ±o o etiqueta</label><input type="text" data-field="year"></div>
                <div class="field"><label>Serie</label><input type="text" data-field="series"></div>
                <div class="field"><label>Imagen actual</label><input type="text" data-field="image"></div>
                <div class="field"><label>Subir imagen</label><input type="file" data-field="image_file" accept="image/*" data-preview-input></div>
            </div>
            <div class="field" style="margin-top:12px;"><label>DescripciÃ³n</label><textarea class="field-small" data-field="description"></textarea></div>
            <img class="thumb" data-preview-image style="display:none; margin-top:14px;" alt="Preview">
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="office-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nueva oficina</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-3" style="margin-top:12px;">
                <div class="field"><label>Nombre oficina</label><input type="text" data-field="title"></div>
                <div class="field"><label>Ciudad o etiqueta</label><input type="text" data-field="name"></div>
                <div class="field"><label>CÃ³digo depto</label><input type="text" data-field="dept" placeholder="BOL, BOC, BOS..."></div>
                <div class="field"><label>DirecciÃ³n</label><input type="text" data-field="address"></div>
                <div class="field"><label>Lun a Vie</label><input type="text" data-field="weekday_hours" placeholder="08:00 a 18:00"></div>
                <div class="field"><label>SÃ¡bado</label><input type="text" data-field="saturday_hours" placeholder="09:00 a 13:00"></div>
                <div class="field"><label>TelÃ©fono</label><input type="text" data-field="phone"></div>
                <div class="field"><label>PosiciÃ³n izquierda</label><input type="text" data-field="left" placeholder="29.6%"></div>
                <div class="field"><label>PosiciÃ³n arriba</label><input type="text" data-field="top" placeholder="46%"></div>
                <div class="field"><label>Google Maps URL</label><input type="text" data-field="maps_url" value="#"></div>
                <div class="field" style="grid-column:1/-1;"><label>Horario general de respaldo</label><input type="text" data-field="hours" placeholder="Opcional para compatibilidad"></div>
            </div>
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="announcement-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nuevo popup</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-2" style="margin-top:12px;">
                <div class="field"><label>Nombre interno</label><input type="text" data-field="title"></div>
                <div class="field"><label>Texto alternativo</label><input type="text" data-field="poster_alt" value="Comunicado institucional"></div>
                <div class="field"><label>Imagen actual</label><input type="text" data-field="poster_image"></div>
                <div class="field"><label>Subir imagen</label><input type="file" data-field="poster_file" accept="image/*" data-preview-input></div>
                <div class="field"><label>TÃ­tulo visible</label><input type="text" data-field="poster_title"></div>
                <div class="field"><label>Pie o detalle</label><input type="text" data-field="poster_caption"></div>
            </div>
            <img class="thumb" data-preview-image data-no-inline-preview="1" style="display:none; margin-top:14px; max-width: 320px; aspect-ratio: auto;" alt="Preview">
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="app-banner-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nuevo banner</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-3" style="margin-top:12px;">
                <div class="field"><label>Nombre interno</label><input type="text" data-field="title"></div>
                <div class="field"><label>DuraciÃ³n (segundos)</label><input type="number" min="1" max="300" step="1" data-field="duration_seconds" value="5"></div>
                <div class="field"><label>Imagen actual</label><input type="text" data-field="image"></div>
                <div class="field"><label>Subir imagen</label><input type="file" data-field="image_file" accept="image/*" data-preview-input></div>
            </div>
            <div style="margin-top:12px;">
                <button type="button" class="button button-secondary" @click="openPreviewFromCard($event.currentTarget)">Ver imagen</button>
                <img class="thumb" data-preview-image data-no-inline-preview="1" style="display:none;" alt="Preview">
            </div>
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
                    if (preview.dataset.noInlinePreview === '1') return;
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
            if (img.dataset.noInlinePreview === '1') return;
            if (img.getAttribute('src')) img.style.display = 'block';
            img.addEventListener('error', function () {
                img.style.display = 'none';
            });
        });
    </script>
</body>
</html>


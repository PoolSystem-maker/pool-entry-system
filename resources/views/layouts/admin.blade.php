<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Panel' }} — Pool Entry</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --navy:      #0f172a;
            --navy-mid:  #1e293b;
            --navy-soft: #334155;
            --accent:    #38bdf8;
            --accent2:   #0ea5e9;
            --success:   #22c55e;
            --danger:    #ef4444;
            --warning:   #f59e0b;
            --text:      #f1f5f9;
            --text-muted:#94a3b8;
            --border:    #334155;
            --card:      #1e293b;
            --radius:    12px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--navy);
            color: var(--text);
            font-family: 'Segoe UI', system-ui, sans-serif;
            font-size: 15px;
            min-height: 100vh;
        }

        /* ── TOP NAV ── */
        .topnav {
            background: var(--navy-mid);
            border-bottom: 1px solid var(--border);
            padding: 0 20px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topnav-brand {
            font-size: 17px;
            font-weight: 700;
            color: var(--accent);
            letter-spacing: -0.3px;
        }

        .topnav-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topnav-user {
            color: var(--text-muted);
            font-size: 13px;
        }

        .btn-logout {
            background: var(--navy-soft);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-logout:hover { background: var(--danger); border-color: var(--danger); }

        .hamburger {
            display: none;
            background: none;
            border: none;
            color: var(--text);
            font-size: 22px;
            cursor: pointer;
            padding: 4px;
        }

        /* ── LAYOUT ── */
        .layout {
            display: flex;
            min-height: calc(100vh - 56px);
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 220px;
            background: var(--navy-mid);
            border-right: 1px solid var(--border);
            padding: 20px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex-shrink: 0;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s;
        }

        .sidebar a:hover {
            background: var(--navy-soft);
            color: var(--text);
        }

        .sidebar a.active {
            background: var(--accent2);
            color: #fff;
        }

        .sidebar-divider {
            height: 1px;
            background: var(--border);
            margin: 8px 0;
        }

        .sidebar-scanner {
            color: var(--accent) !important;
        }

        /* ── MAIN ── */
        .main {
            flex: 1;
            padding: 24px;
            overflow-x: hidden;
            max-width: 100%;
        }

        /* ── ALERTS ── */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(34,197,94,0.15);
            border: 1px solid rgba(34,197,94,0.3);
            color: #86efac;
        }

        .alert-error {
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5;
        }

        /* ── CARDS ── */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
        }

        /* ── TABLES ── */
        .table-wrap {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        thead {
            background: var(--navy-soft);
        }

        thead th {
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        tbody tr {
            border-top: 1px solid var(--border);
            transition: background 0.15s;
        }

        tbody tr:hover { background: rgba(255,255,255,0.03); }

        tbody td {
            padding: 12px 16px;
            color: var(--text);
            vertical-align: middle;
        }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            white-space: nowrap;
        }

        .btn-primary   { background: var(--accent2); color: #fff; }
        .btn-primary:hover { background: #0284c7; }

        .btn-success   { background: var(--success); color: #fff; }
        .btn-success:hover { filter: brightness(1.1); }

        .btn-danger    { background: var(--danger); color: #fff; }
        .btn-danger:hover { filter: brightness(1.1); }

        .btn-warning   { background: var(--warning); color: #fff; }
        .btn-warning:hover { filter: brightness(1.1); }

        .btn-ghost {
            background: var(--navy-soft);
            color: var(--text);
            border: 1px solid var(--border);
        }
        .btn-ghost:hover { background: #475569; }

        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
            border-radius: 6px;
        }

        /* ── BADGES ── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-green  { background: rgba(34,197,94,0.15);  color: #86efac; }
        .badge-red    { background: rgba(239,68,68,0.15);  color: #fca5a5; }
        .badge-yellow { background: rgba(245,158,11,0.15); color: #fcd34d; }
        .badge-blue   { background: rgba(56,189,248,0.15); color: #7dd3fc; }

        /* ── FORM ELEMENTS ── */
        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="date"],
        input[type="password"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            background: var(--navy);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 14px;
            color: var(--text);
            font-size: 14px;
            transition: border-color 0.15s;
            outline: none;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--accent2);
        }

        input::placeholder { color: var(--navy-soft); }

        .form-hint {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 5px;
        }

        .form-error {
            font-size: 12px;
            color: #fca5a5;
            margin-top: 5px;
        }

        /* ── PAGE HEADER ── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
        }

        .page-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ── STAT CARDS ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-icon {
            font-size: 32px;
            flex-shrink: 0;
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
        }

        /* ── PAGINATION ── */
        .pagination-wrap {
            padding: 12px 16px;
            border-top: 1px solid var(--border);
        }

        nav[role="navigation"] span,
        nav[role="navigation"] a {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            margin: 2px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            color: var(--text-muted);
            background: var(--navy-soft);
            border: 1px solid var(--border);
        }

        nav[role="navigation"] a:hover {
            background: var(--accent2);
            color: #fff;
            border-color: var(--accent2);
        }

        nav[role="navigation"] span[aria-current="page"] span {
            background: var(--accent2);
            color: #fff;
            border-color: var(--accent2);
        }

        /* ── PROGRESS BAR ── */
        .progress-track {
            background: var(--navy-soft);
            border-radius: 999px;
            height: 8px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 999px;
            transition: width 0.3s;
        }

        /* ── MOBILE ── */
        @media (max-width: 768px) {
            .hamburger { display: block; }

            .topnav-user { display: none; }

            .sidebar {
                position: fixed;
                top: 56px;
                left: 0;
                bottom: 0;
                z-index: 99;
                width: 240px;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                box-shadow: 4px 0 20px rgba(0,0,0,0.4);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 56px 0 0 0;
                background: rgba(0,0,0,0.5);
                z-index: 98;
            }

            .sidebar-overlay.open { display: block; }

            .main { padding: 16px; }

            .page-header { flex-direction: column; align-items: flex-start; }

            .stat-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }

            .stat-card { padding: 14px; gap: 12px; }

            .stat-value { font-size: 22px; }

            table { font-size: 13px; }

            thead th, tbody td { padding: 10px 12px; }

            .btn { padding: 8px 14px; font-size: 13px; }

            /* Stack action buttons on mobile */
            .action-btns {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
        }
    </style>
</head>
<body>

    {{-- TOP NAV --}}
    <nav class="topnav">
        <div style="display:flex; align-items:center; gap:12px;">
            <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">☰</button>
            <span class="topnav-brand">🏊 Pool Entry</span>
        </div>
        <div class="topnav-right">
            <span class="topnav-user">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        </div>
    </nav>

    {{-- SIDEBAR OVERLAY (mobile) --}}
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="layout">

        {{-- SIDEBAR --}}
        <aside class="sidebar" id="sidebar">
            <a href="{{ route('admin.dashboard') }}"
                class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                📊 Dashboard
            </a>
            <a href="{{ route('admin.members.index') }}"
                class="{{ request()->routeIs('admin.members.*') ? 'active' : '' }}">
                👥 Members
            </a>
            <a href="{{ route('admin.entry-logs.index') }}"
                class="{{ request()->routeIs('admin.entry-logs.*') ? 'active' : '' }}">
                📋 Entry Logs
            </a>
            <a href="{{ route('admin.monthly-logs.index') }}"
                class="{{ request()->routeIs('admin.monthly-logs.*') ? 'active' : '' }}">
                📅 Monthly Logs
            </a>
            <div class="sidebar-divider"></div>
            <a href="{{ route('scanner.index') }}" target="_blank" class="sidebar-scanner">
                📷 Open Scanner
            </a>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="main">

            @if(session('success'))
                <div class="alert alert-success">✓ {{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">✕ {{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        <div>✕ {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('content')

        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebar-overlay').classList.toggle('open');
        }
    </script>

</body>
</html>
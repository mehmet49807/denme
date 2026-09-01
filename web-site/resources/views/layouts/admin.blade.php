<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Paneli — Gönül Köprüsü')</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- App Basic Resets -->
    <link rel="stylesheet" href="{{ asset('css/app.min.css') }}">

    <style>
        :root {
            --sidebar-bg: #1a1a2e;
            --sidebar-hover: #262646;
            --sidebar-text: #e2e8f0;
            --sidebar-text-muted: #94a3b8;
            --sidebar-active: #7C3AED;
            --content-bg: #f5f5f5;
            --primary: #7C3AED;
            --primary-hover: #6D28D9;
            --card-bg: #ffffff;
            --text-heading: #333333;
            --text-body: #666666;
            --border-color: #e5e7eb;
            --badge-success-bg: #D1FAE5;
            --badge-success-text: #065F46;
            --badge-danger-bg: #FEE2E2;
            --badge-danger-text: #991B1B;
            --badge-warning-bg: #FEF3C7;
            --badge-warning-text: #92400E;
            --badge-info-bg: #E0E7FF;
            --badge-info-text: #3730A3;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--content-bg);
            color: var(--text-body);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .admin-sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 20px;
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand span.logo-icon {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .sidebar-nav {
            list-style: none;
            padding: 15px 10px;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .nav-section-title {
            padding: 12px 12px 6px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--sidebar-text-muted);
            font-weight: 600;
        }

        .sidebar-nav li {
            margin-bottom: 4px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.925rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar-nav a:hover {
            background-color: var(--sidebar-hover);
            color: #ffffff;
        }

        .sidebar-nav a.active {
            background-color: var(--primary);
            color: #ffffff;
        }

        .nav-icon {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .nav-group-title {
            font-size: 0.925rem;
            font-weight: 500;
            padding: 10px 14px;
            color: var(--sidebar-text);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-submenu {
            list-style: none;
            padding-left: 36px;
        }

        .nav-submenu a {
            padding: 8px 12px;
            font-size: 0.875rem;
        }

        /* Main Content Area */
        .admin-main {
            flex-grow: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            min-width: 0;
            transition: margin-left 0.3s ease;
        }

        /* Top Bar */
        .admin-topbar {
            height: 64px;
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-heading);
            padding: 4px;
        }

        .page-title-display {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-heading);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .admin-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-heading);
        }

        .btn-logout {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid var(--border-color);
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-logout:hover {
            background-color: #e5e7eb;
            color: #111827;
        }

        /* Content Container */
        .admin-content {
            padding: 24px;
            flex-grow: 1;
        }

        /* Mobile Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        /* Admin Layout Cards & Components */
        .card {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03);
            border: 1px solid var(--border-color);
            padding: 20px;
            margin-bottom: 24px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 16px;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-heading);
        }

        /* Grid utilities */
        .grid-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid var(--border-color);
            border-left-width: 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-card.border-purple { border-left-color: #7C3AED; }
        .stat-card.border-blue { border-left-color: #3B82F6; }
        .stat-card.border-pink { border-left-color: #EC4899; }
        .stat-card.border-amber { border-left-color: #F59E0B; }
        .stat-card.border-red { border-left-color: #EF4444; }
        .stat-card.border-green { border-left-color: #10B981; }
        .stat-card.border-indigo { border-left-color: #6366F1; }
        .stat-card.border-teal { border-left-color: #14B8A6; }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-body);
            font-weight: 500;
            margin-bottom: 6px;
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-heading);
        }

        .stat-icon {
            font-size: 2rem;
            opacity: 0.85;
        }

        .grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 8px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-secondary {
            background-color: #ffffff;
            border-color: var(--border-color);
            color: var(--text-heading);
        }
        .btn-secondary:hover {
            background-color: #f9fafb;
        }

        .btn-danger {
            background-color: #EF4444;
            color: #ffffff;
        }
        .btn-danger:hover {
            background-color: #DC2626;
        }

        .btn-success {
            background-color: #10B981;
            color: #ffffff;
        }
        .btn-success:hover {
            background-color: #059669;
        }

        .btn-warning {
            background-color: #F59E0B;
            color: #ffffff;
        }
        .btn-warning:hover {
            background-color: #D97706;
        }

        .btn-sm {
            padding: 4px 10px;
            font-size: 0.8rem;
            border-radius: 6px;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success {
            background-color: var(--badge-success-bg);
            color: var(--badge-success-text);
        }

        .badge-danger {
            background-color: var(--badge-danger-bg);
            color: var(--badge-danger-text);
        }

        .badge-warning {
            background-color: var(--badge-warning-bg);
            color: var(--badge-warning-text);
        }

        .badge-info {
            background-color: var(--badge-info-bg);
            color: var(--badge-info-text);
        }

        .badge-secondary {
            background-color: #E5E7EB;
            color: #374151;
        }

        /* Tables */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.875rem;
        }

        .admin-table th {
            background-color: #f9fafb;
            padding: 12px 16px;
            font-weight: 600;
            color: var(--text-heading);
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }

        .admin-table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            color: var(--text-body);
        }

        .admin-table tr:hover {
            background-color: #f9fafb;
        }

        /* Forms */
        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-heading);
        }

        .form-control, .form-select {
            width: 100%;
            padding: 9px 12px;
            font-size: 0.875rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: #ffffff;
            color: var(--text-heading);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            align-items: end;
        }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: var(--badge-success-bg);
            color: var(--badge-success-text);
            border: 1px solid #A7F3D0;
        }

        .alert-danger {
            background-color: var(--badge-danger-bg);
            color: var(--badge-danger-text);
            border: 1px solid #FECACA;
        }

        .pagination-wrapper {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Media Queries */
        @media (max-width: 991px) {
            .grid-2col {
                grid-template-columns: 1fr;
            }

            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
            }

            .mobile-toggle {
                display: block;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <span class="logo-icon">🌉</span>
            <span>Gönül Köprüsü</span>
        </div>

        <ul class="sidebar-nav">
            <li class="nav-section-title">Ana Menü</li>
            <li>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <span class="nav-icon">👥</span>
                    <span>Kullanıcılar</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <span class="nav-icon">⚠️</span>
                    <span>Şikayetler</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.premium.index') }}" class="{{ request()->routeIs('admin.premium.*') ? 'active' : '' }}">
                    <span class="nav-icon">💎</span>
                    <span>Premium</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.support.index') }}" class="{{ request()->routeIs('admin.support.*') ? 'active' : '' }}">
                    <span class="nav-icon">🎧</span>
                    <span>Destek</span>
                </a>
            </li>

            <li class="nav-section-title">Yönetim & İçerik</li>
            <li>
                <div class="nav-group-title">
                    <span class="nav-icon">📸</span>
                    <span>İçerik</span>
                </div>
                <ul class="nav-submenu">
                    <li>
                        <a href="{{ route('admin.content.posts') }}" class="{{ request()->routeIs('admin.content.posts') ? 'active' : '' }}">
                            Gönderiler
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.content.stories') }}" class="{{ request()->routeIs('admin.content.stories') ? 'active' : '' }}">
                            Hikayeler
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="{{ route('admin.messages.index') }}" class="{{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                    <span class="nav-icon">💬</span>
                    <span>Mesajlar</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.broadcasts.index') }}" class="{{ request()->routeIs('admin.broadcasts.*') ? 'active' : '' }}">
                    <span class="nav-icon">📢</span>
                    <span>Duyurular</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <span class="nav-icon">⚙️</span>
                    <span>Ayarlar</span>
                </a>
            </li>
        </ul>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button type="button" class="mobile-toggle" id="mobileToggle" aria-label="Menüyü Aç">
                    ☰
                </button>
                <h1 class="page-title-display">@yield('title', 'Admin Paneli')</h1>
            </div>

            <div class="topbar-right">
                <div class="admin-user-info">
                    <div class="admin-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <span class="admin-name">{{ auth()->user()->name ?? 'Yönetici' }}</span>
                </div>

                <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout">
                        🚪 Çıkış
                    </button>
                </form>
            </div>
        </header>

        <div class="admin-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <span>❌</span> {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileToggle = document.getElementById('mobileToggle');
            const adminSidebar = document.getElementById('adminSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            function toggleSidebar() {
                adminSidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            }

            if (mobileToggle) {
                mobileToggle.addEventListener('click', toggleSidebar);
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', toggleSidebar);
            }
        });
    </script>
</body>
</html>

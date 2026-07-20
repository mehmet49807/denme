<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Yönetim Paneli') — Gönül Köprüsü</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}?v=brand-v17" sizes="32x32" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v=admin-nav-group-icons-1">
    <link rel="stylesheet" href="{{ asset('css/admin-lumiere.css') }}?v=admin-nav-group-icons-1">
</head>
@php
    $adminPageThemes = [
        'admin.dashboard' => 'dashboard',
        'admin.moderation' => 'reports',
        'admin.users' => 'users',
        'admin.profile-approvals' => 'users',
        'admin.messages' => 'messages',
        'admin.gallery' => 'content',
        'admin.content' => 'content',
        'admin.ai' => 'ai',
        'admin.auto-rules' => 'ai',
        'admin.github' => 'seo',
        'admin.reports' => 'reports',
        'admin.premium' => 'premium',
        'admin.broadcasts' => 'broadcasts',
        'admin.referrals' => 'referrals',
        'admin.support' => 'support',
        'admin.emails' => 'emails',
        'admin.seo' => 'seo',
        'admin.audit' => 'seo',
        'admin.system-health' => 'seo',
        'admin.updates' => 'seo',
        'admin.staff' => 'users',
        'admin.ops' => 'ai',
        'admin.profile' => 'profile',
    ];
    $adminPageTheme = 'dashboard';
    foreach ($adminPageThemes as $route => $theme) {
        if (request()->routeIs($route) || request()->routeIs($route.'.*')) {
            $adminPageTheme = $theme;
            break;
        }
    }
@endphp
<body class="admin-body admin-theme-lumiere admin-page--{{ $adminPageTheme }}">
    <div class="admin-menu-overlay" id="adminMenuOverlay" onclick="toggleAdminMenu()"></div>

    <div class="admin-shell">
        <aside class="admin-sidebar" id="adminSidebar" aria-label="Yönetim navigasyonu">
            <div class="admin-sidebar-glow" aria-hidden="true"></div>
            <button class="admin-sidebar-close" type="button" onclick="toggleAdminMenu()" aria-label="Menüyü kapat">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>

            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand">
                <img src="{{ asset('images/logo-admin-ink.png') }}?v=brand-v18-ink" alt="Gönül Köprüsü" width="220" height="76" class="admin-sidebar-logo admin-sidebar-logo--ink">
                <span class="admin-sidebar-brand-text">
                    <strong class="admin-sidebar-brand-text__title">Yönetim</strong>
                    <small>Premium panel</small>
                </span>
            </a>

            @include('partials.admin-nav')

            @auth
            
            <div class="admin-sidebar-footer">
                <div class="admin-sidebar-user">
                    <span class="admin-sidebar-avatar">
                        @if(auth()->user()->profile_photo_url)
                            <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->username }}" width="36" height="36" loading="lazy" decoding="async">
                        @else
                            {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                        @endif
                    </span>
                    <div>
                        <strong>{{ auth()->user()->username }}</strong>
                        <small>Yönetici</small>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="admin-sidebar-logout">
                        @include('partials.admin-icon', ['icon' => 'logout'])
                        <span>Çıkış</span>
                    </button>
                </form>
            </div>
            @endauth
        </aside>

        <div class="admin-main-wrap">
            <header class="admin-topbar">
                <button class="admin-menu-toggle" type="button" onclick="toggleAdminMenu()" aria-label="Menüyü aç/kapat">
                    <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="16" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <div class="admin-page-header">
                    @hasSection('eyebrow')
                        <p class="admin-page-eyebrow">@yield('eyebrow')</p>
                    @else
                        <p class="admin-page-eyebrow">Premium Yönetim</p>
                    @endif
                    <h1>@yield('title', 'Yönetim Paneli')</h1>
                    @hasSection('lead')
                        <p class="admin-page-lead">@yield('lead')</p>
                    @endif
                </div>
                <div class="admin-topbar-actions">
                    @yield('header-actions')
                </div>
            </header>

            <main class="admin-main">
                @if(session('success'))
                    <div class="admin-flash admin-flash--success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="admin-flash admin-flash--error">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="admin-flash admin-flash--error">
                        {{ $errors->first() }}
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleAdminMenu() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('adminMenuOverlay');
            const isOpen = sidebar.classList.contains('is-open');

            if (isOpen) {
                sidebar.classList.remove('is-open');
                overlay.classList.remove('is-visible');
                document.body.style.overflow = '';
            } else {
                sidebar.classList.add('is-open');
                overlay.classList.add('is-visible');
                document.body.style.overflow = 'hidden';
            }
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const sidebar = document.getElementById('adminSidebar');
                if (sidebar.classList.contains('is-open')) {
                    toggleAdminMenu();
                }
            }
        });
    </script>
</body>
</html>

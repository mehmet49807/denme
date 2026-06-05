<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Yönetim') · Gönül Köprüsü</title>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <style>
        /* ----- Admin shell: navigation strictly on the RIGHT side ----- */
        .admin-shell { display: flex; min-height: 100vh; }
        .admin-content { flex: 1; padding: 28px 32px; order: 1; }
        .admin-aside {
            order: 2;                /* RIGHT side */
            width: 260px;
            background: var(--gk-cream-2);
            border-left: 1px solid var(--gk-beige);
            padding: 24px 18px;
            position: sticky; top: 0; height: 100vh;
            transition: transform .25s ease;
        }
        .admin-aside .gk-logo { margin-bottom: 26px; }
        .admin-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 14px; margin-bottom: 6px;
            border-radius: 12px;
            color: var(--gk-text); font-weight: 500;
        }
        .admin-nav a:hover { background: var(--gk-card); }
        .admin-nav a.active {
            background: linear-gradient(135deg, var(--gk-rose), var(--gk-terracotta));
            color: #fff;
        }
        .admin-nav .dot { width:8px; height:8px; border-radius:50%; background: var(--gk-lavender); }
        .admin-topbar {
            display:flex; justify-content: space-between; align-items:center;
            margin-bottom: 24px;
        }
        .admin-burger { display:none; }
        .gk-status {
            background: var(--gk-sage); color:#fff; padding:10px 16px;
            border-radius: 12px; margin-bottom: 18px;
        }
        @media (max-width: 860px) {
            .admin-aside {
                position: fixed; right: 0; top: 0; transform: translateX(100%); z-index: 50;
            }
            .admin-aside.open { transform: translateX(0); }
            .admin-burger { display:inline-block; }
        }
    </style>
</head>
<body>
<div class="admin-shell">
    <main class="admin-content">
        <div class="admin-topbar">
            <h1 style="margin:0;">@yield('title', 'Yönetim Paneli')</h1>
            <button class="gk-btn admin-burger" onclick="document.querySelector('.admin-aside').classList.toggle('open')">☰ Menü</button>
        </div>

        @if (session('status'))
            <div class="gk-status">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>

    {{-- Navigation menu positioned strictly on the RIGHT side --}}
    <aside class="admin-aside">
        <div class="gk-logo"><span class="gk-mark"></span> Gönül Köprüsü</div>
        <nav class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span class="dot"></span> Genel Bakış</a>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><span class="dot"></span> Kullanıcı Yönetimi</a>
            <a href="{{ route('admin.messages.index') }}" class="{{ request()->routeIs('admin.messages.*') ? 'active' : '' }}"><span class="dot"></span> Mesaj Denetimi</a>
            <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"><span class="dot"></span> Şikayet Paneli</a>
            <a href="{{ route('admin.premium.index') }}" class="{{ request()->routeIs('admin.premium.*') ? 'active' : '' }}"><span class="dot"></span> Premium Takibi</a>
            <a href="{{ route('admin.broadcast.index') }}" class="{{ request()->routeIs('admin.broadcast.*') ? 'active' : '' }}"><span class="dot"></span> Duyuru Sistemi</a>
        </nav>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:24px;">
            @csrf
            <button class="gk-btn gk-btn--ghost" style="width:100%;">Çıkış Yap</button>
        </form>
    </aside>
</div>
</body>
</html>

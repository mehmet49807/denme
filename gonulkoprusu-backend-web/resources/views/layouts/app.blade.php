<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gönül Köprüsü')</title>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <style>
        .gk-nav {
            display:flex; justify-content:space-between; align-items:center;
            padding:16px 32px; background:var(--gk-card); border-bottom:1px solid var(--gk-beige);
            position:sticky; top:0; z-index:20;
        }
        .gk-nav a { margin-left:18px; font-weight:600; }
        .gk-container { max-width:1080px; margin:0 auto; padding:32px; }
    </style>
</head>
<body>
<header class="gk-nav">
    <a href="{{ route('home') }}" class="gk-logo"><span class="gk-mark"></span> Gönül Köprüsü</a>
    <nav>
        @auth
            <a href="{{ route('feed') }}">Akış</a>
            <a href="{{ route('profile') }}">Profilim</a>
            @if(auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}">Yönetim</a>@endif
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">@csrf
                <button class="gk-btn gk-btn--ghost" style="margin-left:18px;">Çıkış</button>
            </form>
        @else
            <a href="{{ route('login') }}">Giriş</a>
            <a href="{{ route('register') }}" class="gk-btn" style="color:#fff;">Kayıt Ol</a>
        @endauth
    </nav>
</header>

<main>@yield('content')</main>
</body>
</html>

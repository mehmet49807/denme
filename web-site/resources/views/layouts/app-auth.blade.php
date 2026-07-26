<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#0B1F2A">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <title>@yield('title', 'Gönül Köprüsü')</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}?v={{ config('brand.logo_version') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app-login.css') }}?v=app-login-1">
    @stack('head')
</head>
<body class="app-auth app-auth--@yield('auth-mode', 'login')">
    <div class="app-auth__atmosphere" aria-hidden="true">
        <span class="app-auth__orb app-auth__orb--a"></span>
        <span class="app-auth__orb app-auth__orb--b"></span>
        <span class="app-auth__grain"></span>
    </div>

    <main class="app-auth__shell">
        <header class="app-auth__brand">
            <img
                class="app-auth__logo"
                src="{{ asset('images/logo-180.png') }}?v={{ config('brand.logo_version') }}"
                alt="Gönül Köprüsü"
                width="88"
                height="88"
            >
            <p class="app-auth__brand-name">Gönül Köprüsü</p>
            <p class="app-auth__brand-tag">@yield('app-auth-tag', 'Kalpten kalbe köprü')</p>
        </header>

        <section class="app-auth__sheet">
            <header class="app-auth__sheet-head">
                @yield('auth-form-header')
            </header>
            <div class="app-auth__sheet-body">
                @yield('auth-form')
            </div>
            <footer class="app-auth__sheet-foot">
                @yield('auth-form-footer')
            </footer>
        </section>

        <p class="app-auth__legal">
            Devam ederek
            <a href="{{ route('privacy') }}">Gizlilik</a>
            ve
            <a href="{{ route('terms') }}">Kullanım</a>
            koşullarını kabul etmiş olursun.
        </p>
    </main>
    @stack('auth-scripts')
</body>
</html>

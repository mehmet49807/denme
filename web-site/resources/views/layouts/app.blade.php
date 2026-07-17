<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@php
    $isLanding = request()->routeIs('home');
    $appShell = trim($__env->yieldContent('body-class')) === 'app-shell';
    $isContentPage = str_contains(trim($__env->yieldContent('body-class')), 'page-content');
    $isAuthPage = str_contains(trim($__env->yieldContent('body-class')), 'page-auth');
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('app.brand'))</title>
    @include('partials.seo-head')
    @include('partials.logo-brand-css')
    @stack('head')
    <link rel="icon" href="{{ asset('images/favicon.png') }}?v={{ config('brand.logo_version') }}" sizes="32x32" type="image/png">
    <link rel="icon" href="{{ asset('images/favicon.svg') }}?v={{ config('brand.logo_version') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}?v={{ config('brand.logo_version') }}">
    @include('partials.async-fonts')
    @if($isLanding)
    @php
        $hero640 = is_file(base_path('images/landing-hero-couple-640.webp'));
        $hero960 = is_file(base_path('images/landing-hero-couple-960.webp'));
    @endphp
    @if($hero640)
    <link rel="preload" as="image" href="{{ asset('images/landing-hero-couple-640.webp?v=opt-v6') }}" type="image/webp" fetchpriority="high" media="(max-width: 768px)">
    @endif
    @if($hero960)
    <link rel="preload" as="image" href="{{ asset('images/landing-hero-couple-960.webp?v=opt-v6') }}" type="image/webp" fetchpriority="high" media="(min-width: 769px)">
    @elseif($hero640)
    <link rel="preload" as="image" href="{{ asset('images/landing-hero-couple-640.webp?v=opt-v6') }}" type="image/webp" fetchpriority="high" media="(min-width: 769px)">
    @else
    <link rel="preload" as="image" href="{{ asset('images/landing-hero-couple.webp?v=opt-v6') }}" type="image/webp" fetchpriority="high">
    @endif
    @include('partials.landing-inline-css')
    @include('partials.asset', ['path' => 'css/growth.min.css'])
    @else
    @include('partials.asset', ['path' => 'css/app.min.css'])
    @include('partials.asset', ['path' => 'css/growth.min.css'])
    @endif
    @auth
    @if($appShell)
    @include('partials.asset', ['path' => 'css/app-shell.min.css'])
    @endif
    @php $realtimeEnabled = app(\App\Services\RealtimeBroadcastService::class)->isEnabled(); @endphp
    <meta name="badges-url" content="{{ route('notifications.badge-counts') }}">
    <meta name="live-sync-url" content="{{ route('live.sync') }}">
    @if($realtimeEnabled)
    <meta name="auth-user-id" content="{{ auth()->id() }}">
    <meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
    <meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster', 'eu') }}">
    <meta name="pusher-auth-url" content="{{ url('/broadcasting/auth') }}">
    @endif
    @include('partials.live-sync-meta')
    @stack('head-meta')
    @endauth
</head>
<body class="{{ trim(($appShell ? 'app-shell-body' : '') . ' ' . ($isLanding ? 'page-landing' : '') . ' ' . ($isContentPage ? 'page-content' : '') . ' ' . ($isAuthPage ? 'page-auth' : '')) }}">
@include('partials.google-tag-manager-body')
    <header class="site-header {{ $isLanding || $isAuthPage ? 'site-header--landing' : '' }}">
        <div class="site-header-inner">
            @include('partials.logo', ['showTagline' => true])

            @unless($appShell)
            <nav class="site-nav" aria-label="{{ __('app.nav.main') }}">
                <a href="{{ route('home') }}">{{ __('app.nav.home') }}</a>
                <a href="{{ route('about') }}">{{ __('app.nav.about') }}</a>
                <a href="{{ route('safe-meeting') }}">{{ __('app.nav.security') }}</a>
                <a href="{{ url('/blog') }}">{{ __('app.nav.blog') }}</a>
                <a href="{{ url('/sss') }}">{{ __('app.nav.sss') }}</a>
                @auth
                    @php
                        $unreadNotifications = $unreadNotifications ?? 0;
                        $unreadMessages = $unreadMessages ?? 0;
                    @endphp
                    <a href="{{ route('feed') }}">{{ __('app.nav.feed') }}</a>
                    <a href="{{ route('notifications.index') }}" data-nav-badge="notifications">
                        {{ __('app.nav.notifications') }}
                        @if($unreadNotifications > 0)
                            <span class="site-nav-badge">{{ $unreadNotifications }}</span>
                        @endif
                    </a>
                    <a href="{{ route('messages.index') }}" data-nav-badge="messages">
                        {{ __('app.nav.messages') }}
                        @if($unreadMessages > 0)
                            <span class="site-nav-badge">{{ $unreadMessages }}</span>
                        @endif
                    </a>
                    <a href="{{ route('profile') }}">{{ __('app.nav.profile') }}</a>
                    @if(auth()->user()->gender === 'male')
                        <a href="{{ route('premium') }}">{{ __('app.nav.premium') }}</a>
                    @endif
                    @if(auth()->user()->isAdmin() && \Illuminate\Support\Facades\Route::has('admin.dashboard'))
                        <a href="{{ route('admin.dashboard') }}">{{ __('app.nav.admin') }}</a>
                    @endif
                    <span class="site-nav-logout">
                        <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit">{{ __('app.nav.logout') }}</button></form>
                    </span>
                @endauth
                @guest
                    <a href="{{ route('login') }}" class="site-nav-login">{{ __('app.nav.login') }}</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">{{ __('app.nav.register') }}</a>
                @endguest
            </nav>
            @else
            <div class="site-header-toolbar">
                @auth
                    @unless(request()->routeIs('premium'))
                        @include('partials.header-premium-btn')
                    @endunless
                    @include('partials.profile-settings-open-btn')
                @endauth
                @stack('header-actions')
            </div>
            @endunless
        </div>
    </header>

    <main class="site-main @yield('main-class') {{ $isLanding ? 'site-main--landing' : '' }}">
        @if(session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>

    @unless($appShell || $isAuthPage)
        @include('partials.footer')
    @endunless
    @auth
    @php $realtimeEnabled = app(\App\Services\RealtimeBroadcastService::class)->isEnabled(); @endphp
    @include('partials.asset', ['path' => 'js/core.min.js'])
    @if($realtimeEnabled)
    <script src="https://js.pusher.com/8.4.0/pusher.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js" crossorigin="anonymous"></script>
    @include('partials.asset', ['path' => 'js/rt-client.min.js'])
    @endif
    @if($appShell)
        @include('partials.profile-settings-sheet', ['user' => auth()->user()])
        @include('partials.asset', ['path' => 'js/app-shell.min.js'])
    @endif
    @stack('page-scripts')
    @endauth
    @stack('ld-json')
    @include('partials.deferred-analytics')
</body>
</html>

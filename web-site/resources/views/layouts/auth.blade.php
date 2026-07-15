@extends('layouts.app')

@section('body-class', 'page-auth')

@section('main-class', 'site-main--auth')

@section('content')
<section class="auth-screen auth-screen--@yield('auth-mode', 'login')">
    <aside class="auth-visual">
        <div class="auth-visual-bg" aria-hidden="true">
            <x-optimized-image name="landing-hero-couple" alt="" width="720" height="480" />
        </div>
        <div class="auth-visual-overlay" aria-hidden="true"></div>
        <div class="auth-visual-inner">
            <div class="auth-visual-logo-wrap">
                @include('partials.logo', ['showTagline' => true])
            </div>
            <h2 class="auth-visual-title">@yield('auth-visual-title')</h2>
            <p class="auth-visual-lead">@yield('auth-visual-lead')</p>
            <ul class="auth-visual-features">
                <li>
                    <span class="auth-visual-feature-icon">@include('partials.theme-icon', ['icon' => 'shield'])</span>
                    <span>Moderasyon ve güvenli tanışma</span>
                </li>
                <li>
                    <span class="auth-visual-feature-icon">@include('partials.theme-icon', ['icon' => 'heart'])</span>
                    <span>Ciddi ilişki odaklı topluluk</span>
                </li>
                <li>
                    <span class="auth-visual-feature-icon">@include('partials.theme-icon', ['icon' => 'messages'])</span>
                    <span>Özel ve güvenli mesajlaşma</span>
                </li>
            </ul>
        </div>
    </aside>

    <div class="auth-form-wrap">
        <div class="auth-form-card">
            <header class="auth-form-header">
                @yield('auth-form-header')
            </header>
            @yield('auth-form')
            <footer class="auth-form-footer">
                @yield('auth-form-footer')
            </footer>
        </div>
    </div>
</section>
@stack('auth-scripts')
@endsection

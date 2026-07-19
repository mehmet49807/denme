@extends('layouts.app')

@section('title', 'Gönül Köprüsü — Ücretsiz Tanışma, Evlilik ve Ciddi İlişki Sitesi')

@section('content')
<section class="landing-hero">
    <div class="landing-hero-glow landing-hero-glow--a" aria-hidden="true"></div>
    <div class="landing-hero-glow landing-hero-glow--b" aria-hidden="true"></div>
    <div class="landing-hero-glow landing-hero-glow--c" aria-hidden="true"></div>
    <div class="landing-hero-bg" aria-hidden="true">
        @php
            $heroVersion = 'opt-v7';
            $heroWidths = [640, 960, 1280];
            $heroWebp = [];
            $heroJpg = [];
            foreach ($heroWidths as $w) {
                $suffix = $w === 1280 ? '' : "-{$w}";
                $base = 'landing-hero-couple'.$suffix;
                if (is_file(base_path("images/{$base}.webp"))) {
                    $heroWebp[] = asset("images/{$base}.webp?v={$heroVersion}")." {$w}w";
                }
                if (is_file(base_path("images/{$base}.jpg"))) {
                    $heroJpg[] = asset("images/{$base}.jpg?v={$heroVersion}")." {$w}w";
                }
            }
            $heroFallbackWebp = is_file(base_path('images/landing-hero-couple-640.webp'))
                ? asset("images/landing-hero-couple-640.webp?v={$heroVersion}")
                : asset("images/landing-hero-couple.webp?v={$heroVersion}");
            $heroFallbackJpg = is_file(base_path('images/landing-hero-couple-640.jpg'))
                ? asset("images/landing-hero-couple-640.jpg?v={$heroVersion}")
                : asset("images/landing-hero-couple.jpg?v={$heroVersion}");
        @endphp
        <picture>
            @if($heroWebp !== [])
                <source srcset="{{ implode(', ', $heroWebp) }}" sizes="(max-width: 768px) 640px, 100vw" type="image/webp">
            @else
                <source srcset="{{ $heroFallbackWebp }}" type="image/webp">
            @endif
            <img
                src="{{ $heroFallbackWebp }}"
                @if($heroJpg !== []) srcset="{{ implode(', ', $heroJpg) }}" sizes="(max-width: 768px) 640px, 100vw" @endif
                alt="Gönül Köprüsü — güvenli tanışma ve ciddi ilişki"
                width="1280"
                height="853"
                fetchpriority="high"
                decoding="sync"
            >
        </picture>
    </div>
    <div class="landing-hero-overlay"></div>
    <div class="landing-hero-grid landing-hero-grid--solo">
        <div class="landing-hero-copy">
            <p class="landing-hero-eyebrow">Gönül Köprüsü — tanışma ve sohbet sitesi</p>
            <h1>Gönülleri<br><span class="landing-hero-accent">Birleştiren Köprü</span></h1>
            <p class="landing-hero-lead">
                Ücretsiz üye ol, şehrine göre keşfet, güvenli sohbet et.
                Kadınlarda mesajlaşma ücretsiz — ciddi ilişki ve evlilik için Gönül Köprüsü.
            </p>
            @guest
            <div class="landing-hero-actions landing-hero-actions--inline">
                <a href="{{ route('register', ['utm_source' => 'home', 'utm_medium' => 'hero', 'utm_campaign' => 'organic']) }}" class="btn btn-primary" data-gk-event="sign_up_click" data-gk-event-label="home_hero">Ücretsiz Üye Ol</a>
                <a href="{{ route('login') }}" class="btn btn-ghost">Giriş Yap</a>
            </div>
            <div class="landing-hero-fast">
                <p class="landing-hero-fast-divider" aria-hidden="true"><span>veya</span></p>
                <a href="{{ url('auth/google') }}" class="landing-hero-google" data-gk-event="sign_up_click" data-gk-event-label="home_hero_google">
                    <span class="landing-hero-google__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="18" height="18">
                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.36-8.16 2.36-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                            <path fill="none" d="M0 0h48v48H0z"/>
                        </svg>
                    </span>
                    <span class="landing-hero-google__text">
                        <strong>Google ile devam et</strong>
                        <small>Hesabınla saniyeler içinde üye ol</small>
                    </span>
                </a>
                <ul class="landing-hero-trust" aria-label="Hızlı üyelik avantajları">
                    <li>30 sn</li>
                    <li>Ücretsiz</li>
                    <li>Kadınlarda mesaj ücretsiz</li>
                    <li>Kart yok</li>
                </ul>
            </div>
            @endguest
            @auth
            <div class="landing-hero-actions">
                <a href="{{ route('feed') }}" class="btn btn-primary">Akışa Git</a>
                <a href="{{ route('messages.index') }}" class="btn btn-ghost">Mesajlarım</a>
            </div>
            @endauth
        </div>
    </div>
</section>

@include('partials.homepage-body')
@endsection

@isset($jsonLd)
@push('ld-json')
    @include('partials.json-ld', ['schema' => $jsonLd])
@endpush
@endisset

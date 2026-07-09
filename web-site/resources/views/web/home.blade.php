@extends('layouts.app')

@section('title', 'Gönül Köprüsü — Evlilik ve Tanışma Platformu')

@section('content')
<section class="landing-hero">
    <div class="landing-hero-glow landing-hero-glow--a" aria-hidden="true"></div>
    <div class="landing-hero-glow landing-hero-glow--b" aria-hidden="true"></div>
    <div class="landing-hero-glow landing-hero-glow--c" aria-hidden="true"></div>
    <div class="landing-hero-bg" aria-hidden="true">
        @php
            $heroVersion = 'opt-v6';
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
                alt=""
                width="1280"
                height="853"
                fetchpriority="high"
                decoding="async"
            >
        </picture>
    </div>
    <div class="landing-hero-overlay"></div>
    <div class="landing-hero-grid{{ auth()->check() ? ' landing-hero-grid--solo' : '' }}">
        <div class="landing-hero-copy">
            <p class="landing-hero-eyebrow">Evlilik ve tanışma platformu</p>
            <h1>Gönülleri<br><span class="landing-hero-accent">Birleştiren Köprü</span></h1>
            <p class="landing-hero-lead">
                Ciddi ilişki arayan yetişkinler için güvenli, saygılı ve modern bir tanışma ortamı.
                Gönül Köprüsü ile anlamlı bağlar kur.
            </p>
            <ul class="landing-hero-pills">
                <li>
                    <span class="landing-hero-pill-icon">@include('partials.theme-icon', ['icon' => 'heart'])</span>
                    Ciddi Üyelik
                </li>
                <li>
                    <span class="landing-hero-pill-icon">@include('partials.theme-icon', ['icon' => 'shield'])</span>
                    %100 Güvenli
                </li>
                <li>
                    <span class="landing-hero-pill-icon">@include('partials.theme-icon', ['icon' => 'sparkles'])</span>
                    Eşleşme Odaklı
                </li>
            </ul>
            @guest
            <div class="landing-hero-actions landing-hero-actions--inline">
                <a href="{{ route('register') }}" class="btn btn-primary">Ücretsiz Üye Ol</a>
                <a href="{{ route('login') }}" class="btn btn-ghost">Giriş Yap</a>
            </div>
            <div class="landing-trust-wrap">
                @include('partials.trust-badges')
            </div>
            @endguest
            @auth
            <div class="landing-hero-actions">
                <a href="{{ route('feed') }}" class="btn btn-primary">Akışa Git</a>
                <a href="{{ route('messages.index') }}" class="btn btn-ghost">Mesajlarım</a>
            </div>
            @endauth
        </div>

        @guest
        <div class="landing-hero-visual">
            <aside class="landing-signup glass-card">
                <span class="landing-signup-badge">Tamamen ücretsiz</span>
                <h2>Hemen Ücretsiz Üye Ol</h2>
                <p class="landing-signup-sub">Profilini oluştur, tanışmaya başla.</p>
                <ul class="landing-signup-benefits">
                    <li>Ücretsiz kayıt</li>
                    <li>Güvenli mesajlaşma</li>
                    <li>Profil ve keşif</li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn-primary btn-full">Ücretsiz Üye Ol</a>
                <p class="landing-signup-login">Zaten hesabın var mı? <a href="{{ route('login') }}">Giriş Yap</a></p>
            </aside>
        </div>
        @endguest
    </div>
</section>

@include('partials.homepage-body')
@endsection

@isset($jsonLd)
@push('ld-json')
    @include('partials.json-ld', ['schema' => $jsonLd])
@endpush
@endisset

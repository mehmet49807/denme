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
                Şehrinde güvenli tanış — kadınlarda mesajlaşma ücretsiz.
            </p>
            @guest
            @if(!empty($heroCities))
            <ul class="landing-hero-city-chips" aria-label="Şehrine göre kayıt ol">
                @foreach($heroCities as $cityLink)
                    <li>
                        <a
                            href="{{ route('register', ['city' => $cityLink['name'], 'utm_source' => 'home', 'utm_medium' => 'hero_city', 'utm_campaign' => $cityLink['slug']]) }}"
                            data-gk-event="sign_up_click"
                            data-gk-event-label="home_hero_city_{{ $cityLink['slug'] }}"
                        >{{ $cityLink['name'] }}</a>
                    </li>
                @endforeach
            </ul>
            @endif
            <div class="landing-hero-signup">
                <div class="landing-hero-actions landing-hero-actions--inline">
                    <a href="{{ route('register', ['utm_source' => 'home', 'utm_medium' => 'hero', 'utm_campaign' => 'organic']) }}" class="btn btn-primary" data-gk-event="sign_up_click" data-gk-event-label="home_hero">Ücretsiz Üye Ol</a>
                    <a href="{{ route('login') }}" class="btn btn-ghost">Giriş Yap</a>
                </div>

                <p class="landing-hero-fast-divider" aria-hidden="true"><span>veya</span></p>

                <div class="landing-hero-google-wrap">
                    @include('partials.google-auth-button', [
                        'label' => 'oogle ile devam et',
                        'class' => 'btn btn-primary landing-hero-google',
                        'event' => 'sign_up_click',
                        'eventLabel' => 'home_hero_google',
                        'showArrow' => false,
                        'iconSize' => 18,
                        'gate' => true,
                    ])
                    <p class="landing-hero-google-note">
                        <span class="landing-hero-google-note__icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'heart'])</span>
                        Ücretsiz kayıt ol ve hesabınla saniyeler içinde mesajlaşmaya başla
                    </p>
                </div>
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

@guest
<nav class="home-sticky-cta" aria-label="Hızlı üyelik">
    <a href="{{ route('register', ['utm_source' => 'home', 'utm_medium' => 'sticky', 'utm_campaign' => 'organic']) }}" class="home-sticky-cta__primary" data-gk-event="sign_up_click" data-gk-event-label="home_sticky">Üye Ol</a>
    @include('partials.google-auth-button', [
        'label' => 'oogle',
        'ariaLabel' => 'Google ile üye ol',
        'class' => 'home-sticky-cta__google',
        'event' => 'sign_up_click',
        'eventLabel' => 'home_sticky_google',
        'showArrow' => false,
        'iconSize' => 16,
        'gate' => true,
    ])
</nav>
@include('partials.google-signup-gate')
@endguest
@endsection

@isset($jsonLd)
@push('ld-json')
    @include('partials.json-ld', ['schema' => $jsonLd])
@endpush
@endisset

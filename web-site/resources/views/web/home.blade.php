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

@extends('layouts.content-page')

@section('title', $city . ' Tanışma — Gönül Köprüsü')
@section('page-eyebrow', 'Şehir rehberi')
@section('page-title', $city . ' tanışma ve evlilik')
@section('page-lead')
    {{ $city }} ve çevresinde ciddi ilişki arayan yetişkinler için güvenli tanışma platformu. Ücretsiz kayıt ol, profilleri keşfet.
@endsection

@section('page-content')
    @include('partials.trust-badges')

    <div class="city-seo-stats">
        <p><strong>{{ number_format($memberCount) }}</strong> kayıtlı üye</p>
        @if($memberCount > 0)
            <p class="city-seo-stats-detail">{{ number_format($femaleCount) }} kadın · {{ number_format($maleCount) }} erkek profil</p>
        @endif
    </div>

    <p>
        Gönül Köprüsü, {{ $city }} bölgesinde evlilik ve ciddi ilişki arayan yetişkinleri bir araya getirir.
        Profil fotoğrafı, ilgi alanları ve konum bilgileriyle sana en uygun eşleşmeleri keşfedebilirsin.
    </p>

    <h2>Neden Gönül Köprüsü?</h2>
    <ul>
        <li>Moderasyon ve güvenli tanışma rehberi</li>
        <li>7/24 destek ekibi</li>
        <li>Kadın üyeler için güvenli ortam</li>
        <li>Ücretsiz kayıt — birkaç dakikada başla</li>
    </ul>

    <p class="city-seo-cta-wrap">
        <a href="{{ route('register', ['utm_source' => 'seo', 'utm_medium' => 'city', 'utm_campaign' => $slug]) }}" class="btn btn-primary">Ücretsiz Kayıt Ol</a>
        @guest
            <a href="{{ route('login') }}" class="btn btn-outline">Giriş Yap</a>
        @endguest
    </p>

    <h2>Popüler şehirler</h2>
    <ul class="city-seo-links">
        @foreach(['istanbul', 'ankara', 'izmir', 'bursa', 'antalya', 'adana'] as $popular)
            @if($popular !== $slug)
                <li><a href="{{ route('city.seo', $popular) }}">{{ ucfirst(str_replace('-', ' ', $popular)) }}</a></li>
            @endif
        @endforeach
    </ul>
@endsection

@push('head')
    <meta name="description" content="{{ $city }} tanışma ve evlilik — Gönül Köprüsü ile güvenli, ciddi ilişki odaklı tanışma. Ücretsiz kayıt.">
    <link rel="canonical" href="{{ route('city.seo', $slug) }}">
@endpush

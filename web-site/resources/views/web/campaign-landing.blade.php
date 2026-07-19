@extends('layouts.content-page')

@section('title', 'Ücretsiz Kayıt — Gönül Köprüsü')
@section('page-eyebrow', 'Kampanya')
@section('page-title')
    @if(!empty($cityName))
        {{ $cityName }} tanışma — ücretsiz üye ol
    @else
        Ücretsiz üye ol, güvenle tanış
    @endif
@endsection
@section('page-lead')
    Ciddi ilişki ve evlilik odaklı Gönül Köprüsü. Kadın üyelerde mesajlaşma ücretsiz; kayıt bir dakikadan az sürer.
@endsection

@section('page-content')
    @include('partials.trust-badges')

    <div class="campaign-landing-cta">
        @include('partials.google-auth-button', [
            'href' => $googleUrl,
            'label' => 'oogle ile hızlı kayıt',
            'event' => 'google_complete',
            'eventLabel' => 'campaign_'.$source,
            'iconSize' => 20,
        ])
        <a href="{{ $registerUrl }}" class="btn btn-outline btn-full" data-gk-event="sign_up_click" data-gk-event-label="campaign_{{ $campaign }}">
            E-posta ile ücretsiz kayıt
        </a>
        <p class="campaign-landing-note">
            Zaten üye misin? <a href="{{ route('login') }}">Giriş yap</a>
            · <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" data-gk-event="instagram_cta" data-gk-event-label="campaign">Instagram</a>
        </p>
    </div>

    <h2>Neden Gönül Köprüsü?</h2>
    <ul>
        <li>Güvenli sohbet, engelleme ve şikayet</li>
        <li>Moderasyonlu, ciddi ilişki odaklı ortam</li>
        <li>Şehrine göre keşif{{ !empty($cityName) ? ' — '.$cityName : '' }}</li>
        <li>Arkadaş davet ödülleri</li>
    </ul>

    @if(!empty($cityLinks))
        <h2>Popüler şehirler</h2>
        <ul class="city-seo-links">
            @foreach(array_slice($cityLinks, 0, 12) as $link)
                <li>
                    <a href="{{ route('city.seo', $link['slug']) }}?utm_source={{ urlencode($source) }}&utm_medium={{ urlencode($medium) }}&utm_campaign={{ urlencode($campaign) }}">
                        {{ $link['name'] }} tanışma
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
@endsection

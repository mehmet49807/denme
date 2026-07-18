@extends('layouts.content-page')

@section('title', $referrerName . ' seni davet etti — Gönül Köprüsü')
@section('page-eyebrow', 'Davet')
@section('page-title', $referrerName . ' seni Gönül Köprüsü’ne davet etti')
@section('page-lead')
    Ücretsiz kayıt ol, güvenli ve ciddi ilişki odaklı tanışmaya birkaç dakikada başla.
@endsection

@section('page-content')
    @include('partials.trust-badges')

    <div class="invite-landing-card">
        <p class="invite-landing-from">
            <strong>{{ $referrerName }}</strong>
            @if($referrer->city)
                <span>· {{ $referrer->city }}</span>
            @endif
            seni bekliyor.
        </p>
        <ul class="invite-landing-benefits">
            <li>Ücretsiz üyelik</li>
            <li>Moderasyon ve güvenli tanışma</li>
            <li>Ciddi ilişki odaklı topluluk</li>
        </ul>
        <p class="invite-landing-cta-wrap">
            <a href="{{ $registerUrl }}" class="btn btn-primary" data-gk-event="invite_cta_click" data-gk-event-label="register">Ücretsiz Kayıt Ol</a>
            <a href="{{ route('login', ['ref' => $code]) }}" class="btn btn-outline">Giriş Yap</a>
        </p>
        <p class="invite-landing-share">
            <a href="{{ $whatsappUrl }}" class="btn btn-ghost" target="_blank" rel="noopener" data-gk-event="invite_share" data-gk-event-label="whatsapp">WhatsApp ile paylaş</a>
        </p>
    </div>

    <p class="invite-landing-instagram">
        Bizi Instagram’da takip et:
        <a href="{{ \App\Support\InstagramUrl::withUtm('invite', 'landing', 'instagram') }}"
           target="_blank" rel="noopener"
           data-gk-event="instagram_cta" data-gk-event-label="invite_landing">@gonulkoprusucom</a>
    </p>
@endsection

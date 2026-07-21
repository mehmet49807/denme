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

    <div
        class="campaign-landing-cta"
        data-campaign-root
        data-utm-source="{{ $source }}"
        data-utm-medium="{{ $medium }}"
        data-utm-campaign="{{ $campaign }}"
        data-city="{{ $citySlug }}"
    >
        @include('partials.google-auth-button', [
            'label' => 'oogle ile hızlı kayıt',
            'event' => 'sign_up_click',
            'eventLabel' => 'campaign_'.$source.'_google',
            'iconSize' => 20,
            'gate' => true,
            'city' => $cityName ?? '',
        ])
        @include('partials.google-signup-gate')
        <a
            href="{{ $registerUrl }}"
            class="btn btn-outline btn-full"
            data-gk-event="sign_up_click"
            data-gk-event-label="campaign_{{ $campaign }}_email"
            data-funnel-step="email_register"
        >
            E-posta ile ücretsiz kayıt
        </a>
        <p class="campaign-landing-note">
            Zaten üye misin? <a href="{{ route('login') }}" data-gk-event="login_click" data-gk-event-label="campaign_{{ $campaign }}">Giriş yap</a>
            · <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" data-gk-event="instagram_cta" data-gk-event-label="campaign_{{ $campaign }}">Instagram</a>
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
                    <a
                        href="{{ route('city.seo', $link['slug']) }}?utm_source={{ urlencode($source) }}&utm_medium={{ urlencode($medium) }}&utm_campaign={{ urlencode($campaign) }}"
                        data-gk-event="city_cta_click"
                        data-gk-event-label="campaign_{{ $campaign }}_{{ $link['slug'] }}"
                    >
                        {{ $link['name'] }} tanışma
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    <script>
    (function () {
        var root = document.querySelector('[data-campaign-root]');
        if (!root) return;
        var payload = {
            event_category: 'growth',
            event_label: 'campaign_' + (root.getAttribute('data-utm-campaign') || 'growth'),
            utm_source: root.getAttribute('data-utm-source') || '',
            utm_medium: root.getAttribute('data-utm-medium') || '',
            utm_campaign: root.getAttribute('data-utm-campaign') || '',
            city: root.getAttribute('data-city') || ''
        };
        function track() {
            if (typeof window.gkTrack === 'function') {
                window.gkTrack('campaign_landing_view', payload);
                return true;
            }
            return false;
        }
        if (!track()) {
            var tries = 0;
            var timer = setInterval(function () {
                tries += 1;
                if (track() || tries > 20) clearInterval(timer);
            }, 250);
        }
        root.addEventListener('click', function (ev) {
            var el = ev.target && ev.target.closest ? ev.target.closest('[data-google-signup-gate], [data-funnel-step]') : null;
            if (!el || typeof window.gkTrack !== 'function') return;
            var step = el.getAttribute('data-funnel-step') || (el.hasAttribute('data-google-signup-gate') ? 'google_gate' : 'cta');
            window.gkTrack('campaign_funnel_step', Object.assign({}, payload, { funnel_step: step }));
        }, true);
    })();
    </script>
@endsection

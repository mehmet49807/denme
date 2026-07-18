@php
    $settings = app(\App\Services\SiteSettingsService::class);
    $androidUrl = trim((string) $settings->get('android_app_url', ''));
    $iosUrl = trim((string) $settings->get('ios_app_url', ''));
    $soon = __('app.premium.app_store_soon');
@endphp
<section class="pm-app" aria-labelledby="pm-app-title">
    <header class="pm-app__head">
        <p class="pm-app__eyebrow">{{ __('app.premium.app_cta_badge') }}</p>
        <h2 id="pm-app-title">{{ __('app.premium.app_cta_title') }}</h2>
        <p>{{ __('app.premium.app_cta_lead') }}</p>
    </header>

    <ol class="pm-app__steps">
        <li><span>1</span>{{ __('app.premium.app_cta_step_download') }}</li>
        <li><span>2</span>{{ __('app.premium.app_cta_step_choose') }}</li>
        <li><span>3</span>{{ __('app.premium.app_cta_step_start') }}</li>
    </ol>

    <div class="pm-app__stores">
        @if($androidUrl !== '')
            <a href="{{ $androidUrl }}" class="pm-app__store" target="_blank" rel="noopener noreferrer" aria-label="Google Play">
                <img src="{{ asset('images/icon-google-play.svg') }}?v=2022" alt="" width="22" height="24" decoding="async">
                <span>
                    <small>{{ __('app.premium.app_store_get') }}</small>
                    <strong>Google Play</strong>
                </span>
            </a>
        @else
            <span class="pm-app__store" aria-label="Google Play — {{ $soon }}">
                <img src="{{ asset('images/icon-google-play.svg') }}?v=2022" alt="" width="22" height="24" decoding="async">
                <span>
                    <small>{{ $soon }}</small>
                    <strong>Google Play</strong>
                </span>
            </span>
        @endif
        @if($iosUrl !== '')
            <a href="{{ $iosUrl }}" class="pm-app__store" target="_blank" rel="noopener noreferrer" aria-label="App Store">
                <img src="{{ asset('images/icon-apple.svg') }}" alt="" width="20" height="20" decoding="async">
                <span>
                    <small>{{ __('app.premium.app_store_get') }}</small>
                    <strong>App Store</strong>
                </span>
            </a>
        @else
            <span class="pm-app__store" aria-label="App Store — {{ $soon }}">
                <img src="{{ asset('images/icon-apple.svg') }}" alt="" width="20" height="20" decoding="async">
                <span>
                    <small>{{ $soon }}</small>
                    <strong>App Store</strong>
                </span>
            </span>
        @endif
    </div>

    <p class="pm-app__trust">
        @include('partials.theme-icon', ['icon' => 'shield'])
        {{ __('app.premium.app_cta_trust') }}
    </p>
</section>

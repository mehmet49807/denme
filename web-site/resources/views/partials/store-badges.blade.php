@php
    $settings = app(\App\Services\SiteSettingsService::class);
    $androidUrl = trim((string) $settings->get('android_app_url', ''));
    $iosUrl = trim((string) $settings->get('ios_app_url', ''));
@endphp
<div class="store-badges">
    @if($androidUrl !== '')
        <a href="{{ $androidUrl }}" class="store-badge store-badge--google" target="_blank" rel="noopener noreferrer" aria-label="Google Play">
            <span class="store-badge-icon store-badge-icon--google">
                <img src="{{ asset('images/icon-google-play.svg') }}?v=2022" alt="" width="29" height="32" decoding="async">
            </span>
            <span class="store-badge-text">
                <span class="store-badge-label">İndir</span>
                <span class="store-badge-name">Google Play</span>
            </span>
        </a>
    @else
        <span class="store-badge store-badge--google" aria-label="Google Play — Yakında">
            <span class="store-badge-icon store-badge-icon--google">
                <img src="{{ asset('images/icon-google-play.svg') }}?v=2022" alt="" width="29" height="32" decoding="async">
            </span>
            <span class="store-badge-text">
                <span class="store-badge-label">Yakında</span>
                <span class="store-badge-name">Google Play</span>
            </span>
        </span>
    @endif
    @if($iosUrl !== '')
        <a href="{{ $iosUrl }}" class="store-badge store-badge--apple" target="_blank" rel="noopener noreferrer" aria-label="App Store">
            <span class="store-badge-icon store-badge-icon--apple">
                <img src="{{ asset('images/icon-apple.svg') }}" alt="" width="24" height="24" decoding="async">
            </span>
            <span class="store-badge-text">
                <span class="store-badge-label">İndir</span>
                <span class="store-badge-name">App Store</span>
            </span>
        </a>
    @else
        <span class="store-badge store-badge--apple" aria-label="App Store — Yakında">
            <span class="store-badge-icon store-badge-icon--apple">
                <img src="{{ asset('images/icon-apple.svg') }}" alt="" width="24" height="24" decoding="async">
            </span>
            <span class="store-badge-text">
                <span class="store-badge-label">Yakında</span>
                <span class="store-badge-name">App Store</span>
            </span>
        </span>
    @endif
</div>

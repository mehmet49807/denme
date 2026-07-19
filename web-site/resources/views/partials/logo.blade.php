@php
    $logoVersion = (string) config('brand.logo_version', 'brand-v17');
@endphp
<a href="{{ auth()->check() ? route('feed') : route('home') }}" class="site-logo site-logo--brand" aria-label="{{ auth()->check() ? 'Gönül Köprüsü — Akış' : 'Gönül Köprüsü — Ana Sayfa' }}">
    <img
        src="{{ asset('images/logo-220.png') }}?v={{ $logoVersion }}"
        srcset="{{ asset('images/logo-180.png') }}?v={{ $logoVersion }} 180w, {{ asset('images/logo-220.png') }}?v={{ $logoVersion }} 220w, {{ asset('images/logo-320.png') }}?v={{ $logoVersion }} 320w"
        sizes="(max-width: 480px) 150px, (max-width: 900px) 190px, 220px"
        alt="Gönül Köprüsü — Gönülleri Birleştiren Köprü"
        class="site-logo-brand-img site-logo-brand-img--ink"
        width="220"
        height="76"
        decoding="async"
    >
    <img
        src="{{ asset('images/logo-220-light.png') }}?v={{ $logoVersion }}"
        srcset="{{ asset('images/logo-180-light.png') }}?v={{ $logoVersion }} 180w, {{ asset('images/logo-220-light.png') }}?v={{ $logoVersion }} 220w, {{ asset('images/logo-320-light.png') }}?v={{ $logoVersion }} 320w"
        sizes="(max-width: 480px) 150px, (max-width: 900px) 190px, 220px"
        alt=""
        aria-hidden="true"
        class="site-logo-brand-img site-logo-brand-img--light"
        width="220"
        height="76"
        decoding="async"
    >
</a>

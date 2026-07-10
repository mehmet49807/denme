@php
    $logoVersion = (string) config('brand.logo_version', 'brand-v15');
@endphp
<a href="{{ auth()->check() ? route('feed') : route('home') }}" class="site-logo site-logo--brand" aria-label="{{ auth()->check() ? 'Gönül Köprüsü — Akış' : 'Gönül Köprüsü — Ana Sayfa' }}">
    <img
        src="{{ asset('images/logo-220.png') }}?v={{ $logoVersion }}"
        srcset="{{ asset('images/logo-180.png') }}?v={{ $logoVersion }} 180w, {{ asset('images/logo-220.png') }}?v={{ $logoVersion }} 220w, {{ asset('images/logo-320.png') }}?v={{ $logoVersion }} 320w"
        sizes="(max-width: 480px) 150px, (max-width: 900px) 190px, 220px"
        alt="Gönül Köprüsü — Gönülleri Birleştiren Köprü"
        class="site-logo-brand-img"
        width="220"
        height="94"
        decoding="async"
    >
</a>

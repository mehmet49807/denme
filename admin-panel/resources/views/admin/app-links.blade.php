@extends('layouts.admin')

@section('title', 'Uygulama Linkleri')
@section('lead', 'Android uygulama demosu, Google Play / App Store linkleri. APK-AAB son adım.')

@section('header-actions')
    <a href="{{ $appDemoUrl }}" class="btn btn-primary" target="_blank" rel="noopener">Android demo</a>
@endsection

@section('content')
@if(session('success'))
    <div class="admin-flash admin-flash--success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="admin-flash admin-flash--error">
        <ul class="admin-flash-list">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="admin-panel admin-panel--glass">
    <header class="admin-package-card__head">
        <div>
            <h3 class="admin-panel-title">Android uygulama demosu</h3>
            <p class="admin-package-card__sub">
                APK / AAB üretmeden önce özel link ile mobil deneyimi kontrol edin.
                Tasarım birebir mevcut mobil web (Trusted Web Activity yolu).
            </p>
        </div>
    </header>
    <div class="admin-marketing-link-card" style="margin-top:0.75rem">
        <div class="admin-marketing-link-card__meta">
            <strong>Özel demo linki</strong>
            <span>Telefon çerçevesi + tam ekran uygulama modu</span>
        </div>
        <code class="admin-marketing-link-card__url" data-copy-source>{{ $appDemoUrl }}</code>
        <button type="button" class="btn btn-outline btn-sm admin-copy-btn" data-copy="{{ $appDemoUrl }}">Kopyala</button>
    </div>
    <div class="admin-form-actions" style="margin-top:0.85rem">
        <a href="{{ $appDemoUrl }}" class="btn btn-primary" target="_blank" rel="noopener">Demoyu aç</a>
        <a href="{{ $appDemoOpenUrl }}" class="btn btn-outline" target="_blank" rel="noopener">Tam ekran aç</a>
    </div>
    <p class="admin-package-card__sub" style="margin-top:0.75rem">
        Sıra: 1) Demo kontrol → 2) Onay → 3) APK + AAB (Play Store). Key: <code>gk-app-demo-2026</code>
    </p>
    <p class="admin-package-card__sub">
        App giriş teması (web’den ayrı):
        <a href="{{ $frontendUrl }}/uygulama/giris" target="_blank" rel="noopener">{{ $frontendUrl }}/uygulama/giris</a>
    </p>
</section>

<form method="POST" action="{{ route('admin.app-links.update') }}" class="admin-app-links-form">
    @csrf

    <section class="admin-panel admin-panel--glass">
        <header class="admin-package-card__head">
            <div>
                <h3 class="admin-panel-title">Mağaza linkleri</h3>
                <p class="admin-package-card__sub">Footer, ana sayfa ve Premium sayfasındaki mağaza rozetlerinde kullanılır.</p>
            </div>
        </header>

        <div class="admin-form-grid">
            <div class="form-group form-group--full">
                <label for="android_app_url">Android (Google Play) URL</label>
                <input
                    type="url"
                    id="android_app_url"
                    name="android_app_url"
                    value="{{ old('android_app_url', $androidAppUrl) }}"
                    placeholder="https://play.google.com/store/apps/details?id=..."
                    inputmode="url"
                    autocomplete="off"
                >
            </div>
            <div class="form-group form-group--full">
                <label for="ios_app_url">iOS (App Store) URL</label>
                <input
                    type="url"
                    id="ios_app_url"
                    name="ios_app_url"
                    value="{{ old('ios_app_url', $iosAppUrl) }}"
                    placeholder="https://apps.apple.com/app/id..."
                    inputmode="url"
                    autocomplete="off"
                >
            </div>
        </div>

        <div class="admin-app-links-preview">
            <p class="admin-package-card__sub">
                Android:
                @if(filled(old('android_app_url', $androidAppUrl)))
                    <a href="{{ old('android_app_url', $androidAppUrl) }}" target="_blank" rel="noopener">{{ old('android_app_url', $androidAppUrl) }}</a>
                @else
                    <em>Yakında</em>
                @endif
            </p>
            <p class="admin-package-card__sub">
                iOS:
                @if(filled(old('ios_app_url', $iosAppUrl)))
                    <a href="{{ old('ios_app_url', $iosAppUrl) }}" target="_blank" rel="noopener">{{ old('ios_app_url', $iosAppUrl) }}</a>
                @else
                    <em>Yakında</em>
                @endif
            </p>
        </div>
    </section>

    <div class="admin-form-actions">
        <button type="submit" class="btn btn-primary">Linkleri Kaydet</button>
        <a href="{{ route('admin.packages') }}" class="btn btn-outline">Paketlere Dön</a>
    </div>
</form>

<script>
(function () {
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            var text = btn.getAttribute('data-copy') || '';
            try {
                await navigator.clipboard.writeText(text);
                var prev = btn.textContent;
                btn.textContent = 'Kopyalandı';
                setTimeout(function () { btn.textContent = prev; }, 1400);
            } catch (e) {
                window.prompt('Kopyala:', text);
            }
        });
    });
})();
</script>
@endsection

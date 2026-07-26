<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#7C3AED">
    <title>Android Uygulama Demo — Gönül Köprüsü</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}?v={{ config('brand.logo_version') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/app-demo.css') }}?v=app-demo-1">
</head>
<body class="app-demo-gate">
    <main class="app-demo-gate__wrap">
        <header class="app-demo-gate__head">
            <img src="{{ asset('images/logo-180.png') }}?v={{ config('brand.logo_version') }}" alt="Gönül Köprüsü" width="72" height="72">
            <h1>Android uygulama demosu</h1>
            <p>
                Bu özel link, yayınlanacak Android uygulamasının <strong>birebir mobil web</strong> deneyimini gösterir.
                APK / AAB henüz üretilmedi — önce bu demoyu kontrol ediyoruz.
            </p>
        </header>

        <div class="app-demo-phone" aria-hidden="true">
            <div class="app-demo-phone__notch"></div>
            <div class="app-demo-phone__screen">
                <div class="app-demo-phone__status">
                    <span>9:41</span>
                    <span>Gönül Köprüsü</span>
                    <span>5G</span>
                </div>
                <iframe
                    class="app-demo-phone__frame"
                    title="Uygulama önizleme"
                    src="{{ $isAuthed ? $feedUrl : $loginUrl }}"
                    referrerpolicy="no-referrer-when-downgrade"
                    loading="lazy"
                ></iframe>
            </div>
        </div>

        <div class="app-demo-gate__actions">
            <a class="app-demo-gate__btn app-demo-gate__btn--primary" href="{{ $openUrl }}">
                Uygulamayı aç (tam ekran)
            </a>
            @if($isAuthed)
                <a class="app-demo-gate__btn" href="{{ $feedUrl }}">Akışa git</a>
            @else
                <a class="app-demo-gate__btn" href="{{ $loginUrl }}">Giriş yap / kayıt ol</a>
            @endif
            <a class="app-demo-gate__btn app-demo-gate__btn--ghost" href="{{ route('app.demo.exit') }}">Demo modundan çık</a>
        </div>

        <section class="app-demo-gate__notes">
            <h2>Kontrol listesi</h2>
            <ul>
                <li>Alt navigasyon (Üyeler, Profil, Akış, Mesajlar, Bildirim)</li>
                <li>Header Premium / Eşleşme / Ayarlar</li>
                <li>Hikâyeler, keşif, mesajlaşma</li>
                <li>Mobil genişlik ve safe-area (çentik) hissi</li>
            </ul>
            <p class="app-demo-gate__hint">
                Onay sonrası adım: Google Play için <strong>APK + AAB</strong> (Trusted Web Activity — aynı mobil site).
            </p>
        </section>
    </main>
</body>
</html>

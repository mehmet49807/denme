@extends('layouts.content-page')

@section('title', 'Hakkımızda — Gönül Köprüsü Tanışma Platformu')
@section('legal-active', 'about')
@section('page-eyebrow', 'Gönül Köprüsü')
@section('page-title', 'Hakkımızda')
@section('page-lead', 'Türkiye\'nin güvenli, ciddi ve modern tanışma platformu — kimiz, ne sunuyoruz, nasıl iletişime geçilir.')

@section('page-content')
<div class="content-feature-banner">
    <x-optimized-image name="landing-community" alt="Gönül Köprüsü topluluğu" width="640" height="360" />
    <div class="content-feature-banner-copy">
        <strong>Gönülleri Birleştiren Köprü</strong>
        <span>Evlilik odaklı topluluk</span>
    </div>
</div>

<p>
    Gönül Köprüsü, Türkiye'de <strong>ciddi ilişki</strong> ve anlamlı bağlar arayan yetişkinleri güvenli,
    saygılı ve modern bir ortamda buluşturan bir <strong>tanışma ve evlilik sitesi</strong>dir.
    Yüzeysel kaydırmalar yerine profil, konum, güvenli mesajlaşma ve moderasyonu ön planda tutarız.
</p>

<h2 id="kimiz">Kimiz?</h2>
<p>
    Bağımsız bir Türk tanışma platformuyuz. Operasyon ve destek ekibimiz üyelerin güvenliğini,
    KVKK uyumunu ve saygılı bir topluluk kültürünü önceleyen süreçlerle çalışır.
    Marka adımız <strong>Gönül Köprüsü</strong>; web sitemiz
    <a href="https://gonulkoprusu.com">gonulkoprusu.com</a>, Instagram hesabımız
    <a href="{{ \App\Support\InstagramUrl::withUtm('about', 'body', 'instagram') }}" target="_blank" rel="noopener">@gonulkoprusucom</a>.
</p>

<h2 id="misyon">Misyonumuz</h2>
<p>
    Teknolojiyi samimiyet ve güvenle birleştirerek insanların gerçek bağlar kurmasına
    aracılık etmek. Evlilik ve uzun soluklu ilişki niyeti taşıyan yetişkinler için
    şehir bazlı keşif ve güvenli sohbet sunmak.
</p>

<h2 id="ozellikler">Ne Sunuyoruz?</h2>
<div class="content-icon-cards">
    <div class="content-icon-card">
        @include('partials.theme-icon', ['icon' => 'heart'])
        <strong>Profil & keşif</strong>
        <span>Şehir ve ilçe bazlı üye keşfi</span>
    </div>
    <div class="content-icon-card">
        @include('partials.theme-icon', ['icon' => 'messages'])
        <strong>Mesajlaşma</strong>
        <span>Özel sohbet — kadınlarda ücretsiz</span>
    </div>
    <div class="content-icon-card">
        @include('partials.theme-icon', ['icon' => 'camera'])
        <strong>Akış & hikayeler</strong>
        <span>Fotoğraf paylaşımı ve etkileşim</span>
    </div>
    <div class="content-icon-card">
        @include('partials.theme-icon', ['icon' => 'star'])
        <strong>Premium</strong>
        <span>Pro, Gold ve Platinum paketler</span>
    </div>
</div>

<h2 id="guvenlik">Güvenlik, gizlilik ve KVKK</h2>
<p>
    Platformumuzda şikayet, engelleme ve moderasyon süreçleri aktiftir. Uygunsuz davranışlar
    incelenir; gerekli durumlarda hesaplar askıya alınır veya kapatılır. Kişisel verileriniz
    <a href="{{ route('privacy') }}">Gizlilik Sözleşmemiz</a> ve
    <a href="{{ route('kvkk') }}">KVKK Aydınlatma Metnimiz</a> kapsamında korunur.
    İlk buluşma için <a href="{{ route('safe-meeting') }}">güvenli tanışma rehberimizi</a> okuyun.
</p>

<h2 id="kimler">Kimler İçin?</h2>
<p>
    Gönül Köprüsü, 18 yaş ve üzeri, Türkiye'de yaşayan ve karşı cinsiyetten ciddi tanışma
    arayan bireyler için tasarlanmıştır. İstanbul, Ankara, İzmir ve 80+ şehirde ücretsiz kayıt açıktır.
</p>

<h2 id="guven">Neden güvenilir?</h2>
<ul>
    <li>Moderasyon ve şikayet / engelleme araçları</li>
    <li>Profil Google’da herkese açık listelenmez (üye alanları noindex)</li>
    <li>Şeffaf yasal metinler: gizlilik, KVKK, kullanım koşulları</li>
    <li>7/24 e-posta desteği: <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></li>
    <li>
        @if(Route::has('stories'))
            <a href="{{ route('stories') }}">Başarı hikâyeleri</a> ve
        @endif
        şehir / konu rehberleri
    </li>
</ul>

<h2 id="vizyon">Vizyonumuz</h2>
<p>
    Türkiye'nin en güvenilir tanışma platformu olmak; her eşleşmenin bir hikâyeye,
    her hikâyenin ise saygı ve güven üzerine kurulmasına katkı sağlamak.
</p>

<h2 id="iletisim">İletişim ve destek</h2>
<p>
    Soru, öneri, güvenlik bildirimi ve destek:<br>
    E-posta: <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a><br>
    Web: <a href="https://gonulkoprusu.com">gonulkoprusu.com</a><br>
    Destek formu: <a href="{{ route('support') }}">/destek</a><br>
    Instagram: <a href="{{ \App\Support\InstagramUrl::withUtm('about', 'contact', 'instagram') }}" target="_blank" rel="noopener">@gonulkoprusucom</a>
</p>

<p>
    Konu sayfaları:
    @if(Route::has('seo.marriage')) <a href="{{ route('seo.marriage') }}">Evlilik sitesi</a> · @endif
    @if(Route::has('seo.serious')) <a href="{{ route('seo.serious') }}">Ciddi ilişki</a> · @endif
    @if(Route::has('seo.free')) <a href="{{ route('seo.free') }}">Ücretsiz tanışma</a> · @endif
    <a href="{{ route('blog') }}">Blog</a> ·
    <a href="{{ route('sss') }}">SSS</a>
</p>
@endsection

@push('ld-json')
    @include('partials.json-ld', ['schema' => $jsonLd ?? []])
@endpush

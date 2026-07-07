@extends('layouts.content-page')

@section('title', 'Hakkımızda — Gönül Köprüsü')
@section('legal-active', 'about')
@section('page-eyebrow', 'Gönül Köprüsü')
@section('page-title', 'Hakkımızda')
@section('page-lead', 'Türkiye\'nin güvenli, ciddi ve modern tanışma platformu.')

@section('page-content')
<div class="content-feature-banner">
    <x-optimized-image name="landing-community" alt="Gönül Köprüsü topluluğu" width="640" height="360" />
    <div class="content-feature-banner-copy">
        <strong>Gönülleri Birleştiren Köprü</strong>
        <span>Evlilik odaklı topluluk</span>
    </div>
</div>

<p>
    Gönül Köprüsü, Türkiye'de ciddi ilişki ve anlamlı bağlar arayan yetişkinleri güvenli,
    saygılı ve modern bir ortamda buluşturan bir tanışma platformudur.
</p>

<h2 id="misyon">Misyonumuz</h2>
<p>
    Teknolojiyi samimiyet ve güvenle birleştirerek insanların gerçek bağlar kurmasına
    aracılık etmek. Yüzeysel kaydırmalar yerine; profil, mesajlaşma ve güvenli tanışma
    kültürünü ön planda tutuyoruz.
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
        <span>Özel ve güvenli sohbet</span>
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

<h2 id="guvenlik">Güvenlik ve Moderasyon</h2>
<p>
    Platformumuzda şikayet, engelleme ve moderasyon süreçleri aktiftir. Uygunsuz davranışlar
    incelenir; gerekli durumlarda hesaplar askıya alınır veya kapatılır. Kişisel verileriniz
    <a href="{{ route('privacy') }}">Gizlilik Sözleşmemiz</a> ve
    <a href="{{ route('kvkk') }}">KVKK Aydınlatma Metnimiz</a> kapsamında korunur.
</p>

<h2 id="kimler">Kimler İçin?</h2>
<p>
    Gönül Köprüsü, 18 yaş ve üzeri, Türkiye'de yaşayan ve karşı cinsiyetten ciddi tanışma
    arayan bireyler için tasarlanmıştır.
</p>

<h2 id="vizyon">Vizyonumuz</h2>
<p>
    Türkiye'nin en güvenilir tanışma platformu olmak; her eşleşmenin bir hikâyeye,
    her hikâyenin ise saygı ve güven üzerine kurulmasına katkı sağlamak.
</p>

<h2 id="iletisim">İletişim</h2>
<p>
    Soru, öneri ve destek: <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a><br>
    Web: <a href="https://gonulkoprusu.com">gonulkoprusu.com</a>
</p>
@endsection

@push('ld-json')
    @include('partials.json-ld', ['schema' => $jsonLd ?? []])
@endpush

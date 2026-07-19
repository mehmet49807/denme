@extends('layouts.content-page')

@section('title', 'Başarı Hikâyeleri — Ciddi İlişki ve Evlilik | Gönül Köprüsü')
@section('page-eyebrow', 'Tanışma hikâyeleri')
@section('page-title', 'Başarı hikâyeleri')
@section('page-lead', 'Ciddi ilişki ve evlilik niyetiyle Gönül Köprüsü’nde tanışan üyelerden esinlenen hikâyeler.')

@section('page-content')
    @include('partials.trust-badges')

    <p>
        Gönül Köprüsü; flört uygulamalarından farklı olarak <strong>ciddi ilişki</strong> ve
        <strong>evlilik</strong> odaklı bir topluluk kurar. Aşağıdaki anlatımlar üye deneyimlerinden
        esinlenerek hazırlanmış örnek hikâyelerdir — her bağ benzersizdir.
    </p>

    <p class="city-seo-cta-wrap">
        <a href="{{ $registerUrl }}" class="btn btn-primary" data-gk-event="sign_up_click" data-gk-event-label="success_stories">Ücretsiz Kayıt Ol</a>
        <a href="{{ $instagramUrl }}" class="btn btn-ghost" target="_blank" rel="noopener">Instagram</a>
        @if(Route::has('seo.marriage'))
            <a href="{{ route('seo.marriage') }}" class="btn btn-outline">Evlilik sitesi</a>
        @endif
    </p>

    @foreach($stories as $story)
        <article class="gk-story-block">
            <h2>{{ $story['names'] }} — {{ $story['city'] }}</h2>
            <p class="gk-story-quote">“{{ $story['quote'] }}”</p>
            <p>{{ $story['body'] }}</p>
        </article>
    @endforeach

    <h2>Senin hikâyen neden olmasın?</h2>
    <p>
        Ücretsiz üye ol, şehrini seç, profilini tamamla. Kadın üyelerde mesajlaşma ücretsizdir.
        Güvenli tanışma için <a href="{{ route('safe-meeting') }}">güvenli tanışma rehberimizi</a> oku;
        platform hakkında <a href="{{ route('about') }}">Hakkımızda</a> sayfamıza göz at.
    </p>

    @if(!empty($faqs))
        <h2>Sık sorulan sorular</h2>
        <div class="city-seo-faq">
            @foreach($faqs as $item)
                <details>
                    <summary>{{ $item['question'] }}</summary>
                    <p>{{ $item['answer'] }}</p>
                </details>
            @endforeach
        </div>
    @endif

    <p>
        <a href="{{ route('blog') }}">Blog</a> ·
        <a href="{{ route('sss') }}">SSS</a> ·
        <a href="{{ route('city.seo', 'istanbul') }}">İstanbul tanışma</a> ·
        <a href="{{ route('city.seo', 'ankara') }}">Ankara tanışma</a> ·
        <a href="{{ route('city.seo', 'izmir') }}">İzmir tanışma</a>
    </p>
@endsection

@push('ld-json')
@include('partials.json-ld', ['schema' => $jsonLd ?? []])
@endpush

@push('head')
<style>
.gk-story-block{margin:0 0 1.5rem;padding:0 0 1.25rem;border-bottom:1px solid rgba(15,23,42,.08)}
.gk-story-block:last-of-type{border-bottom:0}
.gk-story-quote{font-weight:700;color:var(--violet,#7c3aed);margin:.35rem 0 .65rem}
</style>
@endpush

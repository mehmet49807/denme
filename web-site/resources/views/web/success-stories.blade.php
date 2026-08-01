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
        <article class="gk-story-block gk-story-block--photo">
            <div class="gk-story-block__media">
                <x-optimized-image
                    name="{{ $story['image'] }}"
                    alt="{{ $story['image_alt'] ?? ($story['names'].' — '.$story['city']) }}"
                    width="960"
                    height="720"
                    loading="lazy"
                    sizes="(max-width: 768px) 100vw, 320px"
                />
            </div>
            <div class="gk-story-block__body">
                <h2>{{ $story['names'] }} — {{ $story['city'] }}</h2>
                <p class="gk-story-quote">“{{ $story['quote'] }}”</p>
                @if(!empty($story['note']))
                    <p class="gk-story-note">{{ $story['note'] }}</p>
                @endif
                <p>{{ $story['body'] }}</p>
            </div>
        </article>
    @endforeach

    <aside class="gk-story-thanks">
        <h2>Teşekkürler</h2>
        <p>
            Yuva kuranlara, nişanlananlara ve uzun soluklu bağ kuran herkese teşekkür ederiz.
            Evlilik ve ciddi ilişki yolunda yanınızdayız.
        </p>
    </aside>

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
                    <summary>{{ $item['question'] ?? '' }}</summary>
                    <p>{{ $item['answer'] ?? '' }}</p>
                </details>
            @endforeach
        </div>
    @endif

    <h2>Şehir ve ilçe keşfi</h2>
    <ul class="city-seo-links">
        <li><a href="{{ route('city.seo', 'istanbul') }}">İstanbul tanışma</a></li>
        <li><a href="{{ route('city.seo.district', ['slug' => 'istanbul', 'district' => 'kadikoy']) }}">Kadıköy</a></li>
        <li><a href="{{ route('city.seo', 'ankara') }}">Ankara tanışma</a></li>
        <li><a href="{{ route('city.seo.district', ['slug' => 'ankara', 'district' => 'cankaya']) }}">Çankaya</a></li>
        <li><a href="{{ route('city.seo', 'izmir') }}">İzmir tanışma</a></li>
        <li><a href="{{ route('city.seo.district', ['slug' => 'izmir', 'district' => 'karsiyaka']) }}">Karşıyaka</a></li>
        <li><a href="{{ route('blog') }}">Blog</a></li>
        <li><a href="{{ route('sss') }}">SSS</a></li>
        <li><a href="{{ url('/davet') }}">Arkadaşını davet et</a></li>
    </ul>
@endsection

@push('ld-json')
@include('partials.json-ld', ['schema' => $jsonLd ?? []])
@endpush

@push('head')
<style>
.gk-story-block--photo{
    display:grid;
    grid-template-columns:minmax(0,280px) minmax(0,1fr);
    gap:1.15rem;
    align-items:start;
    margin:0 0 1.75rem;
    padding:0 0 1.5rem;
    border-bottom:1px solid rgba(15,23,42,.08);
}
.gk-story-block--photo:last-of-type{border-bottom:0}
.gk-story-block__media{
    border-radius:16px;
    overflow:hidden;
    aspect-ratio:4/3;
    background:#f3e8ff;
}
.gk-story-block__media img,
.gk-story-block__media picture,
.gk-story-block__media .optimized-picture{
    display:block;width:100%;height:100%;
}
.gk-story-block__media img{object-fit:cover;width:100%;height:100%}
.gk-story-block__body h2{margin:0 0 .35rem;font-size:1.2rem;line-height:1.3}
.gk-story-quote{font-weight:700;color:#9a3412;margin:.2rem 0 .55rem}
.gk-story-note{margin:0 0 .65rem;color:#5c5470;font-size:.92rem}
.gk-story-thanks{
    margin:1.5rem 0 1.75rem;
    padding:1.15rem 1.2rem;
    border-radius:16px;
    background:linear-gradient(135deg,#fff8f4 0%,#fff 60%);
    border:1px solid rgba(26,18,37,.08);
}
.gk-story-thanks h2{margin:0 0 .45rem;font-size:1.2rem}
.gk-story-thanks p{margin:0;line-height:1.55}
@media (max-width:720px){
    .gk-story-block--photo{grid-template-columns:1fr}
}
</style>
@endpush

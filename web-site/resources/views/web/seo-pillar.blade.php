@extends('layouts.content-page')

@section('title', $documentTitle)
@section('page-eyebrow', $eyebrow)
@section('page-title', $h1)
@section('page-lead', $lead)

@section('page-content')
    @include('partials.trust-badges')

    <nav class="seo-pillar-nav" aria-label="Tanışma konuları">
        @foreach($pillarLinks as $link)
            <a href="{{ $link['url'] }}" class="{{ $link['active'] ? 'is-active' : '' }}">{{ $link['label'] }}</a>
        @endforeach
    </nav>

    @foreach($sections as $section)
        <h2>{{ $section['title'] }}</h2>
        <p>{{ $section['body'] }}</p>
    @endforeach

    <p class="city-seo-cta-wrap">
        <a href="{{ $registerUrl }}" class="btn btn-primary" data-gk-event="sign_up_click" data-gk-event-label="seo_pillar_{{ $pageKey }}">Ücretsiz Kayıt Ol</a>
        @guest
            <a href="{{ route('login') }}" class="btn btn-outline">Giriş Yap</a>
        @endguest
        <a href="{{ $instagramUrl }}" class="btn btn-ghost" target="_blank" rel="noopener">Instagram</a>
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

    @if(!empty($relatedPosts))
        <h2>Rehber yazıları</h2>
        <ul class="city-seo-blog-links">
            @foreach($relatedPosts as $post)
                <li>
                    <a href="{{ url('/blog/'.($post['slug'] ?? '')) }}">{{ $post['title'] ?? 'Blog' }}</a>
                    @if(!empty($post['description']))
                        <span>{{ \Illuminate\Support\Str::limit($post['description'], 110) }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
        <p>
            <a href="{{ route('blog') }}">Tüm blog</a> ·
            <a href="{{ route('sss') }}">SSS</a> ·
            <a href="{{ route('safe-meeting') }}">Güvenli tanışma</a> ·
            <a href="{{ route('about') }}">Hakkımızda</a>
            @if(Route::has('stories'))
                · <a href="{{ route('stories') }}">Başarı hikâyeleri</a>
            @endif
        </p>
    @endif

    <h2>Güven ve şeffaflık</h2>
    <p>
        Kişisel verilerin <a href="{{ route('kvkk') }}">KVKK</a> kapsamında işlenir;
        <a href="{{ route('privacy') }}">gizlilik politikamızı</a> ve
        <a href="{{ route('safe-meeting') }}">güvenli tanışma rehberimizi</a> okuyabilirsin.
        Destek: <a href="mailto:destek@gonulkoprusu.com">destek@gonulkoprusu.com</a>.
    </p>

    <h2>Şehir şehir tanışma</h2>
    <ul class="city-seo-links">
        @foreach($cityLinks as $link)
            <li><a href="{{ route('city.seo', $link['slug']) }}">{{ $link['name'] }} tanışma</a></li>
        @endforeach
    </ul>
@endsection

@push('ld-json')
@include('partials.json-ld', ['schema' => $jsonLd ?? []])
@endpush

@push('head')
<style>
.seo-pillar-nav{display:flex;flex-wrap:wrap;gap:.45rem;margin:0 0 1.25rem}
.seo-pillar-nav a{display:inline-flex;padding:.4rem .75rem;border-radius:999px;font-size:.82rem;font-weight:700;text-decoration:none;color:var(--violet);background:rgba(124,58,237,.08);border:1px solid rgba(124,58,237,.16)}
.seo-pillar-nav a.is-active{color:#fff;background:var(--gradient-sunset);border-color:transparent}
</style>
@endpush

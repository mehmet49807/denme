@extends('layouts.content-page')

@section('title', $city . ' Tanışma ve Evlilik Sitesi — Gönül Köprüsü')
@section('page-eyebrow', 'Şehir rehberi')
@section('page-title', $city . ' tanışma ve evlilik')
@section('page-lead')
    {{ $city }} ve çevresinde ciddi ilişki arayan yetişkinler için güvenli tanışma platformu. Ücretsiz kayıt ol, profilleri keşfet.
@endsection

@section('page-content')
    @include('partials.trust-badges')

    <div class="city-seo-stats">
        <p><strong>{{ number_format($memberCount) }}</strong> kayıtlı üye</p>
        @if($memberCount > 0)
            <p class="city-seo-stats-detail">{{ number_format($femaleCount) }} kadın · {{ number_format($maleCount) }} erkek profil</p>
        @endif
    </div>

    <p>
        Gönül Köprüsü, <strong>{{ $city }}</strong> bölgesinde evlilik ve ciddi ilişki arayan yetişkinleri bir araya getirir.
        Profil fotoğrafı, ilgi alanları ve konum bilgileriyle sana en uygun eşleşmeleri keşfedebilirsin.
    </p>

    <h2>{{ $city }}’da neden Gönül Köprüsü?</h2>
    <ul>
        <li>{{ $city }} odaklı üye keşfi ve konum filtreleri</li>
        <li>Moderasyon ve güvenli tanışma rehberi</li>
        <li>Kadın üyeler için güvenli ortam</li>
        <li>Ücretsiz kayıt — birkaç dakikada başla</li>
    </ul>

    <p class="city-seo-cta-wrap">
        <a href="{{ $registerUrl }}" class="btn btn-primary" data-gk-event="city_cta_click" data-gk-event-label="{{ $slug }}">Ücretsiz Kayıt Ol</a>
        @guest
            <a href="{{ route('login') }}" class="btn btn-outline">Giriş Yap</a>
        @endguest
        <a href="{{ $instagramUrl }}" class="btn btn-ghost" target="_blank" rel="noopener" data-gk-event="instagram_cta" data-gk-event-label="city_{{ $slug }}">Instagram</a>
    </p>

    @if(!empty($relatedPosts))
        <h2>{{ $city }} ve tanışma rehberi</h2>
        <ul class="city-seo-blog-links">
            @foreach($relatedPosts as $post)
                <li>
                    <a href="{{ url('/blog/'.($post['slug'] ?? '')) }}">{{ $post['title'] ?? 'Blog yazısı' }}</a>
                    @if(!empty($post['description']))
                        <span>{{ \Illuminate\Support\Str::limit($post['description'], 110) }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
        <p><a href="{{ route('blog') }}">Tüm blog yazıları</a> · <a href="{{ route('sss') }}">SSS</a></p>
    @endif

    @if(!empty($faqItems))
        <h2>Sık sorulanlar</h2>
        <div class="city-seo-faq">
            @foreach($faqItems as $item)
                <details>
                    <summary>{{ $item['question'] ?? '' }}</summary>
                    <p>{{ $item['answer'] ?? '' }}</p>
                </details>
            @endforeach
        </div>
    @endif

    <h2>Türkiye’de popüler şehirler</h2>
    <ul class="city-seo-links">
        @foreach($cityLinks as $link)
            @if($link['slug'] !== $slug)
                <li><a href="{{ route('city.seo', $link['slug']) }}">{{ $link['name'] }} tanışma</a></li>
            @endif
        @endforeach
    </ul>
@endsection

@push('head')
    <meta name="description" content="{{ $city }} tanışma ve evlilik sitesi — Gönül Köprüsü ile güvenli, ciddi ilişki odaklı tanışma. Ücretsiz kayıt.">
    <link rel="canonical" href="{{ route('city.seo', $slug) }}">
@endpush

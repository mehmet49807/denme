@extends('layouts.content-page')

@php
    $placeTitle = !empty($district) ? ($district.', '.$city) : $city;
@endphp

@section('title', $placeTitle . ' Tanışma, Sohbet ve Evlilik Sitesi — Gönül Köprüsü')
@section('page-eyebrow', !empty($district) ? 'İlçe rehberi' : 'Şehir rehberi')
@section('page-title', $placeTitle . ' tanışma sitesi — güvenli sohbet ve evlilik')
@section('page-lead')
    {{ $seoLead ?? ($placeTitle . ' tanışma sitesi: ücretsiz üye ol, güvenli online sohbet et, ciddi ilişki ve evlilik odaklı profilleri keşfet.') }}
@endsection

@section('page-content')
    @include('partials.trust-badges')

    @if(!empty($district))
        <p class="city-seo-breadcrumb-inline">
            <a href="{{ route('city.seo', $slug) }}">{{ $city }} tanışma</a>
            · {{ $district }}
        </p>
    @endif

    <div class="city-seo-stats">
        <p><strong>{{ number_format($memberCount) }}</strong> kayıtlı üye</p>
        @if($memberCount > 0)
            <p class="city-seo-stats-detail">{{ number_format($femaleCount) }} kadın · {{ number_format($maleCount) }} erkek profil</p>
        @endif
    </div>

    <p>
        <strong>{{ $placeTitle }} tanışma</strong> arıyorsan Gönül Köprüsü, evlilik ve ciddi ilişki niyetiyle
        <strong>{{ $placeTitle }}</strong> ve çevresindeki yetişkinleri bir araya getirir.
        Ücretsiz kayıt sonrası profil fotoğrafı, ilgi alanları ve konum bilgileriyle güvenli <strong>online sohbet</strong> başlatabilirsin.
        Kadın üyelerde mesajlaşma ücretsizdir.
    </p>

    <h2>{{ $placeTitle }}’da neden Gönül Köprüsü?</h2>
    <ul>
        @foreach(($seoWhy ?? []) as $reason)
            <li>{{ $reason }}</li>
        @endforeach
    </ul>

    <p class="city-seo-cta-wrap">
        <a href="{{ $registerUrl }}" class="btn btn-primary" data-gk-event="city_cta_click" data-gk-event-label="{{ $slug }}{{ !empty($district) ? '-'.\App\Support\SeoDistricts::slug($district) : '' }}">Ücretsiz Kayıt Ol</a>
        @guest
            <a href="{{ route('login') }}" class="btn btn-outline">Giriş Yap</a>
        @endguest
        <a href="{{ $instagramUrl }}" class="btn btn-ghost" target="_blank" rel="noopener" data-gk-event="instagram_cta" data-gk-event-label="city_{{ $slug }}">Instagram</a>
    </p>

    @if(!empty($districtLinks) && empty($district))
        <h2>{{ $city }} ilçelerinde tanışma</h2>
        <ul class="city-seo-links">
            @foreach($districtLinks as $d)
                <li><a href="{{ route('city.seo.district', ['slug' => $slug, 'district' => $d['slug']]) }}">{{ $d['name'] }} tanışma</a></li>
            @endforeach
        </ul>
    @endif

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
    @endif

    @if(!empty($faqItems))
        <h2>Sık sorulan sorular</h2>
        <div class="city-seo-faq">
            @foreach($faqItems as $item)
                <details>
                    <summary>{{ $item['question'] ?? '' }}</summary>
                    <p>{{ $item['answer'] ?? '' }}</p>
                </details>
            @endforeach
        </div>
    @endif

    <h2>Diğer şehirler</h2>
    <ul class="city-seo-links">
        @foreach($cityLinks as $link)
            @if($link['slug'] !== $slug)
                <li><a href="{{ route('city.seo', $link['slug']) }}">{{ $link['name'] }} tanışma</a></li>
            @endif
        @endforeach
    </ul>

    <h2>İlgili konular</h2>
    <ul class="city-seo-links">
        @if(Route::has('seo.marriage'))
            <li><a href="{{ route('seo.marriage') }}">Evlilik sitesi</a></li>
        @endif
        @if(Route::has('seo.serious'))
            <li><a href="{{ route('seo.serious') }}">Ciddi ilişki</a></li>
        @endif
        @if(Route::has('seo.free'))
            <li><a href="{{ route('seo.free') }}">Ücretsiz tanışma sitesi</a></li>
        @endif
        @if(Route::has('seo.friendship'))
            <li><a href="{{ route('seo.friendship') }}">Arkadaşlık sitesi</a></li>
        @endif
        @if(Route::has('stories'))
            <li><a href="{{ route('stories') }}">Başarı hikâyeleri</a></li>
        @endif
        <li><a href="{{ route('safe-meeting') }}">Güvenli tanışma</a></li>
        <li><a href="{{ route('about') }}">Hakkımızda</a></li>
    </ul>
@endsection

@push('ld-json')
@include('partials.json-ld', ['schema' => $jsonLd ?? []])
@endpush

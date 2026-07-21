@extends('layouts.content-page')

@section('title', ($post['title'] ?? 'Blog') . ' — Gönül Köprüsü')
@section('legal-active', 'blog')
@section('page-eyebrow', 'Blog')
@section('page-title', $post['title'] ?? 'Blog yazısı')
@section('page-lead', $post['description'] ?? '')

@section('page-content')
    @foreach(($post['sections'] ?? []) as $section)
        @if(!empty($section['heading']))
            <h2>{{ $section['heading'] }}</h2>
        @endif
        @if(!empty($section['body']))
            <p>{!! nl2br(e($section['body'])) !!}</p>
        @endif
    @endforeach

    @if(!empty($post['faq']))
        <h2>Sıkça sorulan sorular</h2>
        <div class="city-seo-faq">
            @foreach($post['faq'] as $item)
                <details>
                    <summary>{{ $item['question'] ?? '' }}</summary>
                    <p>{{ $item['answer'] ?? '' }}</p>
                </details>
            @endforeach
        </div>
    @endif

    <p>
        @if(Route::has('seo.marriage'))
            <a href="{{ route('seo.marriage') }}">Evlilik sitesi</a> ·
        @endif
        @if(Route::has('seo.serious'))
            <a href="{{ route('seo.serious') }}">Ciddi ilişki</a> ·
        @endif
        @if(Route::has('stories'))
            <a href="{{ route('stories') }}">Başarı hikâyeleri</a> ·
        @endif
        <a href="{{ route('safe-meeting') }}">Güvenli tanışma</a>
    </p>

    <h2>Popüler şehirlerde tanışma</h2>
    <ul class="city-seo-links">
        @php
            try {
                $blogCities = array_slice(\App\Support\FeaturedCities::links(app(\App\Services\LocationDataService::class)), 0, 10);
            } catch (\Throwable) {
                $blogCities = [
                    ['slug' => 'istanbul', 'name' => 'İstanbul'],
                    ['slug' => 'ankara', 'name' => 'Ankara'],
                    ['slug' => 'izmir', 'name' => 'İzmir'],
                ];
            }
        @endphp
        @foreach($blogCities as $blogCity)
            <li>
                <a href="{{ route('city.seo', $blogCity['slug']) }}" data-gk-event="city_cta_click" data-gk-event-label="blog_{{ $blogCity['slug'] }}">
                    {{ $blogCity['name'] }} tanışma
                </a>
            </li>
        @endforeach
    </ul>

    <p class="city-seo-cta-wrap">
        <a href="{{ url('/blog') }}" class="btn btn-outline">Tüm blog yazıları</a>
        <a href="{{ route('register', ['utm_source' => 'blog', 'utm_medium' => 'post', 'utm_campaign' => $slug ?? 'seo']) }}" class="btn btn-primary" data-gk-event="sign_up_click" data-gk-event-label="blog_post">Ücretsiz Kayıt Ol</a>
    </p>
@endsection

@push('ld-json')
    @include('partials.json-ld', ['schema' => $jsonLd ?? []])
@endpush

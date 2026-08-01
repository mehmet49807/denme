@extends('layouts.content-page')

@section('title', 'Blog — Gönül Köprüsü')
@section('legal-active', 'blog')
@section('page-eyebrow', 'Gönül Köprüsü')
@section('page-title', 'Blog')
@section('page-lead', 'Şehir rehberleri, ciddi ilişki tavsiyeleri ve evlilik odaklı yazılar.')

@section('page-content')
<div class="blog-hero">
    <x-optimized-image name="blog-city-general" alt="Gönül Köprüsü Blog" width="960" height="640" loading="eager" sizes="(max-width: 768px) 100vw, 720px" />
    <div class="blog-hero__copy">
        <strong>İlişki ve Evlilik Rehberi</strong>
        <span>Şehrine göre güvenli tanışma yazıları</span>
    </div>
</div>

@if(!empty($posts))
    <div class="blog-grid">
        @foreach($posts as $post)
            @php
                $slug = (string) ($post['slug'] ?? '');
                $title = (string) ($post['title'] ?? 'Blog yazısı');
                $description = (string) ($post['description'] ?? '');
                $visual = \App\Support\BlogPostVisual::forPost(is_array($post) ? $post : []);
            @endphp
            <article class="blog-card">
                <a href="{{ $slug !== '' ? url('/blog/'.$slug) : '#' }}" class="blog-card__media" tabindex="-1" aria-hidden="true">
                    <x-optimized-image
                        name="{{ $visual['image'] }}"
                        alt="{{ $visual['alt'] }}"
                        width="640"
                        height="427"
                        loading="lazy"
                        sizes="(max-width: 768px) 100vw, 300px"
                    />
                    <span class="blog-card__city">{{ $visual['label'] }}</span>
                </a>
                <div class="blog-card__body">
                    <h2><a href="{{ $slug !== '' ? url('/blog/'.$slug) : '#' }}">{{ $title }}</a></h2>
                    @if($description !== '')
                        <p>{{ \Illuminate\Support\Str::limit($description, 120) }}</p>
                    @endif
                    <p class="blog-meta">
                        @if(!empty($post['updated_at']))<span>{{ $post['updated_at'] }}</span>@endif
                        @if(!empty($post['reading_time']))<span>{{ $post['reading_time'] }}</span>@endif
                    </p>
                    @if($slug !== '')
                        <a href="{{ url('/blog/'.$slug) }}" class="blog-card__more">Devamını oku →</a>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
@endif

@if(!empty($homeStories))
    <section class="blog-stories" aria-labelledby="blog-stories-heading">
        <div class="blog-stories__head">
            <h2 id="blog-stories-heading">Başarı hikâyeleri</h2>
            <p>Gönül Köprüsü’nde tanışıp bağ kuranlardan kısa öyküler.</p>
        </div>
        <div class="blog-stories__grid">
            @foreach($homeStories as $story)
                <article class="blog-story-card">
                    <div class="blog-story-card__media">
                        <x-optimized-image
                            name="{{ $story['image'] }}"
                            alt="{{ $story['image_alt'] ?? ($story['names'].' — '.$story['city']) }}"
                            width="640"
                            height="480"
                            loading="lazy"
                            sizes="(max-width: 768px) 45vw, 200px"
                        />
                    </div>
                    <div class="blog-story-card__body">
                        <h3>{{ $story['names'] }}</h3>
                        <p class="blog-story-card__city">{{ $story['city'] }}</p>
                        <p class="blog-story-card__quote">“{{ $story['quote'] }}”</p>
                    </div>
                </article>
            @endforeach
        </div>
        @if(Route::has('stories'))
            <p class="blog-stories__more">
                <a href="{{ route('stories') }}" class="btn btn-outline">Tüm başarı hikâyeleri</a>
            </p>
        @endif
    </section>
@endif

<p class="city-seo-cta-wrap">
    <a href="{{ url('/sss') }}" class="btn btn-outline">Sıkça Sorulan Sorular</a>
    <a href="{{ route('register') }}" class="btn btn-primary">Ücretsiz Kayıt Ol</a>
</p>
@endsection

@push('ld-json')
    @include('partials.json-ld', ['schema' => $jsonLd ?? []])
@endpush

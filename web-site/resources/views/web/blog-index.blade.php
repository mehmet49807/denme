@extends('layouts.content-page')

@section('title', 'Blog — Gönül Köprüsü')
@section('page-eyebrow', 'Blog')
@section('page-title', 'Ciddi ilişki ve güvenli tanışma rehberi')
@section('page-lead')
    Evlilik ve ciddi ilişki odaklı güncel Türkçe blog yazılarımızı okuyun.
@endsection

@section('page-content')
    @if(empty($posts))
        <p>Henüz yayınlanmış blog yazısı bulunmuyor. Yönetim panelinden yeni içerik üretilebilir.</p>
    @else
        <div class="blog-index-list">
            @foreach($posts as $post)
                @php
                    $slug = (string) ($post['slug'] ?? '');
                    $title = (string) ($post['title'] ?? 'Blog yazısı');
                    $description = (string) ($post['description'] ?? '');
                @endphp
                <article class="blog-index-item">
                    <h2><a href="{{ route('blog.show', $slug) }}">{{ $title }}</a></h2>
                    @if($description !== '')
                        <p>{{ $description }}</p>
                    @endif
                    <p class="blog-index-meta">
                        @if(!empty($post['reading_time']))
                            <span>{{ $post['reading_time'] }}</span>
                        @endif
                        @if(!empty($post['updated_at']))
                            <span>{{ $post['updated_at'] }}</span>
                        @endif
                    </p>
                    <a href="{{ route('blog.show', $slug) }}" class="btn btn-outline btn-sm">Devamını oku</a>
                </article>
            @endforeach
        </div>
    @endif

    <p class="city-seo-cta-wrap">
        <a href="{{ route('sss') }}" class="btn btn-outline">Sıkça Sorulan Sorular</a>
        <a href="{{ route('register') }}" class="btn btn-primary">Ücretsiz Kayıt Ol</a>
    </p>
@endsection

@push('head')
    <meta name="description" content="Gönül Köprüsü blog — ciddi ilişki, güvenli tanışma ve evlilik odaklı Türkçe rehber yazıları.">
    <link rel="canonical" href="{{ route('blog.index') }}">
@endpush

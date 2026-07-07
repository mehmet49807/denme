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
        <dl class="blog-faq-list">
            @foreach($post['faq'] as $item)
                <dt>{{ $item['question'] ?? '' }}</dt>
                <dd>{{ $item['answer'] ?? '' }}</dd>
            @endforeach
        </dl>
    @endif

    <p class="city-seo-cta-wrap">
        <a href="{{ url('/blog') }}" class="btn btn-outline">Tüm blog yazıları</a>
        <a href="{{ route('register') }}" class="btn btn-primary">Ücretsiz Kayıt Ol</a>
    </p>
@endsection

@push('head')
    <meta name="description" content="{{ $post['description'] ?? ($post['title'] ?? 'Blog') }}">
    <link rel="canonical" href="{{ url('/blog/'.$slug) }}">
@endpush

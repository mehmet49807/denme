@extends('layouts.content-page')

@section('title', 'Sıkça Sorulan Sorular — Gönül Köprüsü')
@section('page-eyebrow', 'SSS')
@section('page-title', 'Sıkça Sorulan Sorular')
@section('page-lead')
    Gönül Köprüsü hakkında en çok merak edilen sorular ve yanıtları.
@endsection

@section('page-content')
    @if(empty($faqItems))
        <p>Henüz yayınlanmış SSS içeriği bulunmuyor. Yönetim panelinden yeni içerik üretilebilir.</p>
    @else
        <dl class="blog-faq-list">
            @foreach($faqItems as $item)
                <dt>{{ $item['question'] ?? '' }}</dt>
                <dd>{{ $item['answer'] ?? '' }}</dd>
            @endforeach
        </dl>
    @endif

    @if(!empty($blogPosts))
        <h2>İlgili blog yazıları</h2>
        <ul class="city-seo-links">
            @foreach($blogPosts as $post)
                @php $slug = (string) ($post['slug'] ?? ''); @endphp
                @if($slug !== '')
                    <li><a href="{{ route('blog.show', $slug) }}">{{ $post['title'] ?? $slug }}</a></li>
                @endif
            @endforeach
        </ul>
    @endif

    <p class="city-seo-cta-wrap">
        <a href="{{ route('blog') }}" class="btn btn-outline">Blog</a>
        <a href="{{ route('support') }}" class="btn btn-outline">Destek</a>
        <a href="{{ route('register') }}" class="btn btn-primary">Ücretsiz Kayıt Ol</a>
    </p>
@endsection

@push('head')
    <meta name="description" content="Gönül Köprüsü SSS — güvenli tanışma, ciddi ilişki, üyelik ve moderasyon hakkında sıkça sorulan sorular.">
    <link rel="canonical" href="{{ route('sss') }}">
@endpush

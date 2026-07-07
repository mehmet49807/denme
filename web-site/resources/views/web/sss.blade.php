@extends('layouts.content-page')

@section('title', 'Sıkça Sorulan Sorular — Gönül Köprüsü')
@section('legal-active', 'sss')
@section('page-eyebrow', 'SSS')
@section('page-title', 'Sıkça Sorulan Sorular')
@section('page-lead')
    Gönül Köprüsü hakkında en çok merak edilen sorular ve yanıtları.
@endsection

@section('page-content')
    @if(empty($faqItems))
        <p>Henüz yayınlanmış SSS içeriği bulunmuyor. Yönetim panelindeki AI ekranından yeni içerik üretilebilir.</p>
    @else
        <dl class="blog-faq-list">
            @foreach($faqItems as $item)
                <dt>{{ $item['question'] ?? '' }}</dt>
                <dd>{{ $item['answer'] ?? '' }}</dd>
            @endforeach
        </dl>
    @endif

    @if(!empty($posts))
        <h2>İlgili blog yazıları</h2>
        <ul class="city-seo-links">
            @foreach($posts as $post)
                @php $slug = (string) ($post['slug'] ?? ''); @endphp
                @if($slug !== '')
                    <li><a href="{{ url('/blog/'.$slug) }}">{{ $post['title'] ?? $slug }}</a></li>
                @endif
            @endforeach
        </ul>
    @endif

    <p class="city-seo-cta-wrap">
        <a href="{{ url('/blog') }}" class="btn btn-outline">Blog</a>
        <a href="{{ route('support') }}" class="btn btn-outline">Destek</a>
        <a href="{{ route('register') }}" class="btn btn-primary">Ücretsiz Kayıt Ol</a>
    </p>
@endsection

@push('head')
    <meta name="description" content="Gönül Köprüsü SSS — güvenli tanışma, ciddi ilişki, üyelik ve moderasyon hakkında sıkça sorulan sorular.">
    <link rel="canonical" href="{{ url('/sss') }}">
@endpush

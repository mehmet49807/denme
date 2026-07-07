@extends('layouts.content-page')

@section('title', 'Sıkça Sorulan Sorular — Gönül Köprüsü')
@section('legal-active', 'sss')
@section('page-eyebrow', 'Gönül Köprüsü')
@section('page-title', 'Sıkça Sorulan Sorular')
@section('page-lead', 'Güvenli tanışma, üyelik ve moderasyon hakkında en çok merak edilen sorular.')

@section('page-content')
<div class="content-feature-banner">
    <x-optimized-image name="landing-community" alt="Gönül Köprüsü SSS" width="640" height="360" />
    <div class="content-feature-banner-copy">
        <strong>Bilgi Merkezi</strong>
        <span>Merak ettiklerinizin yanıtları</span>
    </div>
</div>

@if(!empty($faqItems))
    <div class="blog-posts-list">
        @foreach($faqItems as $item)
            @php
                $question = (string) ($item['question'] ?? '');
                $answer = (string) ($item['answer'] ?? '');
            @endphp
            @if($question !== '')
                <article class="blog-card">
                    <h2>{{ $question }}</h2>
                    @if($answer !== '')
                        <p>{{ $answer }}</p>
                    @endif
                </article>
            @endif
        @endforeach
    </div>
@else
    <div class="blog-posts-list">
        <article class="blog-card">
            <h2>Gönül Köprüsü nedir?</h2>
            <p>Gönül Köprüsü, ciddi ilişki ve evlilik arayan yetişkinler için güvenli, saygılı ve modern bir tanışma platformudur.</p>
        </article>
        <article class="blog-card">
            <h2>Üyelik ücretli mi?</h2>
            <p>Temel üyelik ücretsizdir. Erkek üyeler için mesajlaşma ve ek özellikler premium paketlerle sunulur.</p>
        </article>
        <article class="blog-card">
            <h2>Güvenli tanışma nasıl sağlanır?</h2>
            <p>Profil doğrulama, şikayet ve engelleme sistemi ile moderasyon ekibimiz platform güvenliğini sürekli denetler.</p>
        </article>
        <article class="blog-card">
            <h2>Kişisel verilerim nasıl korunur?</h2>
            <p>Verileriniz KVKK kapsamında işlenir. Detaylar için Gizlilik Sözleşmesi ve KVKK Aydınlatma Metni sayfalarına bakabilirsiniz.</p>
        </article>
    </div>
@endif

@if(!empty($posts))
    <h2>İlgili blog yazıları</h2>
    <div class="blog-posts-list">
        @foreach($posts as $post)
            @php
                $slug = (string) ($post['slug'] ?? '');
                $title = (string) ($post['title'] ?? 'Blog yazısı');
                $description = (string) ($post['description'] ?? '');
            @endphp
            @if($slug !== '')
                <article class="blog-card">
                    <h2><a href="{{ url('/blog/'.$slug) }}">{{ $title }}</a></h2>
                    @if($description !== '')
                        <p>{{ $description }}</p>
                    @endif
                    <a href="{{ url('/blog/'.$slug) }}" class="btn btn-outline btn-sm">Devamını Oku</a>
                </article>
            @endif
        @endforeach
    </div>
@endif

<p class="city-seo-cta-wrap">
    <a href="{{ url('/blog') }}" class="btn btn-outline">Blog</a>
    <a href="{{ route('register') }}" class="btn btn-primary">Ücretsiz Kayıt Ol</a>
</p>
@endsection

@push('head')
    <meta name="description" content="Gönül Köprüsü SSS — güvenli tanışma, ciddi ilişki, üyelik ve moderasyon hakkında sıkça sorulan sorular.">
    <link rel="canonical" href="{{ url('/sss') }}">
@endpush

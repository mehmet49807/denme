@extends('layouts.content-page')

@section('title', 'Blog — Gönül Köprüsü')
@section('legal-active', 'blog')
@section('page-eyebrow', 'Gönül Köprüsü')
@section('page-title', 'Blog')
@section('page-lead', 'İlişki tavsiyeleri, evlilik rehberleri ve mutlu birliktelik sırları.')

@section('page-content')
<div class="content-feature-banner">
    <x-optimized-image name="landing-community" alt="Gönül Köprüsü Blog" width="640" height="360" />
    <div class="content-feature-banner-copy">
        <strong>İlişki ve Evlilik Rehberi</strong>
        <span>Mutlu yuvaların adresi</span>
    </div>
</div>

@if(!empty($posts))
    <div class="blog-posts-list">
        @foreach($posts as $post)
            @php
                $slug = (string) ($post['slug'] ?? '');
                $title = (string) ($post['title'] ?? 'Blog yazısı');
                $description = (string) ($post['description'] ?? '');
            @endphp
            <article class="blog-card">
                <h2><a href="{{ $slug !== '' ? url('/blog/'.$slug) : '#' }}">{{ $title }}</a></h2>
                @if($description !== '')
                    <p>{{ $description }}</p>
                @endif
                @if(!empty($post['reading_time']) || !empty($post['updated_at']))
                    <p class="blog-meta">
                        @if(!empty($post['updated_at']))<span>{{ $post['updated_at'] }}</span>@endif
                        @if(!empty($post['reading_time']))<span>{{ $post['reading_time'] }}</span>@endif
                    </p>
                @endif
                @if($slug !== '')
                    <a href="{{ url('/blog/'.$slug) }}" class="btn btn-outline btn-sm">Devamını Oku</a>
                @endif
            </article>
        @endforeach
    </div>
@else
    <div class="blog-posts-list">
        <article class="blog-card">
            <h2>Ciddi Bir İlişkinin Temelleri: Güven ve Saygı</h2>
            <p class="blog-meta"><span>1 Temmuz 2026</span> · <span>4 dk okuma</span></p>
            <p>Sağlıklı ve uzun ömürlü bir evliliğin en önemli iki sütunu güven ve karşılıklı saygıdır. İlk tanışma anından itibaren dürüstlük, açık iletişim ve birbirinizin sınırlarına saygı duymak geleceğe yönelik sağlam bir temel atmanızı sağlar.</p>
        </article>
        <article class="blog-card">
            <h2>İlk Buluşmada Nelere Dikkat Edilmeli?</h2>
            <p class="blog-meta"><span>25 Haziran 2026</span> · <span>5 dk okuma</span></p>
            <p>İnternette tanıştığınız biriyle ilk kez yüz yüze geleceğiniz an heyecan verici olabilir. Güvenliğinizi ön planda tutarak kamuya açık bir yerde buluşun ve yakınlarınıza haber verin.</p>
        </article>
        <article class="blog-card">
            <h2>Doğru Profil Fotoğrafı Nasıl Seçilir?</h2>
            <p class="blog-meta"><span>18 Haziran 2026</span> · <span>3 dk okuma</span></p>
            <p>Profil fotoğrafınız, potansiyel eş adayınızın sizin hakkınızda edineceği ilk izlenimdir. Doğal ışık ve samimi bir gülümseme en iyi sonucu verir.</p>
        </article>
    </div>
@endif

<p class="city-seo-cta-wrap">
    <a href="{{ url('/sss') }}" class="btn btn-outline">Sıkça Sorulan Sorular</a>
    <a href="{{ route('register') }}" class="btn btn-primary">Ücretsiz Kayıt Ol</a>
</p>
@endsection

@push('head')
    <meta name="description" content="Gönül Köprüsü blog — ciddi ilişki, güvenli tanışma ve evlilik odaklı Türkçe rehber yazıları.">
    <link rel="canonical" href="{{ url('/blog') }}">
@endpush

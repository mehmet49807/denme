@extends('layouts.app')

@section('content')
<section class="hero">
    <div class="hero-card">
        <div class="logo-mark">Vibrant Logo Placeholder</div>
        <h1>Gonul Koprusu</h1>
        <p>Modern, sade ve guvenli sosyal tanisma deneyimi. Kullanicilar ayni hesapla web, Android ve iOS uzerinden baglanir; gizli bilgiler yalnizca hesap sahibi ve admin tarafindan gorulur.</p>
        <div class="cta-row">
            <a class="button" href="#register">Kayit Ol</a>
            <a class="button secondary" href="#feed-preview">Akisi Incele</a>
        </div>
    </div>
    <div class="image-grid" aria-label="High quality men and women imagery placeholders">
        <div class="image-placeholder">High-quality women portrait placeholder</div>
        <div class="image-placeholder">High-quality men portrait placeholder</div>
    </div>
</section>

<section class="sections" id="feed-preview">
    <article class="feed-card">
        <div class="feed-header">
            <span>ayse_istanbul</span>
            <span class="location-box"><span>Istanbul</span><span>-</span><span>Kadikoy</span></span>
        </div>
        <div class="post-photo" role="img" aria-label="Post image placeholder"></div>
        <div class="feed-actions">
            <button class="button">Like</button>
            <div class="safety-actions">
                <button class="button secondary">Sikayet</button>
                <button class="button secondary">Engelle</button>
            </div>
        </div>
    </article>

    <article class="profile-card admin-card" id="register">
        <h2>Profil ve Kayit Kurallari</h2>
        <p class="section-copy">Username salt okunur ve kayittan sonra degistirilemez. Ad, soyad, e-posta ve telefon diger kullanicilardan gizlenir; sehir ve ilce yan yana sinirli kutu icinde gosterilir.</p>
        <div class="profile-location"><span>City</span><span>-</span><span>District</span></div>
    </article>

    <section class="testimonials" aria-label="Success story testimonials">
        <div class="testimonial"><strong>Tesekkurler!</strong><p>Burada tanistik ve guvenli iletisim sayesinde ciddi bir adim attik.</p></div>
        <div class="testimonial"><strong>Mutlu cift mesaji</strong><p>Sade profil yapisi ve dogrudan mesajlasma bizim icin cok rahatti.</p></div>
        <div class="testimonial"><strong>Yeni baslangic</strong><p>Gonul Koprusu ekibine tesekkur ederiz.</p></div>
    </section>
</section>
@endsection

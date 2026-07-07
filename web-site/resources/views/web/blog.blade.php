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

<div class="blog-posts-list" style="display: flex; flex-direction: column; gap: 2.5rem; margin-top: 2rem;">
    
    <article class="blog-card" style="border-bottom: 1px solid var(--border-color, #eee); padding-bottom: 2rem;">
        <h2 style="margin-bottom: 0.5rem; color: var(--primary-color, #db2777);"><a href="#" style="text-decoration: none; color: inherit;">Ciddi Bir İlişkinin Temelleri: Güven ve Saygı</a></h2>
        <div class="blog-meta" style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">
            <span>Yayınlanma: 1 Temmuz 2026</span> &bull; <span>Okuma Süresi: 4 dk</span>
        </div>
        <p>
            Sağlıklı ve uzun ömürlü bir evliliğin en önemli iki sütunu güven ve karşılıklı saygıdır. İlk tanışma anından itibaren dürüstlük, açık iletişim ve birbirinizin sınırlarına saygı duymak geleceğe yönelik sağlam bir temel atmanızı sağlar. Bu yazımızda güveni inşa etmenin pratik adımlarını ele alıyoruz.
        </p>
        <a href="#" class="btn btn-outline-primary btn-sm" style="display: inline-block; margin-top: 0.5rem; text-decoration: none; font-weight: 500; font-size: 0.875rem; color: #db2777;">Devamını Oku &rarr;</a>
    </article>

    <article class="blog-card" style="border-bottom: 1px solid var(--border-color, #eee); padding-bottom: 2rem;">
        <h2 style="margin-bottom: 0.5rem; color: var(--primary-color, #db2777);"><a href="#" style="text-decoration: none; color: inherit;">İlk Buluşmada Nelere Dikkat Edilmeli?</a></h2>
        <div class="blog-meta" style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">
            <span>Yayınlanma: 25 Haziran 2026</span> &bull; <span>Okuma Süresi: 5 dk</span>
        </div>
        <p>
            İnternette tanıştığınız biriyle ilk kez yüz yüze geleceğiniz an heyecan verici olabilir. Ancak heyecanınızı kontrol ederken güvenliğinizi de ön planda tutmalısınız. İlk buluşma yeri seçimi, güvenli tanışma kuralları ve konuşulması keyifli konular hakkında bilmeniz gereken ipuçları.
        </p>
        <a href="#" class="btn btn-outline-primary btn-sm" style="display: inline-block; margin-top: 0.5rem; text-decoration: none; font-weight: 500; font-size: 0.875rem; color: #db2777;">Devamını Oku &rarr;</a>
    </article>

    <article class="blog-card" style="border-bottom: 1px solid var(--border-color, #eee); padding-bottom: 2rem;">
        <h2 style="margin-bottom: 0.5rem; color: var(--primary-color, #db2777);"><a href="#" style="text-decoration: none; color: inherit;">Doğru Profil Fotoğrafı Nasıl Seçilir?</a></h2>
        <div class="blog-meta" style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">
            <span>Yayınlanma: 18 Haziran 2026</span> &bull; <span>Okuma Süresi: 3 dk</span>
        </div>
        <p>
            Profil fotoğrafınız, potansiyel eş adayınızın sizin hakkınızda edineceği ilk izlenimdir. Kendinizi en doğal, samimi ve ciddi halinizle yansıtan bir görsel seçmek, aldığınız etkileşim oranını doğrudan artırır. Filtreler yerine doğal ışığı ve gülümsemenizi tercih edin!
        </p>
        <a href="#" class="btn btn-outline-primary btn-sm" style="display: inline-block; margin-top: 0.5rem; text-decoration: none; font-weight: 500; font-size: 0.875rem; color: #db2777;">Devamını Oku &rarr;</a>
    </article>

    <article class="blog-card">
        <h2 style="margin-bottom: 0.5rem; color: var(--primary-color, #db2777);"><a href="#" style="text-decoration: none; color: inherit;">Gönül Köprüsü'nde Güvenli Tanışma Rehberi</a></h2>
        <div class="blog-meta" style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">
            <span>Yayınlanma: 10 Haziran 2026</span> &bull; <span>Okuma Süresi: 6 dk</span>
        </div>
        <p>
            Gönül Köprüsü olarak güvenliğinize her şeyden çok önem veriyoruz. Profil doğrulama adımları, moderasyon ekibimizin 7/24 çalışma sistemi, engelleme ve şikayet mekanizmalarının kullanımı hakkında detaylı bilgilere bu rehberimizden ulaşabilirsiniz.
        </p>
        <a href="#" class="btn btn-outline-primary btn-sm" style="display: inline-block; margin-top: 0.5rem; text-decoration: none; font-weight: 500; font-size: 0.875rem; color: #db2777;">Devamını Oku &rarr;</a>
    </article>

</div>
@endsection

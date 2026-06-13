@extends('layouts.app')
@section('title', 'Gönül Köprüsü · Yeni Başlangıçlar')

@section('content')
{{-- Hero --}}
<section style="background:linear-gradient(135deg,var(--gk-cream) 0%, var(--gk-cream-2) 100%); padding:72px 32px;">
    <div class="gk-container" style="display:grid; grid-template-columns:1.1fr 1fr; gap:40px; align-items:center;">
        <div>
            <h1 style="font-size:2.8rem; line-height:1.15; margin:0 0 16px;">Gönüller arasında<br><span style="color:var(--gk-rose-deep);">sıcak bir köprü.</span></h1>
            <p style="font-size:1.15rem; color:var(--gk-text-soft); max-width:480px;">
                Modern, güvenli ve samimi bir ortamda yeni insanlarla tanışın. Şehrinizdeki gönüllerle bağ kurun.
            </p>
            <div style="margin-top:26px;">
                <a href="{{ route('register') }}" class="gk-btn" style="color:#fff;">Hemen Başla</a>
                <a href="{{ route('login') }}" class="gk-btn gk-btn--ghost">Giriş Yap</a>
            </div>
        </div>
        {{-- High-quality imagery placeholder (men & women) --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
            <div class="gk-card" style="height:180px; background:linear-gradient(135deg,var(--gk-rose),var(--gk-lavender)); display:flex; align-items:flex-end; padding:14px; color:#fff;">Görsel · Kadın profili</div>
            <div class="gk-card" style="height:180px; margin-top:28px; background:linear-gradient(135deg,var(--gk-sage),var(--gk-terracotta)); display:flex; align-items:flex-end; padding:14px; color:#fff;">Görsel · Erkek profili</div>
        </div>
    </div>
</section>

{{-- Success story testimonials --}}
<section class="gk-container">
    <h2 style="text-align:center;">Mutlu Sonlar 💌</h2>
    <p style="text-align:center; color:var(--gk-text-soft);">Burada tanışıp birbirini bulan çiftlerden teşekkür mesajları.</p>
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:18px; margin-top:24px;">
        @for ($i = 1; $i <= 3; $i++)
        <div class="gk-card">
            <div style="display:flex; gap:12px; align-items:center; margin-bottom:12px;">
                <div class="gk-mark" style="width:46px;height:46px;border-radius:50%;"></div>
                <strong>Mutlu Çift #{{ $i }}</strong>
            </div>
            <p style="color:var(--gk-text-soft); font-style:italic;">
                "Gönül Köprüsü sayesinde hayat arkadaşımı buldum. Teşekkür ederiz!" <br>(Başarı hikayesi yer tutucusu)
            </p>
        </div>
        @endfor
    </div>
</section>

{{-- Feature highlights --}}
<section class="gk-container" style="padding-bottom:60px;">
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:18px;">
        <div class="gk-card"><h3>Güvenli Ortam</h3><p style="color:var(--gk-text-soft);">Her profilde şikayet ve engelleme imkanı.</p></div>
        <div class="gk-card"><h3>Gerçek Bağlar</h3><p style="color:var(--gk-text-soft);">Eşleşme bariyeri yok, doğrudan tanışın.</p></div>
        <div class="gk-card"><h3>Gizlilik</h3><p style="color:var(--gk-text-soft);">Gerçek adınız, e-postanız ve telefonunuz gizli kalır.</p></div>
    </div>
</section>
@endsection

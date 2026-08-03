@extends('layouts.content-page')

@section('title', 'KVKK Aydınlatma Metni — Gönül Köprüsü')
@section('legal-active', 'kvkk')
@section('page-eyebrow', 'Gönül Köprüsü')
@section('page-title', 'KVKK Aydınlatma Metni')
@section('page-lead', '6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında verilerinizin işlenmesine ilişkin aydınlatma metni.')

@section('page-content')
<h2>1. Veri Sorumlusu</h2>
@php
    $kvkkSettings = app(\App\Services\SiteSettingsService::class);
    $kvkkCompanyName = $kvkkSettings->get('company_name', '');
    $kvkkContact = $kvkkSettings->get('company_kvkk_contact', '');
    $kvkkEmail = !empty($kvkkContact) ? $kvkkContact : 'destek@gonulkoprusu.com';
@endphp
<p>Gönül Köprüsü platformu kapsamında, kişisel verileriniz 6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) kapsamında işlenmektedir.</p>
@if(!empty($kvkkCompanyName))
<div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:.5rem;padding:1rem;margin:1rem 0;">
    <p style="margin:0;"><strong>Veri Sorumlusu:</strong> {{ $kvkkCompanyName }}</p>
    @if(!empty($kvkkSettings->get('company_address')))
    <p style="margin:.25rem 0 0;"><strong>Adres:</strong> {{ $kvkkSettings->get('company_address') }}</p>
    @endif
</div>
@endif
<p>Başvurularınızı <a href="mailto:{{ $kvkkEmail }}">{{ $kvkkEmail }}</a> adresine iletebilirsiniz.</p>

<h2>2. İşlenen Kişisel Veri Kategorileri</h2>
<ul>
    <li><strong>Kimlik bilgileri:</strong> Ad, soyad, doğum tarihi, cinsiyet</li>
    <li><strong>İletişim bilgileri:</strong> E-posta adresi, kullanıcı adı</li>
    <li><strong>Konum bilgileri:</strong> Şehir, ilçe (profil ayarlarından girilen)</li>
    <li><strong>Görsel veriler:</strong> Profil fotoğrafları, galeri görselleri</li>
    <li><strong>İşlem güvenliği:</strong> IP adresi, giriş zaman damgaları, cihaz bilgisi</li>
    <li><strong>Platform içi iletişim:</strong> Mesajlaşma içeriği</li>
</ul>

<h2>3. İşleme Amaçları ve Hukuki Sebepler</h2>
<ul>
    <li><strong>KVKK md. 5/2-a:</strong> Hukuka uygun olarak alenileştirilmiş kişisel verilerin işlenmesi</li>
    <li><strong>KVKK md. 5/2-c:</strong> İlgili kişinin temel hak ve özgürlüklerine zarar vermemek kaydıyla veri sorumlusunun meşru menfaatleri için zorunlu olması</li>
    <li><strong>KVKK md. 5/2-f:</strong> İlgili kişinin kendisi tarafından alenileştirilmiş verilerin işlenmesi</li>
    <li><strong>Sözleşme ilişkisi:</strong> Üyelik sözleşmesinin ifası için gerekli olması</li>
</ul>

<h2>4. Aktarılan Taraflar</h2>
<p>Kişisel verileriniz aşağıdaki taraflara aktarılabilir:</p>
<ul>
    <li>Bulut sunucu barındırma sağlayıcısı (HTTPS/TLS ile güvenli bağlantı)</li>
    <li>Google OAuth giriş servisi (Google'ın gizlilik politikası geçerlidir)</li>
    <li>Push notification servisleri (Firebase Cloud Messaging)</li>
</ul>
<p>Yurtdışına veri aktarımı KVKK md. 9 kapsamında uygun güvence altında gerçekleştirilir.</p>

<h2>5. Saklama Süreleri</h2>
<p>Kişisel verileriniz, işleme amacının gerektirdiği süre boyunca ve ilgili mevzuatta öngörülen zamanaşımı süreleri kadar saklanır:</p>
<ul>
    <li>Hesap verileri: Üyelik süresince + hesap silindikten sonra 5 yıl (Vergi Usul Kanunu)</li>
    <li>Log kayıtları: 5651 sayılı Kanun gereği 6 ay</li>
    <li>Mesajlaşma: Hizmet süresince + hesap kapatma sonrası 90 gün</li>
</ul>

<h2>6. Teknik Tedbirler</h2>
<ul>
    <li>256-bit SSL/TLS şifreleme</li>
    <li>Şifrelerin hashlenmesi (tek yönlü)</li>
    <li>Erişim yetkilendirme kontrolü</li>
    <li>Düzenli güvenlik güncellemeleri</li>
</ul>

<h2>7. İlgili Kişinin Hakları (KVKK md. 11)</h2>
<p>Kişisel verileri işlenen ilgili kişi, aşağıdaki haklarına sahipdir:</p>
<ul>
    <li>Kişisel verilerinin işlenip işlenmediğini öğrenme</li>
    <li>İşlenmişse buna ilişkin bilgi talep etme</li>
    <li>İşlenme amacını ve amacına uygun kullanılıp kullanılmadığını öğrenme</li>
    <li>Eksik/yanlış işlenmişse düzeltilmesini isteme</li>
    <li>Silinmesini veya yok edilmesini isteme</li>
    <li>Aktarıldığı üçüncü kişileri öğrenme</li>
    <li>İşlenen verilerin münhasıran otomatik sistemler vasıtasıyla analiz edilmesi suretiyle aleyhinize bir sonucun ortaya çıkmasına itiraz etme</li>
</ul>
<p>Başvurularınız <a href="mailto:destek@gonulkoprusu.com">destek@gonulkoprusu.com</a> adresine iletilebilir.</p>

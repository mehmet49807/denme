@extends('layouts.content-page')

@section('title', 'Gizlilik Sözleşmesi — Gönül Köprüsü')
@section('legal-active', 'privacy')
@section('page-eyebrow', 'Gönül Köprüsü')
@section('page-title', 'Gizlilik Sözleşmesi')
@section('page-lead', 'Kişisel verilerinizin nasıl işlendiği ve korunduğu hakkında şeffaf bilgiler.')

@section('page-content')
<h2>1. Veri Sorumlusu</h2>
@php
    $privSettings = app(App\Services\SiteSettingsService::class);
    $privCompanyName = $privSettings->get('company_name', '');
    $privContact = $privSettings->get('company_kvkk_contact', '');
    $privEmail = !empty($privContact) ? $privContact : 'destek@gonulkoprusu.com';
@endphp
<p>Bu Gizlilik Sözleşmesi, Gönül Köprüsü platformu kapsamında kişisel verilerinizin işlenmesine ilişkin olarak bilgilendirme amaçlıdır. Veri sorumlusu sıfatıyla, kişisel verileriniz KVKK (6698 sayılı Kişisel Verilerin Korunması Kanunu) kapsamında işlenmektedir.</p>
@if(!empty($privCompanyName))
<div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:.5rem;padding:1rem;margin:1rem 0;">
    <p style="margin:0;"><strong>Veri Sorumlusu:</strong> {{ $privCompanyName }}</p>
    @if(!empty($privSettings->get('company_address')))
    <p style="margin:.25rem 0 0;"><strong>Adres:</strong> {{ $privSettings->get('company_address') }}</p>
    @endif
    @if(!empty($privSettings->get('company_tax_number')))
    <p style="margin:.25rem 0 0;"><strong>VKN:</strong> {{ $privSettings->get('company_tax_number') }}</p>
    @endif
</div>
@endif
<p><strong>İletişim:</strong> destek@gonulkoprusu.com</p>

<h2>2. İşlenen Kişisel Veriler</h2>
<ul>
    <li><strong>Hesap bilgileri:</strong> Ad, soyad, e-posta, kullanıcı adı, şifre (hashlenmiş), doğum tarihi, cinsiyet, şehir/ilçe konumu, profil fotoğrafı</li>
    <li><strong>Mesajlaşma verileri:</strong> Platform içi mesajlar, sohbet geçmişi</li>
    <li><strong>Kullanım verileri:</strong> Giriş zamanları, IP adresi, cihaz bilgisi, tarayıcı türü</li>
    <li><strong>Çerez verileri:</strong> Oturum çerezleri, tercih çerezleri, analitik çerezler</li>
</ul>

<h2>3. Veri İşleme Amaçları</h2>
<ul>
    <li>Üyelik hesabının oluşturulması ve sürdürülmesi</li>
    <li>Güvenli tanışma ortamının sağlanması ve moderasyon</li>
    <li>Profil eşleştirme ve içerik gösterimi</li>
    <li>Yasal yükümlülüklerin yerine getirilmesi</li>
    <li>Platform güvenliğinin sağlanması (KVKK md. 5/2-f)</li>
</ul>

<h2>4. Veri Aktarımı</h2>
<p>Kişisel verileriniz, platformun barındırma hizmeti sağlayan bulut sunucu sağlayıcılarına (AB/Türkiye veri merkezlerinde) ve bildirim gönderimi için anlık ileti servislere aktarılabilir. Yurtdışına veri aktarımı KVKK md. 9 kapsamında yürütülür.</p>

<h2>5. Saklama Süreleri</h2>
<ul>
    <li><strong>Hesap verileri:</strong> Üyelik süresince; hesap silindikten sonra KVKK ve Vergi Usul Kanunu kapsamında yasal saklama süreleri boyunca (en fazla 5 yıl) saklanır.</li>
    <li><strong>Mesajlar:</strong> Hizmetin sunulması için gerekli süre boyunca; hesap kapatıldıktan sonra 90 gün içinde silinir.</li>
    <li><strong>Log kayıtları:</strong> 5651 sayılı Kanun gereği 6 ay saklanır.</li>
    <li><strong>Çerez verileri:</strong> Oturum çerezleri tarayıcı kapanınca, kalıcı çerezler en fazla 12 ay saklanır.</li>
</ul>

<h2>6. KVKK Haklarınız</h2>
<p>KVKK md. 11 kapsamında kişisel verilerinize ilişkin:</p>
<ul>
    <li>İşlenip işlenmediğini öğrenme</li>
    <li>İşlenmişse buna ilişkin bilgi talep etme</li>
    <li>İşlenme amacını ve bunların amacına uygun kullanılıp kullanılmadığını öğrenme</li>
    <li>Eksik veya yanlış işlenmişse düzeltilmesini talep etme</li>
    <li>Silinmesini veya yok edilmesini talep etme</li>
    <li>Aktarıldığı üçüncü kişileri öğrenme</li>
</ul>
<p>Bu haklarınızı kullanmak için <a href="mailto:destek@gonulkoprusu.com">destek@gonulkoprusu.com</a> adresine başvurabilirsiniz.</p>

<h2>7. Çerez Politikası</h2>
<p>Platformumuz şu çerez türlerini kullanır:</p>
<ul>
    <li><strong>Zorunlu çerezler:</strong> Giriş yapma ve güvenlik için gereklidir, devre dışı bırakılamaz.</li>
    <li><strong>Analitik çerezler:</strong> Kullanım istatistikleri için kullanılır, anonim veri içerir.</li>
    <li><strong>İşlevsel çerezler:</strong> Dil ve tema tercihlerini hatırlar.</li>
</ul>
<p>Pazarlama çerezleri yalnızca açık rızanız ile kullanılır.</p>

<h2>8. Üçüncü Taraf Hizmetleri</h2>
<p>Platform şu üçüncü taraf hizmetlerini kullanır:</p>
<ul>
    <li>Bulut barındırma (HTTPS tabanlı güvenli sunucu)</li>
    <li>Google ile giriş servisi</li>
    <li>Anlık bildirim gönderimi (FCM)</li>
</ul>
<p>Bu hizmetler kendi gizlilik politikalarına tabidir.</p>

<h2>9. Değişiklikler</h2>
<p>Bu sözleşme zaman zaman güncellenebilir. Önemli değişiklikler e-posta ile bildirilir. Son güncelleme tarihi sayfanın üst kısmında belirtilir.</p>

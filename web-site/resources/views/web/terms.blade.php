@extends('layouts.content-page')

@section('title', 'Kullanım Koşulları — Gönül Köprüsü')
@section('legal-active', 'terms')
@section('page-eyebrow', 'Gönül Köprüsü')
@section('page-title', 'Kullanım Koşulları')
@section('page-lead', 'Gönül Köprüsü platformunu kullanırken uymanız gereken kurallar ve koşullar.')

@section('page-content')
<h2>1. Hizmet Tanımı</h2>
<p>Gönül Köprüsü, ciddi ilişki ve evlilik arayan yetişkinler için bir tanışma platformudur. Platform, üyeler arasında iletişim sağlar; ancak eşleştirme garantisi vermez.</p>

<h2>2. Üyelik Şartları</h2>
<ul>
    <li>Üye olabilmek için en az 18 yaşında olmanız gerekmektedir.</li>
    <li>Gerçek ve doğru bilgi vermek zorunludur. Sahte profil oluşturmak yasaktır.</li>
    <li>Hesap başına bir e-posta adresi kullanılabilir.</li>
    <li>Kimlik doğrulama talep edilebilir.</li>
</ul>

<h2>3. Üyelik Ücretleri</h2>
<p>Temel üyelik ücretsizdir. Kadın üyelerde mesajlaşma ücretsizdir. Erkek üyeler için premium paketler:</p>
<ul>
    <li><strong>Pro:</strong> Mesajlaşma ve ek profil özellikleri</li>
    <li><strong>Gold:</strong> Pro özellikler + kimler baktı + gelişmiş filtreler</li>
    <li><strong>Platinum:</strong> Gold özellikler + eşleşme bildirimleri + öncelikli görünürlük</li>
</ul>
<p>Detaylı fiyatlandırma için <a href="{{ route('premium') }}">premium sayfamızı</a> ziyaret edebilirsiniz.</p>

<h2>4. Cayma Hakkı ve İade Politikası</h2>
<p>6502 sayılı Tüketicinin Korunması Hakkında Kanun kapsamında:</p>
<ul>
    <li><strong>Cayma hakkı:</strong> Mesafeli satış sözleşmesi kapsamında, premium üyelik satın alımından itibaren 14 gün içinde cayma hakkınız bulunmaktadır. Ancak, dijital içeriklerin teslimi yapılmışsa cayma hakkı kullanılamaz (6502 md. 15/1-f).</li>
    <li><strong>İade:</strong> Cayma süresi içinde talebiniz halinde ödeme iade edilir. İade, ödemenin alındığı yöntemle 14 iş günü içinde yapılır.</li>
    <li><strong>İptal:</strong> Premium üyeliğinizi istediğiniz zaman iptal edebilirsiniz. İptal, bir sonraki yenileme döneminde geçerli olur.</li>
    <li><strong>Mesafeli Satış Sözleşmesi:</strong> Premium üyelik satın alımında mesafeli satış sözleşmesi kabul edilmiş sayilir.</li>
</ul>

<h2>5. Yasak Davranışlar</h2>
<ul>
    <li>Taciz, tehdit, hakaret içeren mesajlar</li>
    <li>Sahte profil veya başkasının kimliğini kullanma</li>
    <li>Para isteme veya dolandırıcılık girişimi</li>
    <li>Reşit olmayan kişileri hedef alma</li>
    <li>Spam veya reklam amaçlı mesaj gönderme</li>
    <li>Otomatik bot veya script kullanma</li>
</ul>
<p>Bu kuralların ihlali hesap kapatılması ile sonuçlanabilir.</p>

<h2>6. İçerik ve Telif Hakkı</h2>
<p>Üyeler tarafından yüklenen içerikten üye sorumludur. Telif hakkı ihlali tespit edilirse içerik kaldırılır. İçerik moderasyon kurallarına tabidir.</p>

<h2>7. Sorumluluk Sınırı</h2>
<p>Gönül Köprüsü, üyeler arasındaki gerçek hayattaki buluşmalardan sorumlu değildir. Platform hizmetlerini "olduğu gibi" sunar. Hizmet kesintilerinden dolayı tazminat talep edilemez.</p>

<h2>8. Değişiklikler</h2>
<p>Bu koşullar zaman zaman güncellenebilir. Devam eden kullanım, güncellenen koşulları kabul ettiğiniz anlamına gelir.</p>

<h2>9. Uyuşmazlık Çözümü</h2>
<p>Uyuşmazlık durumunda önce <a href="mailto:destek@gonulkoprusu.com">destek@gonulkoprusu.com</a> adresine başvurulmalıdır. Çözülemeyen uyuşmazlıklar Tüketici Hakem Heyeti veya ilgili mahkemelerde çözümlenir.</p>

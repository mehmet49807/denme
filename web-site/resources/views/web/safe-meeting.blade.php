@extends('layouts.content-page')

@section('title', 'Güvenli Tanışma — Gönül Köprüsü')
@section('legal-active', 'safe-meeting')
@section('page-eyebrow', 'Gönül Köprüsü')
@section('page-title', 'Güvenli Tanışma Rehberi')
@section('page-lead', 'İlk mesajdan ilk buluşmaya kadar güvenli tanışma için pratik tavsiyeler.')

@section('page-content')
<h2>1. İlk Mesajlaşma</h2>
<ul>
    <li>İlk mesajdan kişisel telefon, adres veya finansal bilgi paylaşmayın.</li>
    <li>İletişimi platform içinde tutun; platform dışına çıkmak güvenliği azaltır.</li>
    <li>Karşı tarafın mesaj tutarlılığına dikkat edin — çelişkili bilgiler uyarı işaretidir.</li>
    <li>"Yardım", "para", "borç" gibi kelimelere karşı temkinli olun.</li>
</ul>

<h2>2. Buluşma Öncesi</h2>
<ul>
    <li>Profil fotoğrafının gerçek olduğundan emin olun — video görüşme önerebilirsiniz.</li>
    <li>Buluşma yerini birlikte seçin; halka açık, kalabalık bir yer tercih edin.</li>
    <li>Yakınlarınıza buluşma yerini ve zamanı haber verin.</li>
    <li>Kendi ulaşımınızı kendiniz arrange edin.</li>
</ul>

<h2>3. İlk Buluşma</h2>
<ul>
    <li>Kafe, AVM veya restoran gibi halka açık yerlerde buluşun.</li>
    <li>Buluşmaya kimseyle gelmeyin ve kimseyi getirmeyin.</li>
    <li>İçeceğinizi göz önünde tutun; yemek sırasında masadan kalktığınızda içmeyin.</li>
    <li>Uçtuğunuz veya araç kullandığınız durumda alkol tüketmeyin.</li>
    <li>Buluşma iyi gitmezse, kibarca ayrılabilirsiniz; bunun için özür dilemeniz gerekmez.</li>
</ul>

<h2>4. Reddedilmesi Gereken Durumlar</h2>
<ul>
    <li>Para veya finansal yardım talebi</li>
    <li>Erken platform dışı iletişim (telefon, WhatsApp) baskısı</li>
    <li>Profil fotoğrafı reddetme veya video görüşme önerisini sürekli erteleme</li>
    <li>İlk buluşmada ev veya izole yer önerisi</li>
    <li>Aşırı hızlı yakınlaşma veya kontrolcü davranış</li>
</ul>

<h2>5. Acil Durumlar</h2>
<p>Güvenliğinizden endişe duyuyorsanız:</p>
<ul>
    <li><strong>112</strong> — Acil Çağrı Merkezi</li>
    <li><strong>183</strong> — Sosyal Destek Hattı</li>
    <li>Kişiyi engellemek için profilde "Engelle" butonunu kullanın</li>
    <li>Şikayet için <a href="{{ route('complaints') }}">Şikayet ve Engelleme</a> sayfasına bakın</li>
    <li>Destek için <a href="mailto:destek@gonulkoprusu.com">destek@gonulkoprusu.com</a></li>
</ul>

{{-- Legal sayfaların alt bağlantıları --}}
@php
    $legalFooterLinks = [
        ['label' => 'Gizlilik Sözleşmesi', 'route' => 'privacy'],
        ['label' => 'KVKK Aydınlatma', 'route' => 'kvkk'],
        ['label' => 'Kullanım Koşulları', 'route' => 'terms'],
        ['label' => 'Şikayet & Engelleme', 'route' => 'complaints'],
        ['label' => 'Güvenli Tanışma', 'route' => 'safe-meeting'],
    ];
@endphp
<nav class="legal-footer-nav" aria-label="Yasal bağlantılar">
    <ul class="legal-footer-links">
        @foreach($legalFooterLinks as $link)
            <li><a href="{{ route($link['route']) }}">{{ $link['label'] }}</a></li>
        @endforeach
        <li><a href="mailto:destek@gonulkoprusu.com">destek@gonulkoprusu.com</a></li>
        <li><a href="{{ route('home') }}">← Ana Sayfaya Dön</a></li>
    </ul>
</nav>
<p class="legal-footer-copy">© {{ date('Y') }} Gönül Köprüsü. Tüm hakları saklıdır.</p>

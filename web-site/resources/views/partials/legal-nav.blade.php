<nav class="content-page-nav glass-card" aria-label="Yasal ve bilgi sayfaları">
    <p class="content-page-nav-title">Bilgi Merkezi</p>
    <ul>
        <li><a href="{{ route('about') }}" class="{{ ($active ?? '') === 'about' ? 'active' : '' }}">@include('partials.theme-icon', ['icon' => 'heart']) Hakkımızda</a></li>
        <li><a href="{{ route('safe-meeting') }}" class="{{ ($active ?? '') === 'safe-meeting' ? 'active' : '' }}">@include('partials.theme-icon', ['icon' => 'shield']) Güvenli Tanışma</a></li>
        <li><a href="{{ url('/blog') }}" class="{{ ($active ?? '') === 'blog' ? 'active' : '' }}">@include('partials.theme-icon', ['icon' => 'post']) Blog</a></li>
        <li><a href="{{ url('/sss') }}" class="{{ ($active ?? '') === 'sss' ? 'active' : '' }}">@include('partials.theme-icon', ['icon' => 'messages']) SSS</a></li>
        <li><a href="{{ route('complaints') }}" class="{{ ($active ?? '') === 'complaints' ? 'active' : '' }}">@include('partials.theme-icon', ['icon' => 'messages']) Şikayet & Engelleme</a></li>
        <li><a href="{{ route('privacy') }}" class="{{ ($active ?? '') === 'privacy' ? 'active' : '' }}">@include('partials.theme-icon', ['icon' => 'eye']) Gizlilik Sözleşmesi</a></li>
        <li><a href="{{ route('kvkk') }}" class="{{ ($active ?? '') === 'kvkk' ? 'active' : '' }}">@include('partials.theme-icon', ['icon' => 'shield']) KVKK Aydınlatma</a></li>
        <li><a href="{{ route('terms') }}" class="{{ ($active ?? '') === 'terms' ? 'active' : '' }}">@include('partials.theme-icon', ['icon' => 'star']) Kullanım Koşulları</a></li>
    </ul>
</nav>

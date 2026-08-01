<nav class="content-page-nav glass-card content-page-nav--vivid" aria-label="Yasal ve bilgi sayfaları">
    <header class="content-page-nav-head">
        <span class="content-page-nav-mark" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'sparkles'])</span>
        <div>
            <p class="content-page-nav-title">Bilgi Merkezi</p>
            <p class="content-page-nav-sub">Rehber · güven · yasal</p>
        </div>
    </header>
    <ul>
        <li>
            <a href="{{ route('about') }}" class="{{ ($active ?? '') === 'about' ? 'active' : '' }}">
                <span class="content-page-nav-icon content-page-nav-icon--rose" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'heart'])</span>
                <span>Hakkımızda</span>
            </a>
        </li>
        <li>
            <a href="{{ route('safe-meeting') }}" class="{{ ($active ?? '') === 'safe-meeting' ? 'active' : '' }}">
                <span class="content-page-nav-icon content-page-nav-icon--teal" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'shield'])</span>
                <span>Güvenli Tanışma</span>
            </a>
        </li>
        <li>
            <a href="{{ url('/blog') }}" class="{{ ($active ?? '') === 'blog' ? 'active' : '' }}">
                <span class="content-page-nav-icon content-page-nav-icon--amber" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'post'])</span>
                <span>Blog</span>
            </a>
        </li>
        <li>
            <a href="{{ url('/sss') }}" class="{{ ($active ?? '') === 'sss' ? 'active' : '' }}">
                <span class="content-page-nav-icon content-page-nav-icon--sky" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'messages'])</span>
                <span>SSS</span>
            </a>
        </li>
        @if(Route::has('stories'))
        <li>
            <a href="{{ route('stories') }}" class="{{ ($active ?? '') === 'stories' ? 'active' : '' }}">
                <span class="content-page-nav-icon content-page-nav-icon--coral" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'sparkles'])</span>
                <span>Başarı Hikâyeleri</span>
            </a>
        </li>
        @endif
        <li>
            <a href="{{ route('support') }}" class="{{ ($active ?? '') === 'support' ? 'active' : '' }}">
                <span class="content-page-nav-icon content-page-nav-icon--orange" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'support'])</span>
                <span>7/24 Destek</span>
            </a>
        </li>
        <li>
            <a href="{{ route('complaints') }}" class="{{ ($active ?? '') === 'complaints' ? 'active' : '' }}">
                <span class="content-page-nav-icon content-page-nav-icon--violet" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'bell'])</span>
                <span>Şikayet & Engelleme</span>
            </a>
        </li>
        <li>
            <a href="{{ route('privacy') }}" class="{{ ($active ?? '') === 'privacy' ? 'active' : '' }}">
                <span class="content-page-nav-icon content-page-nav-icon--indigo" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'eye'])</span>
                <span>Gizlilik Sözleşmesi</span>
            </a>
        </li>
        <li>
            <a href="{{ route('kvkk') }}" class="{{ ($active ?? '') === 'kvkk' ? 'active' : '' }}">
                <span class="content-page-nav-icon content-page-nav-icon--emerald" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'lock'])</span>
                <span>KVKK Aydınlatma</span>
            </a>
        </li>
        <li>
            <a href="{{ route('terms') }}" class="{{ ($active ?? '') === 'terms' ? 'active' : '' }}">
                <span class="content-page-nav-icon content-page-nav-icon--gold" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'star'])</span>
                <span>Kullanım Koşulları</span>
            </a>
        </li>
    </ul>
</nav>

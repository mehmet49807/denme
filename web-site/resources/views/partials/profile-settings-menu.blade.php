<nav class="profile-settings-menu" aria-label="Ayarlar menüsü">
    <a href="{{ route('settings.profile') }}" class="profile-settings-menu-item profile-settings-menu-item--link">
        <span class="profile-settings-menu-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </span>
        <span class="profile-settings-menu-text">
            <strong>Profil Bilgilerini Düzenle</strong>
            <small>Ad, e-posta, telefon ve konum</small>
        </span>
        <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
    </a>

    <a href="{{ route('settings.hobbies') }}" class="profile-settings-menu-item profile-settings-menu-item--link">
        <span class="profile-settings-menu-icon" aria-hidden="true">✨</span>
        <span class="profile-settings-menu-text">
            <strong>Hobiler</strong>
            <small>İlgi alanlarınızı güncelleyin</small>
        </span>
        <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
    </a>

    <a href="{{ route('settings.language') }}" class="profile-settings-menu-item profile-settings-menu-item--link">
        <span class="profile-settings-menu-icon" aria-hidden="true">🌐</span>
        <span class="profile-settings-menu-text">
            <strong>Dil Seç</strong>
            <small>Profil ve uygulama dili</small>
        </span>
        <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
    </a>

    <a href="{{ route('settings.password') }}" class="profile-settings-menu-item profile-settings-menu-item--link">
        <span class="profile-settings-menu-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </span>
        <span class="profile-settings-menu-text">
            <strong>Şifre Değiştir</strong>
            <small>Hesap güvenliğiniz</small>
        </span>
        <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
    </a>

    <a href="{{ route('referral') }}" class="profile-settings-menu-item profile-settings-menu-item--link">
        <span class="profile-settings-menu-icon" aria-hidden="true">🎁</span>
        <span class="profile-settings-menu-text">
            <strong>{{ $user->gender === 'female' ? 'Arkadaşını Davet Et' : 'Davet Et' }}</strong>
            <small>Arkadaşlarınızı platforma davet edin</small>
        </span>
        <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
    </a>

    <a href="{{ route('complaints') }}" class="profile-settings-menu-item profile-settings-menu-item--link">
        <span class="profile-settings-menu-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </span>
        <span class="profile-settings-menu-text">
            <strong>Şikayet ve Engelleme</strong>
            <small>Güvenlik politikaları</small>
        </span>
        <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
    </a>

    <a href="{{ route('support') }}" class="profile-settings-menu-item profile-settings-menu-item--link">
        <span class="profile-settings-menu-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
        </span>
        <span class="profile-settings-menu-text">
            <strong>Şikayet ve Dilekçe</strong>
            <small>7/24 destek formu</small>
        </span>
        <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
    </a>

    <form method="POST" action="{{ route('logout') }}" class="profile-settings-logout-form">
        @csrf
        <button type="submit" class="profile-settings-menu-item profile-settings-menu-item--danger">
            <span class="profile-settings-menu-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </span>
            <span class="profile-settings-menu-text">
                <strong>Çıkış</strong>
                <small>Hesabınızdan güvenle çıkın</small>
            </span>
        </button>
    </form>
</nav>

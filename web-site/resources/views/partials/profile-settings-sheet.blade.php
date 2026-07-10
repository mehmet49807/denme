@php
    $locales = [
        'tr' => ['label' => 'Türkçe', 'flag' => 'tr', 'emoji' => '🇹🇷'],
        'en' => ['label' => 'English', 'flag' => 'gb', 'emoji' => '🇬🇧'],
        'de' => ['label' => 'Deutsch', 'flag' => 'de', 'emoji' => '🇩🇪'],
        'fr' => ['label' => 'Français', 'flag' => 'fr', 'emoji' => '🇫🇷'],
        'hi' => ['label' => 'हिन्दी', 'flag' => 'in', 'emoji' => '🇮🇳'],
    ];
    $currentLocale = app()->getLocale();
    $hasPassword = ! empty($user->password);
    $initialPanel = old('settings_panel', session('settings_panel', 'menu'));
    if ($errors->any() && $initialPanel === 'menu') {
        if ($errors->has('current_password') || $errors->has('password')) {
            $initialPanel = 'password';
        } elseif ($errors->has('hobbies') || $errors->has('hobbies.*')) {
            $initialPanel = 'hobbies';
        } elseif ($errors->has('locale')) {
            $initialPanel = 'language';
        } else {
            $initialPanel = 'edit';
        }
    }
@endphp

<div
    class="profile-settings-sheet"
    id="profileSettingsSheet"
    hidden
    role="dialog"
    aria-modal="true"
    aria-labelledby="profileSettingsTitle"
    data-initial-panel="{{ $initialPanel }}"
>
    <button type="button" class="profile-settings-sheet-backdrop" data-close-settings aria-label="Kapat"></button>

    <aside class="profile-settings-sheet-panel">
        <header class="profile-settings-sheet-header">
            <button type="button" class="profile-settings-sheet-back" data-settings-back hidden aria-label="Geri">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <h2 class="profile-settings-sheet-title" id="profileSettingsTitle">Ayarlar</h2>
            <button type="button" class="profile-settings-sheet-close" data-close-settings aria-label="Kapat">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </header>

        <div class="profile-settings-sheet-body">
            <nav class="profile-settings-menu" data-settings-panel="menu">
                <button type="button" class="profile-settings-menu-item" data-open-settings-panel="edit">
                    <span class="profile-settings-menu-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </span>
                    <span class="profile-settings-menu-text">
                        <strong>Profil Bilgilerini Düzenle</strong>
                        <small>Ad, e-posta, telefon ve konum</small>
                    </span>
                    <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
                </button>

                <button type="button" class="profile-settings-menu-item" data-open-settings-panel="hobbies">
                    <span class="profile-settings-menu-icon" aria-hidden="true">✨</span>
                    <span class="profile-settings-menu-text">
                        <strong>Hobiler</strong>
                        <small>İlgi alanlarınızı güncelleyin</small>
                    </span>
                    <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
                </button>

                <button type="button" class="profile-settings-menu-item" data-open-settings-panel="language">
                    <span class="profile-settings-menu-icon" aria-hidden="true">🌐</span>
                    <span class="profile-settings-menu-text">
                        <strong>Dil Seç</strong>
                        <small>Profil ve uygulama dili</small>
                    </span>
                    <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
                </button>

                <button type="button" class="profile-settings-menu-item" data-open-settings-panel="password">
                    <span class="profile-settings-menu-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <span class="profile-settings-menu-text">
                        <strong>Şifre Değiştir</strong>
                        <small>Hesap güvenliğiniz</small>
                    </span>
                    <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
                </button>

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

            <section class="profile-settings-panel" data-settings-panel="edit" hidden>
                <form method="POST" action="{{ route('profile.update') }}" class="profile-settings-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="settings_panel" value="edit">

                    <div class="form-group">
                        <label>Kullanıcı Adı (değiştirilemez)</label>
                        <input type="text" value="{{ $user->username }}" readonly>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Ad</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Soyad</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>E-posta</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email') <small class="form-error">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label>Telefon</label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}">
                    </div>

                    <div class="form-group">
                        <label>Ülke, Şehir & İlçe</label>
                        @include('partials.location-fields', [
                            'country' => $user->country ?? 'Türkiye',
                            'city' => $user->city,
                            'district' => $user->district,
                        ])
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">Kaydet</button>
                </form>
            </section>

            <section class="profile-settings-panel" data-settings-panel="hobbies" hidden>
                <form method="POST" action="{{ route('profile.update') }}" class="profile-settings-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="settings_panel" value="hobbies">

                    @include('partials.hobbies-picker', ['selectedHobbies' => old('hobbies', $user->hobbies ?? [])])

                    <button type="submit" class="btn btn-primary btn-full">Hobileri Kaydet</button>
                </form>
            </section>

            <section class="profile-settings-panel" data-settings-panel="language" hidden>
                <p class="profile-settings-panel-lead">Profil ve uygulama dili</p>
                <ul class="profile-settings-language-list">
                    @foreach($locales as $code => $meta)
                        <li>
                            <a
                                href="{{ route('profile.locale', $code) }}"
                                class="profile-settings-language-item {{ $currentLocale === $code ? 'profile-settings-language-item--active' : '' }}"
                                @if($currentLocale === $code) aria-current="true" @endif
                            >
                                <span class="profile-settings-language-flag" aria-hidden="true">{{ $meta['emoji'] }}</span>
                                <span>{{ $meta['label'] }}</span>
                                @if($currentLocale === $code)
                                    <span class="profile-settings-language-check" aria-hidden="true">✓</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="profile-settings-panel" data-settings-panel="password" hidden>
                @if($user->google_id && ! $hasPassword)
                    <p class="profile-settings-panel-lead">Google ile giriş yaptınız. Şifre oluşturmak için e-posta adresinize sıfırlama bağlantısı gönderebilirsiniz.</p>
                    <a href="{{ route('password.request') }}" class="btn btn-outline btn-full">Şifre Belirle</a>
                @else
                    <form method="POST" action="{{ route('profile.password') }}" class="profile-settings-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="settings_panel" value="password">

                        <div class="form-group">
                            <label for="current_password">Mevcut Şifre</label>
                            <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                            @error('current_password') <small class="form-error">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Yeni Şifre</label>
                            <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
                            @error('password') <small class="form-error">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Yeni Şifre (Tekrar)</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn-primary btn-full">Şifreyi Güncelle</button>
                    </form>
                @endif
            </section>
        </div>
    </aside>
</div>

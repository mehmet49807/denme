@php
    $locales = [
        'tr' => ['label' => 'Türkçe', 'emoji' => '🇹🇷'],
        'en' => ['label' => 'English', 'emoji' => '🇬🇧'],
        'de' => ['label' => 'Deutsch', 'emoji' => '🇩🇪'],
        'fr' => ['label' => 'Français', 'emoji' => '🇫🇷'],
        'hi' => ['label' => 'हिन्दी', 'emoji' => '🇮🇳'],
    ];
    $currentLocale = app()->getLocale();
    $hasPassword = ! empty($user->password);
    $themePreference = old('theme_preference', $user->theme_preference ?: 'system');
    if (! in_array($themePreference, ['light', 'dark', 'system'], true)) {
        $themePreference = 'system';
    }
    $readReceiptsEnabled = old('read_receipts_enabled', $user->read_receipts_enabled !== false);
    $quietHoursEnabled = (bool) old('quiet_hours_enabled', $user->quiet_hours_enabled);
    $quietHoursStart = old('quiet_hours_start', $user->quiet_hours_start ? substr((string) $user->quiet_hours_start, 0, 5) : '22:00');
    $quietHoursEnd = old('quiet_hours_end', $user->quiet_hours_end ? substr((string) $user->quiet_hours_end, 0, 5) : '08:00');
    $initialPanel = old('settings_panel', session('settings_panel', 'menu'));
    if ($errors->any() && $initialPanel === 'menu') {
        if ($errors->has('current_password') || $errors->has('password')) {
            $initialPanel = 'password';
        } elseif ($errors->has('hobbies') || $errors->has('hobbies.*')) {
            $initialPanel = 'hobbies';
        } elseif ($errors->has('bio')) {
            $initialPanel = 'bio';
        } elseif ($errors->has('relationship_status')) {
            $initialPanel = 'relationship';
        } elseif ($errors->has('locale')) {
            $initialPanel = 'language';
        } elseif ($errors->has('theme_preference')) {
            $initialPanel = 'appearance';
        } elseif ($errors->has('read_receipts_enabled') || $errors->has('quiet_hours_enabled') || $errors->has('quiet_hours_start') || $errors->has('quiet_hours_end')) {
            $initialPanel = 'privacy';
        } else {
            $initialPanel = 'edit';
        }
    }
@endphp

<div class="profile-settings-sheet-stage" data-settings-panel="menu" @if($initialPanel !== 'menu') hidden @endif>
    <nav class="profile-settings-menu" aria-label="Ayarlar menüsü">
        <button type="button" class="profile-settings-menu-item" data-open-settings-panel="edit">
            <span class="profile-settings-menu-icon profile-settings-menu-icon--edit" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </span>
            <span class="profile-settings-menu-text">
                <strong>Profil Bilgilerini Düzenle</strong>
                <small>Ad, e-posta, telefon, konum ve doğum tarihi</small>
            </span>
            <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
        </button>

        <button type="button" class="profile-settings-menu-item" data-open-settings-panel="bio">
            <span class="profile-settings-menu-icon profile-settings-menu-icon--bio" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg>
            </span>
            <span class="profile-settings-menu-text">
                <strong>Bio</strong>
                <small>Kendini tanıtan kısa yazı</small>
            </span>
            <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
        </button>

        <button type="button" class="profile-settings-menu-item" data-open-settings-panel="relationship">
            <span class="profile-settings-menu-icon profile-settings-menu-icon--heart" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>
            </span>
            <span class="profile-settings-menu-text">
                <strong>İlişki Durumu</strong>
                <small>Bekar, evli, boşanmış…</small>
            </span>
            <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
        </button>

        <button type="button" class="profile-settings-menu-item" data-open-settings-panel="hobbies">
            <span class="profile-settings-menu-icon profile-settings-menu-icon--hobbies" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"/><path d="M19 14l.8 2.2L22 17l-2.2.8L19 20l-.8-2.2L16 17l2.2-.8L19 14z"/></svg>
            </span>
            <span class="profile-settings-menu-text">
                <strong>Hobiler</strong>
                <small>İlgi alanlarınızı güncelleyin</small>
            </span>
            <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
        </button>

        <button type="button" class="profile-settings-menu-item" data-open-settings-panel="language">
            <span class="profile-settings-menu-icon profile-settings-menu-icon--lang" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            </span>
            <span class="profile-settings-menu-text">
                <strong>Dil Seç</strong>
                <small>Profil ve uygulama dili</small>
            </span>
            <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
        </button>

        <button type="button" class="profile-settings-menu-item" data-open-settings-panel="appearance">
            <span class="profile-settings-menu-icon profile-settings-menu-icon--theme" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
            </span>
            <span class="profile-settings-menu-text">
                <strong>Görünüm / Tema</strong>
                <small>Açık, koyu veya sistem teması</small>
            </span>
            <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
        </button>

        <button type="button" class="profile-settings-menu-item" data-open-settings-panel="privacy">
            <span class="profile-settings-menu-icon profile-settings-menu-icon--privacy" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </span>
            <span class="profile-settings-menu-text">
                <strong>Gizlilik</strong>
                <small>Okundu bilgisi ve bildirim sessizliği</small>
            </span>
            <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
        </button>

        <button type="button" class="profile-settings-menu-item" data-open-settings-panel="push">
            <span class="profile-settings-menu-icon profile-settings-menu-icon--push" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </span>
            <span class="profile-settings-menu-text">
                <strong>Tarayıcı bildirimleri</strong>
                <small>Mesaj ve duyuru izni ver</small>
            </span>
            <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
        </button>

        <button type="button" class="profile-settings-menu-item" data-open-settings-panel="password">
            <span class="profile-settings-menu-icon profile-settings-menu-icon--lock" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <span class="profile-settings-menu-text">
                <strong>Şifre Değiştir</strong>
                <small>Hesap güvenliğiniz</small>
            </span>
            <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
        </button>

        <a href="{{ route('referral') }}" class="profile-settings-menu-item profile-settings-menu-item--link">
            <span class="profile-settings-menu-icon profile-settings-menu-icon--invite" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7"/><path d="M12 3v13"/><path d="m7 8 5-5 5 5"/></svg>
            </span>
            <span class="profile-settings-menu-text">
                <strong>{{ $user->gender === 'female' ? 'Arkadaşını Davet Et' : 'Davet Et' }}</strong>
                <small>Arkadaşlarınızı platforma davet edin</small>
            </span>
            <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
        </a>

        <a href="{{ route('complaints') }}" class="profile-settings-menu-item profile-settings-menu-item--link">
            <span class="profile-settings-menu-icon profile-settings-menu-icon--security" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </span>
            <span class="profile-settings-menu-text">
                <strong>Güvenlik: Engelleme &amp; Şikayet</strong>
                <small>Politika ve güvenlik kuralları</small>
            </span>
            <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
        </a>

        <a href="{{ route('support') }}" class="profile-settings-menu-item profile-settings-menu-item--link">
            <span class="profile-settings-menu-icon profile-settings-menu-icon--support" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            </span>
            <span class="profile-settings-menu-text">
                <strong>Destek formu</strong>
                <small>Hesap, premium ve teknik yardım</small>
            </span>
            <span class="profile-settings-menu-chevron" aria-hidden="true">›</span>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="profile-settings-logout-form">
            @csrf
            <button type="submit" class="profile-settings-menu-item profile-settings-menu-item--danger">
                <span class="profile-settings-menu-icon profile-settings-menu-icon--logout" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </span>
                <span class="profile-settings-menu-text">
                    <strong>Çıkış</strong>
                    <small>Hesabınızdan güvenle çıkın</small>
                </span>
            </button>
        </form>
    </nav>
</div>

<div class="profile-settings-sheet-stage" data-settings-panel="edit" @if($initialPanel !== 'edit') hidden @endif>
    <form method="POST" action="{{ route('profile.update') }}" class="profile-settings-form profile-settings-form--authlike">
        @csrf
        @method('PUT')
        <input type="hidden" name="settings_panel" value="edit">

        <div class="auth-form-section">
            <p class="auth-form-section-label">Profil bilgileri</p>

            <div class="form-group auth-field">
                <label>
                    <span class="auth-field-icon auth-field-icon--user" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'user'])</span>
                    <span>Kullanıcı Adı</span>
                </label>
                <input type="text" value="{{ $user->username }}" readonly>
                <small class="profile-settings-hint">Kullanıcı adı değiştirilemez.</small>
            </div>

            <div class="form-row auth-form-row">
                <div class="form-group auth-field">
                    <label>
                        <span class="auth-field-icon auth-field-icon--user" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'user'])</span>
                        <span>Ad</span>
                    </label>
                    <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" placeholder="Ad" required>
                </div>
                <div class="form-group auth-field">
                    <label>
                        <span class="auth-field-icon auth-field-icon--user" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'user'])</span>
                        <span>Soyad</span>
                    </label>
                    <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" placeholder="Soyad" required>
                </div>
            </div>

            <div class="form-group auth-field">
                <label>
                    <span class="auth-field-icon auth-field-icon--mail" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'mail'])</span>
                    <span>E-posta</span>
                </label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="ornek@email.com" required>
                @error('email') <small class="form-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group auth-field">
                <label>
                    <span class="auth-field-icon auth-field-icon--phone" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'phone'])</span>
                    <span>Telefon</span>
                </label>
                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="5XX XXX XX XX">
            </div>

            <div class="form-group auth-field auth-field--location">
                <label>
                    <span class="auth-field-icon auth-field-icon--map" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'map-pin'])</span>
                    <span>Ülke, Şehir & İlçe</span>
                </label>
                @include('partials.location-fields', [
                    'country' => $user->country ?? 'Türkiye',
                    'city' => $user->city,
                    'district' => $user->district,
                ])
            </div>
        </div>

        <div class="auth-form-section auth-form-section--optional">
            <p class="auth-form-section-label">Ek bilgiler</p>

            <div class="form-group auth-field">
                <label>
                    <span class="auth-field-icon auth-field-icon--calendar" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'calendar'])</span>
                    <span>Doğum Tarihi (Gün / Ay / Yıl)</span>
                </label>
                @include('partials.birth-date-fields', ['birthDate' => $user->birth_date])
                <small class="profile-settings-hint">Yaşın profilinde kullanıcı adının yanında görünür.</small>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full">Kaydet</button>
    </form>
</div>

<div class="profile-settings-sheet-stage" data-settings-panel="bio" @if($initialPanel !== 'bio') hidden @endif>
    <form method="POST" action="{{ route('profile.update') }}" class="profile-settings-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="settings_panel" value="bio">

        <div class="form-group">
            <label for="settings_bio">Bio</label>
            <textarea
                id="settings_bio"
                name="bio"
                rows="5"
                maxlength="500"
                placeholder="Kendini kısaca anlat…"
            >{{ old('bio', $user->bio) }}</textarea>
            @error('bio') <small class="form-error">{{ $message }}</small> @enderror
            <small class="profile-settings-hint">İsteğe bağlı · En fazla 500 karakter · Herkes görebilir</small>
        </div>

        <button type="submit" class="btn btn-primary btn-full">Bio’yu Kaydet</button>
    </form>
</div>

<div class="profile-settings-sheet-stage" data-settings-panel="relationship" @if($initialPanel !== 'relationship') hidden @endif>
    <form method="POST" action="{{ route('profile.update') }}" class="profile-settings-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="settings_panel" value="relationship">

        <p class="profile-settings-panel-lead">İlişki durumunu ikonlu olarak seç. Herkes profilinde görebilir.</p>
        @include('partials.relationship-status-picker', ['selected' => old('relationship_status', $user->relationship_status)])

        <button type="submit" class="btn btn-primary btn-full" style="margin-top: 1rem;">İlişki Durumunu Kaydet</button>
    </form>
</div>

<div class="profile-settings-sheet-stage" data-settings-panel="hobbies" @if($initialPanel !== 'hobbies') hidden @endif>
    <form method="POST" action="{{ route('profile.update') }}" class="profile-settings-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="settings_panel" value="hobbies">

        @include('partials.hobbies-picker', ['selectedHobbies' => old('hobbies', $user->hobbies ?? [])])

        <button type="submit" class="btn btn-primary btn-full">Hobileri Kaydet</button>
    </form>
</div>

<div class="profile-settings-sheet-stage" data-settings-panel="language" @if($initialPanel !== 'language') hidden @endif>
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
</div>

@php
    $themeOptions = [
        'light' => ['label' => 'Açık', 'hint' => 'Parlak arayüz', 'icon' => 'sun'],
        'dark' => ['label' => 'Koyu', 'hint' => 'Karanlık arayüz', 'icon' => 'moon'],
        'system' => ['label' => 'Sistem', 'hint' => 'Cihaz ayarını takip et', 'icon' => 'system'],
    ];
@endphp
<div class="profile-settings-sheet-stage" data-settings-panel="appearance" @if($initialPanel !== 'appearance') hidden @endif>
    <p class="profile-settings-panel-lead">Uygulama temasını seçin. Seçiminiz hemen uygulanır ve hesabınıza kaydedilir.</p>
    <ul class="profile-settings-language-list" data-theme-options>
        @foreach($themeOptions as $value => $meta)
            <li>
                <form method="POST" action="{{ route('profile.update') }}" class="profile-settings-theme-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="settings_panel" value="appearance">
                    <input type="hidden" name="theme_preference" value="{{ $value }}">
                    <button
                        type="submit"
                        class="profile-settings-language-item {{ $themePreference === $value ? 'profile-settings-language-item--active' : '' }}"
                        data-theme-choice="{{ $value }}"
                        @if($themePreference === $value) aria-current="true" @endif
                    >
                        <span class="profile-settings-theme-icon" aria-hidden="true">
                            @if($meta['icon'] === 'sun')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                            @elseif($meta['icon'] === 'moon')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 14.5A8.5 8.5 0 1 1 9.5 3a7 7 0 0 0 11.5 11.5z"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                            @endif
                        </span>
                        <span class="profile-settings-menu-text">
                            <strong>{{ $meta['label'] }}</strong>
                            <small>{{ $meta['hint'] }}</small>
                        </span>
                        @if($themePreference === $value)
                            <span class="profile-settings-language-check" aria-hidden="true">✓</span>
                        @endif
                    </button>
                </form>
            </li>
        @endforeach
    </ul>
</div>

<div class="profile-settings-sheet-stage" data-settings-panel="privacy" @if($initialPanel !== 'privacy') hidden @endif>
    <form method="POST" action="{{ route('profile.update') }}" class="profile-settings-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="settings_panel" value="privacy">

        <p class="profile-settings-panel-lead">Mesaj ve bildirim tercihlerinizi buradan yönetin.</p>

        <label class="profile-settings-toggle">
            <input type="hidden" name="read_receipts_enabled" value="0">
            <input type="checkbox" name="read_receipts_enabled" value="1" {{ $readReceiptsEnabled ? 'checked' : '' }}>
            <span class="profile-settings-toggle__ui" aria-hidden="true"></span>
            <span class="profile-settings-toggle__copy">
                <strong>Okundu bilgisi</strong>
                <small>Mesajlarınızın okunduğunu karşı tarafa göster</small>
            </span>
        </label>

        <label class="profile-settings-toggle">
            <input type="hidden" name="quiet_hours_enabled" value="0">
            <input type="checkbox" name="quiet_hours_enabled" value="1" {{ $quietHoursEnabled ? 'checked' : '' }} data-quiet-hours-toggle>
            <span class="profile-settings-toggle__ui" aria-hidden="true"></span>
            <span class="profile-settings-toggle__copy">
                <strong>Sessiz saatler</strong>
                <small>Belirlediğiniz saatlerde bildirimleri azalt</small>
            </span>
        </label>

        <div class="profile-settings-quiet-fields" data-quiet-hours-fields @if(! $quietHoursEnabled) hidden @endif>
            <div class="form-row">
                <div class="form-group">
                    <label for="quiet_hours_start">Başlangıç</label>
                    <input type="time" id="quiet_hours_start" name="quiet_hours_start" value="{{ $quietHoursStart }}">
                </div>
                <div class="form-group">
                    <label for="quiet_hours_end">Bitiş</label>
                    <input type="time" id="quiet_hours_end" name="quiet_hours_end" value="{{ $quietHoursEnd }}">
                </div>
            </div>
            @error('quiet_hours_start') <small class="form-error">{{ $message }}</small> @enderror
            @error('quiet_hours_end') <small class="form-error">{{ $message }}</small> @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-full">Gizlilik ayarlarını kaydet</button>
    </form>
</div>

<div class="profile-settings-sheet-stage" data-settings-panel="push" @if($initialPanel !== 'push') hidden @endif>
    <div class="profile-settings-push" data-fcm-settings>
        <p class="profile-settings-panel-lead">Tarayıcıdan anlık mesaj ve duyuru almak için bildirim izni verin.</p>

        <div class="profile-settings-push-card">
            <div class="profile-settings-push-row">
                <span class="profile-settings-push-label">Durum</span>
                <span class="profile-settings-push-badge" data-fcm-status-badge data-tone="warn">Kontrol ediliyor…</span>
            </div>
            <p class="profile-settings-push-hint" data-fcm-status-hint>Tarayıcı bildirim izni kontrol ediliyor.</p>
            <button type="button" class="btn btn-primary btn-full" data-fcm-enable>İzin ver</button>
        </div>
    </div>
</div>

<div class="profile-settings-sheet-stage" data-settings-panel="password" @if($initialPanel !== 'password') hidden @endif>
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
</div>

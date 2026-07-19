@extends('layouts.auth')

@section('title', 'Kayıt Ol — Gönül Köprüsü')
@section('auth-mode', 'register')
@section('auth-visual-title', 'Yolculuğa başla')
@section('auth-visual-lead', 'Ücretsiz hesabını oluştur, profilini tamamla ve anlamlı bağlantılar kurmaya başla.')

@push('head')
@include('partials.asset', ['path' => 'css/profile-identity.min.css'])
@endpush

@section('auth-form-header')
    <p class="auth-eyebrow">Yeni üyelik</p>
    <h1>Kayıt Ol</h1>
    <p class="auth-subtitle">Google ile saniyeler içinde başla veya kısa formu doldur</p>
@endsection

@section('auth-form')
    @include('partials.google-auth-button', [
        'label' => 'oogle ile Kayıt Ol',
        'event' => 'sign_up_click',
        'eventLabel' => 'google_register',
        'iconSize' => 22,
        'gate' => true,
        'city' => old('city', request('city', '')),
    ])
    @include('partials.google-signup-gate')

    <p class="auth-divider"><span>veya e-posta ile</span></p>

    @include('partials.trust-badges')

    @if(!empty($referrer))
        <div class="auth-referrer-banner" role="note">
            <strong>{{ $referrer->first_name ?: $referrer->username }}</strong> seni Gönül Köprüsü'ne davet etti.
        </div>
    @endif

    <div class="auth-female-banner" role="note">
        <span class="auth-female-banner-icon" aria-hidden="true">💜</span>
        <p><strong>Kadın üyeler ücretsiz:</strong> mesajlaşma, kimler baktı ve galeri — premium gerekmez. Moderasyon + 7/24 destek.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="registerForm" class="auth-form auth-form--register">
        @csrf
        @if(!empty($refCode))
            <input type="hidden" name="ref" value="{{ $refCode }}">
        @endif

        <div class="auth-form-section">
            <p class="auth-form-section-label">Hızlı başlangıç</p>

            <div class="form-group auth-field">
                <label for="photo" class="auth-photo-upload">
                    <span class="auth-photo-upload-icon">@include('partials.theme-icon', ['icon' => 'camera'])</span>
                    <span class="auth-photo-upload-text">
                        <strong>Profil fotoğrafı ekle</strong>
                        <small>İsteğe bağlı — sonra da ekleyebilirsin</small>
                    </span>
                    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif,image/webp">
                </label>
                @error('photo') <small class="form-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group auth-field">
                <label for="username"><span class="auth-field-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'user'])</span><span>Kullanıcı Adı</span></label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="kullanici_adi" autocomplete="username" required>
                @error('username') <small class="form-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group auth-field">
                <label for="email"><span class="auth-field-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'mail'])</span><span>{{ __('app.auth.register.email_private') }}</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="ornek@email.com" autocomplete="email" required>
                @error('email') <small class="form-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-row auth-form-row">
                <div class="form-group auth-field">
                    <label for="password"><span class="auth-field-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'lock'])</span><span>Şifre</span></label>
                    <input type="password" id="password" name="password" placeholder="En az 8 karakter" autocomplete="new-password" required>
                </div>
                <div class="form-group auth-field">
                    <label for="password_confirmation"><span class="auth-field-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'lock'])</span><span>Şifre Tekrar</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Tekrar girin" autocomplete="new-password" required>
                </div>
            </div>

            <div class="form-group auth-field">
                <label for="gender"><span class="auth-field-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'users'])</span><span>Cinsiyet</span></label>
                @php
                    $prefillGender = old('gender', request('gender'));
                    if (! in_array($prefillGender, ['female', 'male'], true)) {
                        $prefillGender = '';
                    }
                @endphp
                <select id="gender" name="gender" required>
                    <option value="">Seçiniz</option>
                    <option value="female" {{ $prefillGender === 'female' ? 'selected' : '' }}>Kadın</option>
                    <option value="male" {{ $prefillGender === 'male' ? 'selected' : '' }}>Erkek</option>
                </select>
            </div>

            <div class="form-group auth-field auth-field--location">
                <label><span class="auth-field-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'map-pin'])</span><span>{{ __('app.auth.register.location_label') }}</span></label>
                @include('partials.location-fields', [
                    'countryMeta' => $countryMeta,
                    'country' => old('country', 'Türkiye'),
                    'city' => old('city', request('city')),
                    'district' => old('district'),
                ])
            </div>
        </div>

        <div class="auth-form-section auth-form-section--optional">
            <p class="auth-form-section-label">Ek bilgiler <small>(isteğe bağlı)</small></p>

            <details class="auth-optional-details" @if(old('first_name') || old('last_name') || $errors->hasAny(['first_name', 'last_name'])) open @endif>
                <summary><span class="auth-optional-details-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'user'])</span><span>Ad soyad (isteğe bağlı · gizli)</span></summary>
                <div class="auth-optional-details-body">
                    <div class="form-group auth-field auth-field--private-group">
                        <label for="first_name">{{ __('app.auth.register.full_name_private') }}</label>
                        <div class="form-row auth-form-row">
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" placeholder="{{ __('app.auth.register.first_name_placeholder') }}" autocomplete="given-name">
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" placeholder="{{ __('app.auth.register.last_name_placeholder') }}" autocomplete="family-name" aria-label="{{ __('app.auth.register.last_name_placeholder') }}">
                        </div>
                        @error('first_name') <small class="form-error">{{ $message }}</small> @enderror
                        @error('last_name') <small class="form-error">{{ $message }}</small> @enderror
                    </div>
                </div>
            </details>

            <details class="auth-optional-details" @if(old('phone_local') || old('phone_country_code') || $errors->hasAny(['phone_local', 'phone_country_code', 'phone'])) open @endif>
                <summary><span class="auth-optional-details-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'phone'])</span><span>Telefon (isteğe bağlı · gizli)</span></summary>
                <div class="auth-optional-details-body">
                    <div class="form-group auth-field">
                        @include('partials.phone-field', [
                            'dialCodes' => $dialCodes,
                            'countryMeta' => $countryMeta,
                            'optional' => true,
                        ])
                    </div>
                </div>
            </details>

            <details class="auth-optional-details" @if(old('bio') || $errors->has('bio')) open @endif>
                <summary><span class="auth-optional-details-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'edit'])</span><span>Bio (isteğe bağlı)</span></summary>
                <div class="auth-optional-details-body">
                    <div class="form-group auth-field">
                        <label for="register_bio">Kendini kısaca anlat</label>
                        <textarea
                            id="register_bio"
                            name="bio"
                            rows="3"
                            maxlength="500"
                            placeholder="Kendini kısaca anlat…"
                        >{{ old('bio') }}</textarea>
                        @error('bio') <small class="form-error">{{ $message }}</small> @enderror
                    </div>
                </div>
            </details>

            <details class="auth-optional-details" @if(old('birth_day') || old('birth_month') || old('birth_year') || $errors->hasAny(['birth_day', 'birth_month', 'birth_year', 'birth_date'])) open @endif>
                <summary><span class="auth-optional-details-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'calendar'])</span><span>Doğum tarihi (isteğe bağlı)</span></summary>
                <div class="auth-optional-details-body">
                    <div class="form-group auth-field">
                        <label>Gün / Ay / Yıl</label>
                        @include('partials.birth-date-fields')
                    </div>
                </div>
            </details>

            <details class="auth-optional-details" @if(old('relationship_status') || $errors->has('relationship_status')) open @endif>
                <summary><span class="auth-optional-details-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'heart'])</span><span>İlişki durumu (isteğe bağlı)</span></summary>
                <div class="auth-optional-details-body">
                    @include('partials.relationship-status-picker', ['selected' => old('relationship_status')])
                </div>
            </details>

            <details class="auth-optional-details" @if(old('hobbies') || $errors->hasAny(['hobbies', 'hobbies.*'])) open @endif>
                <summary><span class="auth-optional-details-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'sparkles'])</span><span>Hobiler (isteğe bağlı)</span></summary>
                <div class="auth-optional-details-body">
                    @include('partials.hobbies-picker', ['selectedHobbies' => old('hobbies', [])])
                </div>
            </details>
        </div>

        <div class="auth-consent">
            <label class="auth-consent-item">
                <input type="checkbox" id="privacy_accepted" name="privacy_accepted" value="1" {{ old('privacy_accepted') ? 'checked' : '' }}>
                <span>
                    <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Gizlilik Sözleşmesi</a>'ni okudum ve kabul ediyorum.
                </span>
            </label>
            @error('privacy_accepted') <small class="form-error auth-consent-error">{{ $message }}</small> @enderror

            <label class="auth-consent-item">
                <input type="checkbox" id="kvkk_accepted" name="kvkk_accepted" value="1" {{ old('kvkk_accepted') ? 'checked' : '' }}>
                <span>
                    <a href="{{ route('kvkk') }}" target="_blank" rel="noopener">KVKK Aydınlatma Metni</a>'ni okudum; kişisel verilerimin bu metin kapsamında işlenmesini kabul ediyorum.
                </span>
            </label>
            @error('kvkk_accepted') <small class="form-error auth-consent-error">{{ $message }}</small> @enderror
        </div>

        <button type="submit" id="registerSubmit" class="btn btn-primary btn-full auth-submit" disabled>Kayıt Ol</button>
    </form>
@endsection

@section('auth-form-footer')
    <p>Zaten hesabın var mı? <a href="{{ route('login') }}">Giriş yap</a></p>
@endsection

@push('auth-scripts')
@include('partials.asset', ['path' => 'js/register.min.js'])
<script>
(function () {
    var privacy = document.getElementById('privacy_accepted');
    var kvkk = document.getElementById('kvkk_accepted');
    var submit = document.getElementById('registerSubmit');
    var photoInput = document.getElementById('photo');
    var photoLabel = photoInput && photoInput.closest('.auth-photo-upload');

    function syncSubmitState() {
        submit.disabled = !(privacy.checked && kvkk.checked);
    }

    if (photoInput && photoLabel) {
        photoInput.addEventListener('change', function () {
            var name = photoInput.files && photoInput.files[0] ? photoInput.files[0].name : null;
            photoLabel.classList.toggle('auth-photo-upload--selected', !!name);
            var textEl = photoLabel.querySelector('.auth-photo-upload-text strong');
            if (textEl) {
                textEl.textContent = name || 'Profil fotoğrafı ekle';
            }
        });
    }

    privacy.addEventListener('change', syncSubmitState);
    kvkk.addEventListener('change', syncSubmitState);
    syncSubmitState();
})();
</script>
@endpush

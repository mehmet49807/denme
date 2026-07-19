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
    <a href="{{ url('auth/google') }}" class="btn btn-primary btn-full btn-google-login btn-google-login--top" data-gk-event="sign_up_click" data-gk-event-label="google_register">
        <span class="btn-google-login__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="22" height="22">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.36-8.16 2.36-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                <path fill="none" d="M0 0h48v48H0z"/>
            </svg>
        </span>
        <span class="btn-google-login__label">Google ile Kayıt Ol</span>
        <span class="btn-google-login__arrow" aria-hidden="true">→</span>
    </a>

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
                <label for="username">Kullanıcı Adı</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="kullanici_adi" autocomplete="username" required>
                @error('username') <small class="form-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group auth-field">
                <label for="email">{{ __('app.auth.register.email_private') }}</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="ornek@email.com" autocomplete="email" required>
                @error('email') <small class="form-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-row auth-form-row">
                <div class="form-group auth-field">
                    <label for="password">Şifre</label>
                    <input type="password" id="password" name="password" placeholder="En az 8 karakter" autocomplete="new-password" required>
                </div>
                <div class="form-group auth-field">
                    <label for="password_confirmation">Şifre Tekrar</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Tekrar girin" autocomplete="new-password" required>
                </div>
            </div>

            <div class="form-group auth-field">
                <label for="gender">Cinsiyet</label>
                <select id="gender" name="gender" required>
                    <option value="">Seçiniz</option>
                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Kadın</option>
                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Erkek</option>
                </select>
            </div>

            <div class="form-group auth-field auth-field--location">
                <label>{{ __('app.auth.register.location_label') }}</label>
                @include('partials.location-fields', [
                    'countryMeta' => $countryMeta,
                    'country' => old('country', 'Türkiye'),
                    'city' => old('city'),
                    'district' => old('district'),
                ])
            </div>
        </div>

        <div class="auth-form-section auth-form-section--optional">
            <p class="auth-form-section-label">Ek bilgiler <small>(isteğe bağlı)</small></p>

            <details class="auth-optional-details" @if(old('first_name') || old('last_name') || $errors->hasAny(['first_name', 'last_name'])) open @endif>
                <summary>Ad soyad (isteğe bağlı · gizli)</summary>
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
                <summary>Telefon (isteğe bağlı · gizli)</summary>
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
                <summary>Bio (isteğe bağlı)</summary>
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
                <summary>Doğum tarihi (isteğe bağlı)</summary>
                <div class="auth-optional-details-body">
                    <div class="form-group auth-field">
                        <label>Gün / Ay / Yıl</label>
                        @include('partials.birth-date-fields')
                    </div>
                </div>
            </details>

            <details class="auth-optional-details" @if(old('relationship_status') || $errors->has('relationship_status')) open @endif>
                <summary>İlişki durumu (isteğe bağlı)</summary>
                <div class="auth-optional-details-body">
                    @include('partials.relationship-status-picker', ['selected' => old('relationship_status')])
                </div>
            </details>

            <details class="auth-optional-details" @if(old('hobbies') || $errors->hasAny(['hobbies', 'hobbies.*'])) open @endif>
                <summary>Hobiler (isteğe bağlı)</summary>
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

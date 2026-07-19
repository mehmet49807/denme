@extends('layouts.auth')

@section('title', 'Google ile kayıt — Gönül Köprüsü')
@section('auth-mode', 'register')
@section('auth-visual-title', 'Son bir adım')
@section('auth-visual-lead', 'Google hesabın doğrulandı. Profilini tamamlayıp güvenli tanışma topluluğuna katıl.')

@section('auth-form-header')
    <p class="auth-eyebrow">Google kayıt</p>
    <h1>Profilini tamamla</h1>
    <p class="auth-subtitle">{{ $googleSignup['email'] ?? '' }}</p>
@endsection

@section('auth-form')
    <div class="auth-female-banner" role="note">
        <span class="auth-female-banner-icon" aria-hidden="true">💜</span>
        <p><strong>Kadın üyeler ücretsiz:</strong> mesajlaşma, kimler baktı ve galeri — premium gerekmez.</p>
    </div>

    <form method="POST" action="{{ route('auth.google.complete') }}" class="auth-form auth-form--register" id="googleCompleteForm">
        @csrf

        <div class="auth-form-section">
            <p class="auth-form-section-label">Hızlı başlangıç</p>

            <div class="form-group auth-field">
                <label for="username"><span class="auth-field-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'user'])</span><span>Kullanıcı Adı</span></label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="kullanici_adi" autocomplete="username" required pattern="[a-zA-Z0-9_]{3,50}">
                @error('username') <small class="form-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group auth-field">
                @include('partials.phone-field', [
                    'dialCodes' => $dialCodes,
                    'countryMeta' => $countryMeta,
                    'optional' => true,
                    'labelIcon' => 'phone',
                ])
            </div>

            <div class="form-group auth-field">
                <label for="gender"><span class="auth-field-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'users'])</span><span>Cinsiyet</span></label>
                <select id="gender" name="gender" required>
                    <option value="">Seçiniz</option>
                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Kadın</option>
                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Erkek</option>
                </select>
                @error('gender') <small class="form-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group auth-field auth-field--location">
                <label><span class="auth-field-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'map-pin'])</span><span>Ülke &amp; Şehir</span></label>
                @include('partials.location-fields', [
                    'countryMeta' => $countryMeta,
                    'country' => old('country', 'Türkiye'),
                    'city' => old('city'),
                    'showDistrict' => false,
                ])
            </div>
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

        <button type="submit" id="googleCompleteSubmit" class="btn btn-primary btn-full auth-submit" disabled>Kaydı tamamla</button>
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
    var submit = document.getElementById('googleCompleteSubmit');
    if (!privacy || !kvkk || !submit) return;
    function syncSubmitState() {
        submit.disabled = !(privacy.checked && kvkk.checked);
    }
    privacy.addEventListener('change', syncSubmitState);
    kvkk.addEventListener('change', syncSubmitState);
    syncSubmitState();
})();
</script>
@endpush

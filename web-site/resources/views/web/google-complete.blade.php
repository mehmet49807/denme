@extends('layouts.auth')

@section('title', 'Google Kayıt — Gönül Köprüsü')
@section('auth-mode', 'register')
@section('auth-visual-title', 'Son bir adım')
@section('auth-visual-lead', 'Cinsiyetini seç, sözleşmeleri onayla — hesabın hemen açılsın.')

@section('auth-form-header')
    <p class="auth-eyebrow">Google ile kayıt</p>
    <h1>Cinsiyetini seç</h1>
    <p class="auth-subtitle">
        {{ $googleSignup['email'] ?? '' }} ile devam ediyorsun.
        Kadınlarda mesajlaşma ücretsiz.
    </p>
@endsection

@section('auth-form')
    <div class="auth-female-banner" role="note">
        <span class="auth-female-banner-icon" aria-hidden="true">💜</span>
        <p><strong>Kadın üyeler ücretsiz:</strong> mesajlaşma, kimler baktı ve galeri — premium gerekmez.</p>
    </div>

    <form method="POST" action="{{ route('auth.google.complete') }}" class="auth-form" id="googleCompleteForm">
        @csrf

        <div class="form-group auth-field">
            <label for="gender"><span class="auth-field-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'users'])</span><span>Cinsiyet</span></label>
            <select id="gender" name="gender" required>
                <option value="">Seçiniz</option>
                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Kadın</option>
                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Erkek</option>
            </select>
            @error('gender') <small class="form-error">{{ $message }}</small> @enderror
        </div>

        <div class="auth-consent">
            <label class="auth-consent-item">
                <input type="checkbox" id="privacy_accepted" name="privacy_accepted" value="1" {{ old('privacy_accepted') ? 'checked' : '' }} required>
                <span><a href="{{ route('privacy') }}" target="_blank" rel="noopener">Gizlilik Sözleşmesi</a>'ni okudum, kabul ediyorum.</span>
            </label>
            <label class="auth-consent-item">
                <input type="checkbox" id="kvkk_accepted" name="kvkk_accepted" value="1" {{ old('kvkk_accepted') ? 'checked' : '' }} required>
                <span><a href="{{ route('kvkk') }}" target="_blank" rel="noopener">KVKK Aydınlatma Metni</a>'ni okudum, kabul ediyorum.</span>
            </label>
            @error('privacy_accepted') <small class="form-error">{{ $message }}</small> @enderror
            @error('kvkk_accepted') <small class="form-error">{{ $message }}</small> @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-full auth-submit" id="googleCompleteSubmit" disabled>
            Kayıtı tamamla
        </button>
    </form>

    <script>
    (function () {
        var form = document.getElementById('googleCompleteForm');
        if (!form) return;
        var privacy = document.getElementById('privacy_accepted');
        var kvkk = document.getElementById('kvkk_accepted');
        var btn = document.getElementById('googleCompleteSubmit');
        function sync() {
            btn.disabled = !(privacy.checked && kvkk.checked);
        }
        privacy.addEventListener('change', sync);
        kvkk.addEventListener('change', sync);
        sync();
    })();
    </script>
@endsection

@section('auth-form-footer')
    <p><a href="{{ route('register') }}">E-posta ile kayıt ol</a></p>
@endsection

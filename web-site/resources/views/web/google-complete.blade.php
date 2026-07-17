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
    <form method="POST" action="{{ route('auth.google.complete') }}" class="auth-form">
        @csrf

        <div class="form-group auth-field">
            <label for="username">Kullanıcı adı</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required autocomplete="username" pattern="[a-zA-Z0-9_]{3,50}">
            @error('username') <small class="form-error">{{ $message }}</small> @enderror
        </div>

        <div class="form-group auth-field">
            <label for="phone">Telefon <small class="auth-optional">(isteğe bağlı)</small></label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="05xx xxx xx xx">
            @error('phone') <small class="form-error">{{ $message }}</small> @enderror
        </div>
        <p class="auth-female-banner auth-female-banner--compact" role="note">
            <strong>Kadın üyeler:</strong> mesajlaşma ve kimler baktı ücretsizdir.
        </p>

        <div class="form-group auth-field">
            <label>Cinsiyet</label>
            <div class="gender-picker">
                <label class="gender-option">
                    <input type="radio" name="gender" value="female" @checked(old('gender') === 'female') required>
                    <span>Kadın</span>
                </label>
                <label class="gender-option">
                    <input type="radio" name="gender" value="male" @checked(old('gender') === 'male') required>
                    <span>Erkek</span>
                </label>
            </div>
            @error('gender') <small class="form-error">{{ $message }}</small> @enderror
        </div>

        <div class="form-group auth-field">
            <label>Ülke, Şehir & İlçe</label>
            @include('partials.location-fields', [
                'country' => old('country', 'Türkiye'),
                'city' => old('city'),
                'district' => old('district'),
            ])
        </div>

        <label class="checkbox-row">
            <input type="checkbox" name="privacy_accepted" value="1" @checked(old('privacy_accepted')) required>
            <span><a href="{{ route('privacy') }}" target="_blank" rel="noopener">Gizlilik Sözleşmesi</a>'ni okudum ve kabul ediyorum.</span>
        </label>
        @error('privacy_accepted') <small class="form-error">{{ $message }}</small> @enderror

        <label class="checkbox-row">
            <input type="checkbox" name="kvkk_accepted" value="1" @checked(old('kvkk_accepted')) required>
            <span><a href="{{ route('kvkk') }}" target="_blank" rel="noopener">KVKK Aydınlatma Metni</a>'ni okudum ve kabul ediyorum.</span>
        </label>
        @error('kvkk_accepted') <small class="form-error">{{ $message }}</small> @enderror

        <button type="submit" class="btn btn-primary btn-full auth-submit">Kaydı tamamla</button>
    </form>
@endsection

@section('auth-form-footer')
    <p class="auth-switch"><a href="{{ route('login') }}">Giriş sayfasına dön</a></p>
@endsection

@push('page-scripts')
@include('partials.asset', ['path' => 'js/locations.min.js'])
@endpush

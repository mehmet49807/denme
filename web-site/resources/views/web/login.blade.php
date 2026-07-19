@extends('layouts.auth')

@section('title', 'Giriş — Gönül Köprüsü')
@section('auth-mode', 'login')
@section('auth-visual-title', 'Tekrar hoş geldin')
@section('auth-visual-lead', 'Bağlantılarını sürdür, yeni hikâyeler keşfet. Güvenli ve saygılı tanışma ortamına kaldığın yerden devam et.')

@section('auth-form-header')
    <p class="auth-eyebrow">Hesabın</p>
    <h1>Giriş Yap</h1>
    <p class="auth-subtitle">Google ile hızlı giriş veya e-posta / kullanıcı adı</p>
@endsection

@section('auth-form')
    <a href="{{ url('auth/google') }}" class="btn btn-primary btn-full btn-google-login btn-google-login--top" data-gk-event="google_login_click">
        <span class="btn-google-login__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="22" height="22">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.36-8.16 2.36-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                <path fill="none" d="M0 0h48v48H0z"/>
            </svg>
        </span>
        <span class="btn-google-login__label">Google ile Giriş Yap</span>
        <span class="btn-google-login__arrow" aria-hidden="true">→</span>
    </a>

    <p class="auth-divider"><span>veya e-posta ile</span></p>

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf
        <div class="form-group auth-field">
            <label for="login">Kullanıcı Adı veya E-posta</label>
            <input type="text" id="login" name="login" value="{{ old('login') }}" placeholder="ornek@email.com" autocomplete="username" required>
            @error('login') <small class="form-error">{{ $message }}</small> @enderror
        </div>
        <div class="form-group auth-field">
            <label for="password">Şifre</label>
            <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full auth-submit">
            Giriş Yap
        </button>
    </form>
@endsection

@section('auth-form-footer')
    <p>Hesabın yok mu? <a href="{{ route('register', ['utm_source' => 'login', 'utm_medium' => 'cta', 'utm_campaign' => 'organic']) }}" data-gk-event="sign_up_click" data-gk-event-label="login">Ücretsiz kayıt ol</a></p>
    <p><a href="{{ route('password.request') }}">Şifremi unuttum</a></p>
@endsection

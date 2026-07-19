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
    @include('partials.google-auth-button', [
        'label' => 'Google ile Giriş Yap',
        'event' => 'google_login_click',
        'iconSize' => 22,
    ])

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

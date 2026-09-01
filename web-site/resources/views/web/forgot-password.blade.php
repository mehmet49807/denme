@extends('layouts.auth')

@section('title', 'Şifre Sıfırla — Gönül Köprüsü')
@section('auth-mode', 'forgot')
@section('auth-visual-title', 'Şifreni mi unuttun?')
@section('auth-visual-lead', 'Sorun değil. E-posta adresini gir, sana şifreni sıfırlamak için bir bağlantı gönderelim.')

@section('auth-form-header')
    <p class="auth-eyebrow">Hesap kurtarma</p>
    <h1>Şifremi Unuttum</h1>
    <p class="auth-subtitle">E-posta adresini gir, sıfırlama bağlantısı gönderelim</p>
@endsection

@section('auth-form')
    @if (session('status'))
        <div class="auth-alert auth-alert--success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf
        <div class="form-group auth-field">
            <label for="email">E-posta Adresi</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="ornek@email.com" autocomplete="email" required oninvalid="this.setCustomValidity('Lütfen geçerli bir e-posta adresi girin.')" oninput="this.setCustomValidity('')">
            @error('email') <small class="form-error">{{ $message }}</small> @enderror
        </div>
        <button type="submit" class="btn btn-primary btn-full auth-submit">
            Sıfırlama Bağlantısı Gönder
        </button>
    </form>
@endsection

@section('auth-form-footer')
    <p>Hatırladın mı? <a href="{{ route('login') }}">Geri giriş yap</a></p>
@endsection

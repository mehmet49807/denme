@extends('layouts.auth')

@section('title', 'Yeni Şifre Belirle — Gönül Köprüsü')
@section('auth-mode', 'reset')
@section('auth-visual-title', 'Yeni şifreni belirle')
@section('auth-visual-lead', 'Güçlü bir şifre seç. Hesabını güvende tutmak için en az 8 karakter kullan.')

@section('auth-form-header')
    <p class="auth-eyebrow">Şifre sıfırlama</p>
    <h1>Yeni Şifre</h1>
    <p class="auth-subtitle">Hesabın için yeni bir şifre belirle</p>
@endsection

@section('auth-form')
    <form method="POST" action="{{ route('password.update') }}" class="auth-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group auth-field">
            <label for="email">E-posta Adresi</label>
            <input type="email" id="email" name="email" value="{{ $email ?? old('email') }}" placeholder="ornek@email.com" autocomplete="email" required oninvalid="this.setCustomValidity('Lütfen geçerli bir e-posta adresi girin.')" oninput="this.setCustomValidity('')">
            @error('email') <small class="form-error">{{ $message }}</small> @enderror
        </div>

        <div class="form-group auth-field">
            <label for="password">Yeni Şifre</label>
            <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="new-password" required minlength="8" oninvalid="this.setCustomValidity('Şifre en az 8 karakter olmalıdır.')" oninput="this.setCustomValidity('')">
            @error('password') <small class="form-error">{{ $message }}</small> @enderror
        </div>

        <div class="form-group auth-field">
            <label for="password_confirmation">Yeni Şifre (Tekrar)</label>
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••" autocomplete="new-password" required minlength="8" oninvalid="this.setCustomValidity('Lütfen şifreyi tekrar girin.')" oninput="this.setCustomValidity('')">
            @error('password_confirmation') <small class="form-error">{{ $message }}</small> @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-full auth-submit">
            Şifreyi Güncelle
        </button>
    </form>
@endsection

@section('auth-form-footer')
    <p><a href="{{ route('login') }}">Geri giriş yap</a></p>
@endsection

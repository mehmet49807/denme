@extends('layouts.auth')

@section('title', 'İki Adımlı Doğrulama — Gönül Köprüsü')
@section('auth-mode', '2fa')
@section('auth-visual-title', 'İki adımlı doğrulama')
@section('auth-visual-lead', 'Hesabının güvenliği için e-postana gönderilen 6 haneli kodu gir.')

@section('auth-form-header')
    <p class="auth-eyebrow">Güvenlik</p>
    <h1>Doğrulama Kodu</h1>
    <p class="auth-subtitle">E-postana gönderilen 6 haneli kodu gir</p>
@endsection

@section('auth-form')
    @if (session('status'))
        <div class="auth-alert auth-alert--success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('2fa.check') }}" class="auth-form">
        @csrf
        <div class="form-group auth-field">
            <label for="code">6 Haneli Kod</label>
            <input type="text" id="code" name="code" maxlength="6" pattern="[0-9]{6}" placeholder="000000" autocomplete="one-time-code" inputmode="numeric" required style="text-align:center;letter-spacing:0.4em;font-size:22px;" oninvalid="this.setCustomValidity('6 haneli kodu girin.')" oninput="this.setCustomValidity('')">
            @error('code') <small class="form-error">{{ $message }}</small> @enderror
        </div>
        <button type="submit" class="btn btn-primary btn-full auth-submit">
            Doğrula
        </button>
    </form>

    <form method="POST" action="{{ route('2fa.resend') }}" style="margin-top:16px;text-align:center;">
        @csrf
        <button type="submit" class="btn btn-ghost btn-full" style="font-size:13px;">Kod gelmedi mi? Tekrar gönder</button>
    </form>
@endsection

@section('auth-form-footer')
    <p><a href="{{ route('login') }}">Geri giriş yap</a></p>
@endsection

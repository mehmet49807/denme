@extends('layouts.app')
@section('title', 'Giriş · Gönül Köprüsü')

@section('content')
<div class="gk-container" style="max-width:440px;">
    <div class="gk-card">
        <h2 style="margin-top:0;">Tekrar Hoş Geldiniz</h2>
        @if ($errors->any())
            <div style="color:var(--gk-rose-deep); margin-bottom:12px;">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="gk-field">
                <label>Kullanıcı Adı veya E-posta</label>
                <input class="gk-input" name="login" value="{{ old('login') }}" required>
            </div>
            <div class="gk-field">
                <label>Şifre</label>
                <input class="gk-input" type="password" name="password" required>
            </div>
            <label style="font-size:.85rem; color:var(--gk-text-soft);">
                <input type="checkbox" name="remember"> Beni hatırla
            </label>
            <button class="gk-btn" style="width:100%; margin-top:14px;">Giriş Yap</button>
        </form>
        <p style="text-align:center; margin-top:16px;">Hesabınız yok mu? <a href="{{ route('register') }}">Kayıt olun</a></p>
    </div>
</div>
@endsection

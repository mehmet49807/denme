@extends('layouts.app-auth')

@section('title', 'Giriş — Gönül Köprüsü Uygulama')
@section('auth-mode', 'login')
@section('app-auth-tag', 'Uygulamaya hoş geldin')

@section('auth-form-header')
    <h1 class="app-auth__title">Merhaba tekrar</h1>
    <p class="app-auth__lead">Hesabına gir, sohbetine ve keşfe kaldığın yerden devam et.</p>
@endsection

@section('auth-form')
    @include('partials.google-auth-button', [
        'label' => 'oogle ile devam et',
        'event' => 'google_login_click',
        'eventLabel' => 'app_login',
        'iconSize' => 20,
        'class' => 'app-auth__google',
        'showArrow' => false,
    ])

    <p class="app-auth__divider"><span>veya e-posta</span></p>

    <form method="POST" action="{{ route('login') }}" class="app-auth__form">
        @csrf
        @if(!empty($redirect))
            <input type="hidden" name="redirect" value="{{ $redirect }}">
        @endif
        <label class="app-auth__field">
            <span>E-posta veya kullanıcı adı</span>
            <input type="text" name="login" value="{{ old('login') }}" placeholder="ornek@email.com" autocomplete="username" required>
            @error('login') <small>{{ $message }}</small> @enderror
        </label>
        <label class="app-auth__field">
            <span>Şifre</span>
            <input type="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
            @error('password') <small>{{ $message }}</small> @enderror
        </label>
        <label class="app-auth__remember">
            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
            <span>Beni hatırla</span>
        </label>
        <button type="submit" class="app-auth__submit">Giriş yap</button>
    </form>
@endsection

@section('auth-form-footer')
    <p>
        Hesabın yok mu?
        <a href="{{ route('app.register', request()->only('redirect', 'ref')) }}">Ücretsiz üye ol</a>
    </p>
    <p>
        <a href="{{ route('password.request') }}">Şifremi unuttum</a>
    </p>
@endsection

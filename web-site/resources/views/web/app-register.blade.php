@extends('layouts.app-auth')

@section('title', 'Üye Ol — Gönül Köprüsü Uygulama')
@section('auth-mode', 'register')
@section('app-auth-tag', 'Köprüyü sen kur')

@section('auth-form-header')
    <h1 class="app-auth__title">Hadi başlayalım</h1>
    <p class="app-auth__lead">Ciddi ilişki arayanlar için güvenli uygulama. Birkaç adımda ücretsiz üye ol.</p>
@endsection

@section('auth-form')
    @include('partials.google-auth-button', [
        'label' => 'oogle ile üye ol',
        'event' => 'google_login_click',
        'eventLabel' => 'app_register',
        'iconSize' => 20,
        'class' => 'app-auth__google',
        'showArrow' => false,
    ])

    <p class="app-auth__divider"><span>veya e-posta ile</span></p>

    <a class="app-auth__submit app-auth__submit--secondary" href="{{ route('register', array_filter(['redirect' => request('redirect'), 'ref' => request('ref'), 'utm_source' => 'app', 'utm_medium' => 'register', 'utm_campaign' => 'organic'])) }}">
        E-posta ile kayıt formuna geç
    </a>
@endsection

@section('auth-form-footer')
    <p>
        Zaten üye misin?
        <a href="{{ route('app.login', request()->only('redirect', 'ref')) }}">Giriş yap</a>
    </p>
@endsection

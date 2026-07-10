@extends('layouts.app-with-sidebar')

@section('title', 'Şifre Değiştir — ' . __('app.brand'))

@section('app-content')
@php $hasPassword = ! empty($user->password); @endphp

<div class="profile-settings-page feed-container">
    @include('partials.settings-page-header', ['title' => 'Şifre Değiştir'])

    <div class="profile-settings-page-body">
        @if($user->google_id && ! $hasPassword)
            <p class="profile-settings-panel-lead">Google ile giriş yaptınız. Şifre oluşturmak için e-posta adresinize sıfırlama bağlantısı gönderebilirsiniz.</p>
            <a href="{{ route('password.request') }}" class="btn btn-outline btn-full">Şifre Belirle</a>
        @else
            <form method="POST" action="{{ route('profile.password') }}" class="profile-settings-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="settings_panel" value="password">

                <div class="form-group">
                    <label for="current_password">Mevcut Şifre</label>
                    <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                    @error('current_password') <small class="form-error">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="password">Yeni Şifre</label>
                    <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
                    @error('password') <small class="form-error">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Yeni Şifre (Tekrar)</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary btn-full">Şifreyi Güncelle</button>
            </form>
        @endif
    </div>
</div>
@endsection

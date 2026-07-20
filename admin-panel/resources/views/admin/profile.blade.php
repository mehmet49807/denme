@extends('layouts.admin')

@section('title', 'Profilim')
@section('lead', 'Yönetici hesap bilgilerinizi ve şifrenizi güncelleyin.')

@section('content')
<div class="admin-profile-grid">
    <section class="admin-panel admin-panel--glass admin-profile-card admin-profile-card--photo">
        <h3 class="admin-panel-title">Profil Fotoğrafı</h3>
        <div class="admin-profile-photo-wrap">
            <span class="admin-profile-photo">
                @if($user->profile_photo_url)
                    <img src="{{ $user->profile_photo_url }}" alt="" width="112" height="112" loading="lazy" decoding="async">
                @else
                    {{ strtoupper(substr($user->username, 0, 1)) }}
                @endif
            </span>
            <div class="admin-profile-photo-meta">
                <strong>{{ $user->username }}</strong>
                <small>Yönetici hesabı</small>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.profile.photo') }}" enctype="multipart/form-data" class="admin-profile-photo-form">
            @csrf
            <label class="admin-profile-file-label">
                <input type="file" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" required>
                <span class="btn btn-outline btn-sm">Yeni fotoğraf seç</span>
            </label>
            <button type="submit" class="btn btn-primary btn-sm">Fotoğrafı yükle</button>
        </form>
        @error('photo') <p class="admin-profile-error">{{ $message }}</p> @enderror
    </section>

    <section class="admin-panel admin-panel--glass admin-profile-card">
        <h3 class="admin-panel-title">Hesap Bilgileri</h3>
        <form method="POST" action="{{ route('admin.profile.update') }}" class="admin-profile-form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="admin_profile_username">Kullanıcı adı</label>
                <input
                    type="text"
                    id="admin_profile_username"
                    name="username"
                    value="{{ old('username', $user->username) }}"
                    required
                    minlength="3"
                    maxlength="50"
                    pattern="[a-zA-Z0-9_]+"
                    autocomplete="username"
                >
                <small class="admin-field-hint">3–50 karakter; yalnızca harf, rakam ve alt çizgi</small>
                @error('username') <small class="admin-profile-error">{{ $message }}</small> @enderror
            </div>

            <div class="admin-profile-form-row">
                <div class="form-group">
                    <label for="admin_profile_first_name">Ad</label>
                    <input type="text" id="admin_profile_first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                    @error('first_name') <small class="admin-profile-error">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label for="admin_profile_last_name">Soyad</label>
                    <input type="text" id="admin_profile_last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                    @error('last_name') <small class="admin-profile-error">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="admin_profile_email">E-posta</label>
                <input type="email" id="admin_profile_email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email') <small class="admin-profile-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="admin_profile_phone">Telefon</label>
                <input type="tel" id="admin_profile_phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Opsiyonel">
                @error('phone') <small class="admin-profile-error">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="btn btn-primary">Bilgileri kaydet</button>
        </form>
    </section>

    <section class="admin-panel admin-panel--glass admin-profile-card admin-profile-card--password">
        <h3 class="admin-panel-title">Şifre Değiştir</h3>
        <form method="POST" action="{{ route('admin.profile.password') }}" class="admin-profile-form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="admin_profile_current_password">Mevcut şifre</label>
                <input type="password" id="admin_profile_current_password" name="current_password" autocomplete="current-password" required>
                @error('current_password') <small class="admin-profile-error">{{ $message }}</small> @enderror
            </div>

            <div class="admin-profile-form-row">
                <div class="form-group">
                    <label for="admin_profile_password">Yeni şifre</label>
                    <input type="password" id="admin_profile_password" name="password" autocomplete="new-password" required>
                    @error('password') <small class="admin-profile-error">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label for="admin_profile_password_confirmation">Yeni şifre tekrar</label>
                    <input type="password" id="admin_profile_password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-outline">Şifreyi güncelle</button>
        </form>
    </section>
</div>
@endsection

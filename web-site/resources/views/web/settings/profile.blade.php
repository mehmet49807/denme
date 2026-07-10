@extends('layouts.app-with-sidebar')

@section('title', 'Profil Düzenle — ' . __('app.brand'))

@section('app-content')
<div class="profile-settings-page feed-container">
    @include('partials.settings-page-header', ['title' => 'Profil Düzenle'])

    <div class="profile-settings-page-body">
        <form method="POST" action="{{ route('profile.update') }}" class="profile-settings-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="settings_panel" value="edit">

            <div class="form-group">
                <label>Kullanıcı Adı (değiştirilemez)</label>
                <input type="text" value="{{ $user->username }}" readonly>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Ad</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                </div>
                <div class="form-group">
                    <label>Soyad</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label>E-posta</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email') <small class="form-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label>Telefon</label>
                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}">
            </div>

            <div class="form-group">
                <label>Ülke, Şehir & İlçe</label>
                @include('partials.location-fields', [
                    'country' => $user->country ?? 'Türkiye',
                    'city' => $user->city,
                    'district' => $user->district,
                ])
            </div>

            <button type="submit" class="btn btn-primary btn-full">Kaydet</button>
        </form>
    </div>
</div>
@endsection

@push('page-scripts')
<script src="{{ asset('js/locations.js') }}?v=world-locations-1"></script>
@endpush

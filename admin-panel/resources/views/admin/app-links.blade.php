@extends('layouts.admin')

@section('title', 'Uygulama Linkleri')
@section('lead', 'Google Play ve App Store adreslerini yönetin. Boş bırakılan linkler sitede “Yakında” olarak görünür.')

@section('content')
@if(session('success'))
    <div class="admin-flash admin-flash--success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="admin-flash admin-flash--error">
        <ul class="admin-flash-list">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.app-links.update') }}" class="admin-app-links-form">
    @csrf

    <section class="admin-panel admin-panel--glass">
        <header class="admin-package-card__head">
            <div>
                <h3 class="admin-panel-title">Mağaza linkleri</h3>
                <p class="admin-package-card__sub">Footer, ana sayfa ve Premium sayfasındaki mağaza rozetlerinde kullanılır.</p>
            </div>
        </header>

        <div class="admin-form-grid">
            <div class="form-group form-group--full">
                <label for="android_app_url">Android (Google Play) URL</label>
                <input
                    type="url"
                    id="android_app_url"
                    name="android_app_url"
                    value="{{ old('android_app_url', $androidAppUrl) }}"
                    placeholder="https://play.google.com/store/apps/details?id=..."
                    inputmode="url"
                    autocomplete="off"
                >
            </div>
            <div class="form-group form-group--full">
                <label for="ios_app_url">iOS (App Store) URL</label>
                <input
                    type="url"
                    id="ios_app_url"
                    name="ios_app_url"
                    value="{{ old('ios_app_url', $iosAppUrl) }}"
                    placeholder="https://apps.apple.com/app/id..."
                    inputmode="url"
                    autocomplete="off"
                >
            </div>
        </div>

        <div class="admin-app-links-preview">
            <p class="admin-package-card__sub">
                Android:
                @if(filled(old('android_app_url', $androidAppUrl)))
                    <a href="{{ old('android_app_url', $androidAppUrl) }}" target="_blank" rel="noopener">{{ old('android_app_url', $androidAppUrl) }}</a>
                @else
                    <em>Yakında</em>
                @endif
            </p>
            <p class="admin-package-card__sub">
                iOS:
                @if(filled(old('ios_app_url', $iosAppUrl)))
                    <a href="{{ old('ios_app_url', $iosAppUrl) }}" target="_blank" rel="noopener">{{ old('ios_app_url', $iosAppUrl) }}</a>
                @else
                    <em>Yakında</em>
                @endif
            </p>
        </div>
    </section>

    <div class="admin-form-actions">
        <button type="submit" class="btn btn-primary">Linkleri Kaydet</button>
        <a href="{{ route('admin.packages') }}" class="btn btn-outline">Paketlere Dön</a>
    </div>
</form>
@endsection

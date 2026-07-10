@extends('layouts.app-with-sidebar')

@php $activeNav = 'profile'; @endphp

@section('title', 'Profil — Gönül Köprüsü')

@push('head')
<link rel="stylesheet" href="{{ asset('css/profile-toolbar-mobile.css') }}?v=profile-toolbar-mobile-2">
@endpush

@section('app-content')
@php
    $allStoryGroups = $ownStoryGroup ? collect([$ownStoryGroup]) : collect();
@endphp

<div class="profile-page feed-container">
    <header class="profile-header">
        <div class="profile-photo-wrap @if($ownStoryGroup) profile-photo-wrap--has-story @endif">
            @if($ownStoryGroup)
            <button
                type="button"
                class="profile-photo profile-photo--story story-item"
                id="profilePhotoPreview"
                data-story-index="0"
                data-profile-open-story="0"
                data-user-id="{{ $user->id }}"
                aria-label="Hikayeni görüntüle"
            >
                <span class="story-ring story-ring--unseen story-ring--profile story-ring--own">
                    <span class="story-avatar">
                        @if($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="Profil" width="96" height="96" loading="eager" decoding="async">
                        @else
                            {{ strtoupper(substr($user->username, 0, 1)) }}
                        @endif
                    </span>
                </span>
            </button>
            @else
            <div class="profile-photo" id="profilePhotoPreview">
                @if($user->profile_photo_url)
                    <img src="{{ $user->profile_photo_url }}" alt="Profil" width="96" height="96" loading="eager" decoding="async">
                @else
                    <span class="profile-photo-initial">{{ strtoupper(substr($user->username, 0, 1)) }}</span>
                @endif
            </div>
            @endif
            <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" class="profile-photo-form">
                @csrf
                <label class="profile-photo-change" title="Profil fotoğrafı değiştir">
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/gif,image/webp">
                    @include('partials.icon-camera')
                </label>
            </form>
        </div>
        <div class="profile-header-meta">
            <h1 class="profile-username">{{ $user->username }}</h1>
            <p class="profile-location-line">
                {{ $user->country ?? 'Türkiye' }} — {{ $user->city }}
                @if($user->district) — {{ $user->district }}@endif
            </p>
            <p class="profile-post-count">{{ $posts->count() }} gönderi</p>
            @if($ownStoryGroup)
                <p class="profile-story-hint">Profil fotoğrafına dokunarak hikayeni görüntüleyebilirsin.</p>
            @endif
        </div>
    </header>
    @error('photo') <small class="form-error profile-photo-error">{{ $message }}</small> @enderror
    @if(session('success')) <p class="profile-success">{{ session('success') }}</p> @endif

    <div class="profile-toolbar-row">
        <details class="profile-settings profile-settings--toolbar">
            <summary class="profile-settings-toggle">
                <span class="profile-settings-toggle-icon" aria-hidden="true">
                    @include('partials.theme-icon', ['icon' => 'edit'])
                </span>
                <span class="profile-settings-toggle-label">Profil Bilgilerini Düzenle</span>
                <span class="profile-settings-toggle-chevron" aria-hidden="true">▾</span>
            </summary>
            <div class="profile-settings-body">
                <form method="POST" action="{{ route('profile') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Kullanıcı Adı (değiştirilemez)</label>
                        <input type="text" value="{{ $user->username }}" readonly>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Ad</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}">
                        </div>
                        <div class="form-group">
                            <label>Soyad</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>E-posta</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}">
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
        </details>

        @include('partials.profile-language-dropdown')
    </div>

    @include('partials.feed-toolbar', ['viewer' => $user])
    @error('image') <small class="form-error">{{ $message }}</small> @enderror
    @error('story') <small class="form-error">{{ $message }}</small> @enderror

    @include('partials.profile-posts-grid', [
        'profileUser' => $user,
        'viewer' => $user,
        'likedPostIds' => $likedPostIds ?? [],
        'isOwnProfile' => true,
    ])
</div>

@include('partials.feed-compose', ['viewer' => $user])

@if($ownStoryGroup)
<div class="ig-story-viewer" id="igStoryViewer" hidden data-groups="{{ $allStoryGroups->toJson() }}">
    <div class="ig-story-frame">
        <div class="ig-story-progress" id="igStoryProgress"></div>

        <header class="ig-story-header">
            <a href="{{ route('profile') }}" id="igStoryUserLink" class="ig-story-user">
                <span class="ig-story-user-avatar" id="igStoryUserAvatar"></span>
                <span class="ig-story-user-meta">
                    <strong id="igStoryUserName"></strong>
                    <small id="igStoryTime">Şimdi</small>
                </span>
            </a>
            <div class="ig-story-header-actions">
                <button type="button" class="ig-story-delete" id="igStoryDelete" hidden aria-label="Hikayeyi sil">🗑</button>
                <button type="button" class="ig-story-close" data-close-story aria-label="Kapat">×</button>
            </div>
        </header>

        <div class="ig-story-stage" id="igStoryStage">
            <button type="button" class="ig-story-tap ig-story-tap--prev" id="igStoryTapPrev" aria-label="Önceki"></button>
            <div class="ig-story-media" id="igStoryMedia"></div>
            <button type="button" class="ig-story-tap ig-story-tap--next" id="igStoryTapNext" aria-label="Sonraki"></button>
        </div>
    </div>
</div>
@endif

<script src="{{ asset('js/feed.js') }}?v=feed-upload-v3"></script>
<script src="{{ asset('js/profile-posts.js') }}?v=profile-posts-1"></script>
<script src="{{ asset('js/profile-photo.js') }}?v=profile-photo-2"></script>
@if($ownStoryGroup)
<script src="{{ asset('js/stories.js') }}?v=ig-stories-3"></script>
@endif
<script src="{{ asset('js/locations.js') }}?v=world-locations-1"></script>
@endsection

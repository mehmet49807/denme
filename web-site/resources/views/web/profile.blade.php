@extends('layouts.app-with-sidebar')

@php $activeNav = 'profile'; @endphp

@push('head')
<link rel="stylesheet" href="{{ asset('css/profile-toolbar-mobile.css') }}?v=profile-toolbar-mobile-3">
<link rel="stylesheet" href="{{ asset('css/profile-premium-sections.css') }}?v=profile-premium-sections-2">
@endpush

@section('title', 'Profil — Gönül Köprüsü')

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
            <h1 class="profile-username">
                <span class="profile-username-text">{{ $user->username }}</span>
                @include('partials.profile-verified-tick', ['user' => $user, 'size' => 'md'])
                @include('partials.trust-badge', ['user' => $user, 'size' => 'md'])
                @include('partials.profile-online-label', ['user' => $user])
            </h1>
            <p class="profile-location-line">
                {{ $user->country ?? 'Türkiye' }} — {{ $user->city }}
                @if($user->district) — {{ $user->district }}@endif
            </p>
            @if($user->bio)
                <p class="profile-bio">{{ $user->bio }}</p>
            @endif
            @if($user->relationship_expectation)
                <p class="profile-expectation"><span>Beklenti:</span> {{ $user->relationship_expectation }}</p>
            @endif
            <p class="profile-post-count">{{ $posts->count() }} gönderi</p>
            @include('partials.hobbies-display', ['user' => $user])
            @if($ownStoryGroup)
                <p class="profile-story-hint">Profil fotoğrafına dokunarak hikayeni görüntüleyebilirsin.</p>
            @endif
        </div>
    </header>
    @error('photo') <small class="form-error profile-photo-error">{{ $message }}</small> @enderror
    @if(session('success')) <p class="profile-success">{{ session('success') }}</p> @endif
    @error('boost') <small class="form-error">{{ $message }}</small> @enderror

    @include('partials.profile-views', ['user' => $user, 'profileViews' => $profileViews ?? collect()])
    @include('partials.profile-gallery', ['user' => $user])

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
@endsection

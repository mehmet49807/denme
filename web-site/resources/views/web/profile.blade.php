@extends('layouts.app-with-sidebar')

@php $activeNav = 'profile'; @endphp

@push('head')
@include('partials.asset', ['path' => 'css/profile-page.min.css'])
@endpush

@section('title', 'Profil — Gönül Köprüsü')

@section('app-content')
@php
    $allStoryGroups = $ownStoryGroup ? collect([$ownStoryGroup]) : collect();
@endphp

<div class="profile-page feed-container">
    <header class="profile-header">
        @php
            $pkgType = $user->activePackageType();
        @endphp
        <div class="profile-photo-wrap @if($ownStoryGroup) profile-photo-wrap--has-story @endif @if(in_array($pkgType, ['pro','gold','platinum'])) profile-photo-wrap--premium-{{ $pkgType }} @endif">
            @if($ownStoryGroup)
            <button
                type="button"
                class="profile-photo profile-photo--story story-item story-item--own"
                id="profilePhotoPreview"
                data-story-index="0"
                data-user-id="{{ $user->id }}"
                aria-label="Hikayeni görüntüle"
            >
                <span class="story-ring story-ring--unseen story-ring--profile story-ring--own{{ in_array($pkgType ?? null, ['pro','gold','platinum']) ? ' story-ring--premium-'.($pkgType ?? '') : '' }}">
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
        @if(in_array($pkgType, ['pro','gold','platinum']))
        @php
            $_pkg = app(App\Services\PremiumPackagesService::class)->package($pkgType);
        @endphp
        <span class="profile-pkg-badge profile-pkg-badge--{{ $pkgType }}">
            {{ $_pkg['badge_label'] ?? ucfirst($pkgType) }}
        </span>
        @endif
        <div class="profile-header-meta">
            @include('partials.profile-identity', [
                'user' => $user,
                'postsCount' => $posts->count(),
                'tickSize' => 'md',
            ])
            @include('partials.profile-member-badges', ['user' => $user])
            @include('partials.hobbies-display', ['user' => $user])
            @if($ownStoryGroup)
                <p class="profile-story-hint">Profil fotoğrafına dokunarak hikayeni görüntüleyebilirsin.</p>
            @endif
        </div>
    </header>
    @error('photo') <small class="form-error profile-photo-error">{{ $message }}</small> @enderror
    @if(session('success')) <p class="profile-success">{{ session('success') }}</p> @endif
    @error('boost') <small class="form-error">{{ $message }}</small> @enderror

    @include('partials.profile-boost', ['user' => $user])
    @include('partials.profile-views', [
        'user' => $user,
        'profileViews' => $profileViews ?? collect(),
        'profileViewsCount' => $profileViewsCount ?? null,
    ])
    @include('partials.profile-gallery', ['user' => $user, 'viewer' => $user])

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
@include('partials.post-caption-edit-dialog')

@include('partials.ig-story-viewer')

@include('partials.asset', ['path' => 'js/profile-page.min.js', 'defer' => true])
@endsection

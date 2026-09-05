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
        <div class="profile-photo-col">
            <div class="profile-photo-wrap @if($ownStoryGroup) profile-photo-wrap--has-story @endif @if(in_array($pkgType, ['pro','gold','platinum'])) profile-photo-wrap--premium-{{ $pkgType }} @endif">
                @if($ownStoryGroup)
                <button type="button" class="profile-photo profile-photo--story story-item story-item--own" id="profilePhotoPreview" data-story-index="0" data-user-id="{{ $user->id }}" aria-label="Hikayeni görüntüle">
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
                    <label class="profile-photo-upload-btn" for="profilePhotoInput">Fotoğraf değiştir</label>
                    <input type="file" id="profilePhotoInput" name="photo" accept="image/jpeg,image/png,image/webp,image/gif" class="profile-photo-input" onchange="this.form.submit()">
                </form>
            </div>
            @if($pkgType)
            @php $_pkg = \App\Models\PremiumSubscription::PACKAGES[$pkgType] ?? null; @endphp
            @if($_pkg)
            <span class="profile-pkg-badge profile-pkg-badge--{{ $pkgType }}">{{ $_pkg['badge_label'] ?? ucfirst($pkgType) }}</span>
            @endif
            @endif
        </div>
        <div class="profile-header-meta">
            @include('partials.profile-identity', ['user' => $user, 'postsCount' => $posts->count(), 'tickSize' => 'md'])
            @include('partials.profile-member-badges', ['user' => $user])
            @include('partials.hobbies-display', ['user' => $user])
            @if($ownStoryGroup)
                <p class="profile-story-hint">Profil fotoğrafına dokunarak hikayeni görüntüleyebilirsin.</p>
            @endif
        </div>
    </header>
    @error('photo') <small class="form-error profile-photo-error">{{ $message }}</small> @enderror
    @if(session('success')) <p class="profile-success">{{ session('success') }}</p> @endif
    @if(session('status')) <p class="profile-success">{{ session('status') }}</p> @endif
    @if(session('error')) <p class="form-error">{{ session('error') }}</p> @endif

    @include('partials.profile-completeness', ['completeness' => $completeness ?? null])

    @if(! $user->email_verified_at)
        <section class="profile-email-verification" role="status" aria-labelledby="profile-email-verification-title">
            <div>
                <strong id="profile-email-verification-title">E-posta doğrulama (doğrulanmış rozet için)</strong>
                <p>Tüm sayfaları kullanabilirsiniz. Doğrulanmış profil rozeti için e-postanıza gelen 6 haneli kodu girin; ardından admin onayını bekleyin.</p>
            </div>
            <form method="POST" action="{{ route('verification.send') }}" style="margin-bottom:12px;">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">Doğrulama kodu gönder</button>
            </form>
            <form method="POST" action="{{ route('verification.code') }}" class="profile-email-code-form">
                @csrf
                <label for="email_verify_code" class="sr-only">6 haneli kod</label>
                <input type="text" id="email_verify_code" name="code" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code" placeholder="000000" required style="text-align:center;letter-spacing:0.35em;font-size:1.15rem;max-width:10rem;margin-right:0.5rem;">
                <button type="submit" class="btn btn-primary btn-sm">Kodu doğrula</button>
                @error('code') <small class="form-error" style="display:block;margin-top:6px;">{{ $message }}</small> @enderror
            </form>
        </section>
    @elseif(! \App\Support\PhotoVerification::hasAdminApproval($user))
        <section class="profile-email-verification profile-email-verification--pending" role="status">
            <div>
                <strong>E-posta doğrulandı</strong>
                <p>Doğrulanmış profil rozeti için admin profil onayını bekliyorsunuz.</p>
            </div>
        </section>
    @endif
    @error('boost') <small class="form-error">{{ $message }}</small> @enderror

    @include('partials.profile-boost', ['user' => $user])
    @include('partials.profile-views', ['user' => $user, 'profileViews' => $profileViews ?? collect(), 'profileViewsCount' => $profileViewsCount ?? null])
    @include('partials.profile-gallery', ['user' => $user, 'viewer' => $user])
    @include('partials.feed-toolbar', ['viewer' => $user])
    @error('image') <small class="form-error">{{ $message }}</small> @enderror
    @error('story') <small class="form-error">{{ $message }}</small> @enderror

    @include('partials.profile-posts-grid', ['profileUser' => $user, 'viewer' => $user, 'likedPostIds' => $likedPostIds ?? [], 'isOwnProfile' => true])
</div>

@include('partials.match-celebration')
@include('partials.feed-compose', ['viewer' => $user])
@include('partials.post-caption-edit-dialog')
@include('partials.ig-story-viewer')
@include('partials.asset', ['path' => 'js/profile-page.min.js', 'defer' => true])
@endsection

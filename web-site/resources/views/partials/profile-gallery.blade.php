@php
    $viewer = $viewer ?? $user;
    $isOwnProfile = ($viewer->id === $user->id);
    $canView = $viewer->canViewProfileGallery();
    $canManage = $isOwnProfile && $viewer->canManageProfileGallery();
    $photos = $canView ? $user->galleryPhotos() : [];
    $photoCount = is_countable($photos) ? count($photos) : 0;
@endphp

@if($canView)
<section class="profile-gallery" aria-label="{{ __('app.profile.gallery_title') }}">
    <header class="profile-section-head profile-section-head--gallery">
        <div class="profile-section-head__main">
            <span class="profile-section-head__icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'camera'])</span>
            <div class="profile-section-head__copy">
                <h2 class="profile-section-head__title">{{ __('app.profile.gallery_title') }}</h2>
                <p class="profile-section-head__sub">{{ __('app.profile.gallery_sub') }}</p>
            </div>
        </div>
        <div class="profile-section-head__aside">
            <span class="profile-section-head__meta">{{ $photoCount }} {{ __('app.profile.gallery_photos') }}</span>
            @if($canManage)
            <form method="POST" action="{{ route('profile.gallery') }}" enctype="multipart/form-data" class="profile-gallery-add">
                @csrf
                <label class="btn btn-outline btn-sm profile-gallery-add-btn">
                    {{ __('app.profile.gallery_add') }}
                    <input type="file" name="gallery_photo" accept="image/jpeg,image/png,image/gif,image/webp" onchange="this.form.submit()" hidden>
                </label>
            </form>
            @endif
        </div>
    </header>
    @if($canManage)
        @error('gallery_photo') <small class="form-error">{{ $message }}</small> @enderror
    @endif
    <div class="profile-gallery-grid">
        @forelse($photos as $photoUrl)
            <figure class="profile-gallery-item">
                <button
                    type="button"
                    class="profile-gallery-open"
                    data-open-gallery-detail
                    data-image-url="{{ $photoUrl }}"
                    data-username="{{ $user->username }}"
                    @if($canManage)
                    data-destroy-url="{{ route('profile.gallery.destroy') }}"
                    data-destroy-url-value="{{ $photoUrl }}"
                    @endif
                    aria-label="{{ __('app.profile.gallery_zoom') }}"
                >
                    <img src="{{ $photoUrl }}" alt="" loading="lazy" decoding="async">
                    <span class="profile-gallery-zoom" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M14 10l6.1-6.1M9 21H3v-6M10 14l-6.1 6.1"/></svg>
                    </span>
                </button>
                @if($canManage)
                <form method="POST" action="{{ route('profile.gallery.destroy') }}" class="profile-gallery-delete" onclick="event.stopPropagation()">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="url" value="{{ $photoUrl }}">
                    <button type="submit" aria-label="{{ __('app.common.delete') }}">×</button>
                </form>
                @endif
            </figure>
        @empty
            <p class="profile-gallery-empty">
                @if($isOwnProfile)
                    {{ __('app.profile.gallery_empty_own') }}
                @else
                    {{ __('app.profile.gallery_empty_other') }}
                @endif
            </p>
        @endforelse
    </div>
</section>
@elseif($viewer->gender === 'male')
<section class="profile-premium-lock profile-premium-lock--gallery" aria-label="{{ __('app.profile.gallery_title') }}">
    <div class="profile-premium-lock-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'camera'])</div>
    <div class="profile-premium-lock-copy">
        <h2>{{ __('app.profile.gallery_title') }}</h2>
        <p>{{ __('app.premium.gallery_lock') }}</p>
    </div>
    <a href="{{ route('premium') }}#premium-packages" class="btn btn-primary btn-sm">Pro’yu incele</a>
</section>
@endif

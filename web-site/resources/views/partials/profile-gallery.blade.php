@php
    $canAccess = $user->canAccessPremiumProfileFeatures();
    $photos = $canAccess ? $user->galleryPhotos() : [];
@endphp

@if($canAccess)
<section class="profile-gallery" aria-label="Galeri">
    <div class="profile-gallery-head">
        <h2 class="profile-gallery-title">
            <span class="profile-gallery-title-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'post'])</span>
            <span>Galeri</span>
        </h2>
        <form method="POST" action="{{ route('profile.gallery') }}" enctype="multipart/form-data" class="profile-gallery-add">
            @csrf
            <label class="btn btn-outline btn-sm">
                Fotoğraf ekle
                <input type="file" name="gallery_photo" accept="image/jpeg,image/png,image/gif,image/webp" onchange="this.form.submit()" hidden>
            </label>
        </form>
    </div>
    @error('gallery_photo') <small class="form-error">{{ $message }}</small> @enderror
    <div class="profile-gallery-grid">
        @forelse($photos as $photoUrl)
            <figure class="profile-gallery-item">
                <img src="{{ $photoUrl }}" alt="" loading="lazy">
                <form method="POST" action="{{ route('profile.gallery.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="url" value="{{ $photoUrl }}">
                    <button type="submit" aria-label="Sil">×</button>
                </form>
            </figure>
        @empty
            <p class="profile-gallery-empty">Henüz ek fotoğraf yok. Galeri profilini güçlendirir.</p>
        @endforelse
    </div>
</section>
@elseif($user->gender === 'male')
<section class="profile-premium-lock profile-premium-lock--gallery" aria-label="Galeri Premium">
    <div class="profile-premium-lock-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'camera'])</div>
    <div class="profile-premium-lock-copy">
        <h2>Galeri</h2>
        <p>Ek fotoğraf galerisi Premium üyelere özeldir.</p>
    </div>
    <a href="{{ route('premium') }}" class="btn btn-primary btn-sm">Premium’u incele</a>
</section>
@endif

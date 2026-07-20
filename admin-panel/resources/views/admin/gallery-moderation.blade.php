@extends('layouts.admin')

@section('title', 'Galeri Moderasyon')
@section('lead', 'Üye galeri fotoğraflarını inceleyin ve uygunsuzları kaldırın.')

@section('content')
<div class="admin-panel admin-panel--glass">
    <form method="GET" action="{{ route('admin.gallery') }}" class="admin-users-filter" role="search">
        <div class="admin-users-filter-field admin-users-filter-field--grow">
            <label for="gallery-search">Kullanıcı ara</label>
            <input type="search" id="gallery-search" name="search" value="{{ $search }}" placeholder="Kullanıcı adı veya e-posta…">
        </div>
        <div class="admin-users-filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">Ara</button>
            @if($search !== '')
                <a href="{{ route('admin.gallery') }}" class="btn btn-outline btn-sm">Temizle</a>
            @endif
        </div>
    </form>
</div>

<div class="admin-gallery-grid">
    @forelse($items as $item)
        <article class="admin-gallery-card">
            <img src="{{ $item['url'] }}" alt="{{ $item['user']->username }}" loading="lazy">
            <div class="admin-gallery-card__meta">
                <strong>{{ $item['user']->username }}</strong>
                <span>{{ $item['user']->city }}</span>
            </div>
            <form method="POST" action="{{ route('admin.gallery.remove', $item['user']) }}" onsubmit="return confirm('Bu fotoğraf kaldırılsın mı?');">
                @csrf
                <input type="hidden" name="index" value="{{ $item['index'] }}">
                <button type="submit" class="btn btn-danger btn-sm">Kaldır</button>
            </form>
        </article>
    @empty
        <div class="admin-panel admin-panel--glass">
            <p class="admin-ops-empty">Gösterilecek galeri fotoğrafı yok.</p>
        </div>
    @endforelse
</div>

{{ $users->links() }}
@endsection

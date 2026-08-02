@extends('layouts.admin')

@section('title', 'İçerik Moderasyonu')
@section('lead', 'Gönderileri ve hikâyeleri inceleyin; yönetici hikâyesi ekleyin.')

@section('content')
<div class="admin-stat-grid admin-stat-grid--compact">
    <div class="admin-stat-card admin-stat-card--violet">
        <div class="admin-stat-value">{{ $stats['posts'] }}</div>
        <div class="admin-stat-label">Gönderi</div>
    </div>
    <div class="admin-stat-card admin-stat-card--indigo">
        <div class="admin-stat-value">{{ $stats['stories_total'] }}</div>
        <div class="admin-stat-label">Hikâye (toplam)</div>
    </div>
    <div class="admin-stat-card admin-stat-card--emerald">
        <div class="admin-stat-value">{{ $stats['stories_active'] }}</div>
        <div class="admin-stat-label">Aktif hikâye</div>
    </div>
</div>

<div class="admin-panel admin-panel--glass">
    <div class="admin-users-filter" style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:end;">
        <div class="admin-users-filter-actions" style="display:flex;gap:.5rem;">
            <a href="{{ route('admin.content', ['tab' => 'posts', 'search' => $search]) }}"
               class="btn btn-sm {{ $tab === 'posts' ? 'btn-primary' : 'btn-outline' }}">Gönderiler</a>
            <a href="{{ route('admin.content', ['tab' => 'stories', 'search' => $search]) }}"
               class="btn btn-sm {{ $tab === 'stories' ? 'btn-primary' : 'btn-outline' }}">Hikâyeler</a>
        </div>
        <form method="GET" action="{{ route('admin.content') }}" class="admin-users-filter" role="search" style="flex:1;display:flex;gap:.5rem;align-items:end;">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="admin-users-filter-field admin-users-filter-field--grow" style="flex:1;">
                <label for="content-search">Kullanıcı ara</label>
                <input type="search" id="content-search" name="search" value="{{ $search }}" placeholder="Kullanıcı adı veya ad…">
            </div>
            <div class="admin-users-filter-actions">
                <button type="submit" class="btn btn-primary btn-sm">Ara</button>
                @if($search !== '')
                    <a href="{{ route('admin.content', ['tab' => $tab]) }}" class="btn btn-outline btn-sm">Temizle</a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($tab === 'stories')
    <div class="admin-panel admin-panel--glass" style="margin-bottom:1rem;">
        <h2 style="margin:0 0 .35rem;font-size:1.05rem;">Yönetici hikâyesi ekle</h2>
        <p class="admin-ops-meta" style="margin:0 0 1rem;">
            24 saat görünür. Hedef kitleyi seçin: tüm üyeler, yalnızca kadınlar veya yalnızca erkekler.
        </p>
        <form method="POST" action="{{ route('admin.content.stories.store') }}" enctype="multipart/form-data"
              style="display:grid;gap:1rem;max-width:36rem;">
            @csrf
            <div>
                <label for="admin-story-media" style="display:block;margin-bottom:.35rem;font-weight:600;">Fotoğraf veya video</label>
                <input type="file" id="admin-story-media" name="media" accept="image/*,video/*" required>
                @error('media')
                    <small class="form-error" style="display:block;margin-top:.35rem;color:#b42318;">{{ $message }}</small>
                @enderror
            </div>
            <fieldset style="border:0;padding:0;margin:0;">
                <legend style="font-weight:600;margin-bottom:.5rem;">Hedef kitle</legend>
                <div style="display:flex;flex-wrap:wrap;gap:.75rem 1.25rem;">
                    <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;">
                        <input type="radio" name="audience" value="all" {{ old('audience', 'all') === 'all' ? 'checked' : '' }}>
                        Tüm üyeler
                    </label>
                    <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;">
                        <input type="radio" name="audience" value="female" {{ old('audience') === 'female' ? 'checked' : '' }}>
                        Yalnızca kadınlar
                    </label>
                    <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;">
                        <input type="radio" name="audience" value="male" {{ old('audience') === 'male' ? 'checked' : '' }}>
                        Yalnızca erkekler
                    </label>
                </div>
                @error('audience')
                    <small class="form-error" style="display:block;margin-top:.35rem;color:#b42318;">{{ $message }}</small>
                @enderror
            </fieldset>
            <div>
                <button type="submit" class="btn btn-primary">Hikâyeyi paylaş</button>
            </div>
        </form>
    </div>
@endif

@if($tab === 'posts')
    <div class="admin-gallery-grid">
        @forelse($items as $post)
            <article class="admin-gallery-card">
                @if($post->image_url)
                    <img src="{{ $post->image_url }}" alt="{{ $post->user?->username }}" loading="lazy">
                @else
                    <div class="admin-ops-empty" style="padding:2rem;text-align:center;">Metin gönderi</div>
                @endif
                <div class="admin-gallery-card__meta">
                    <strong>{{ $post->user?->username ?? '—' }}</strong>
                    <span>{{ $post->created_at?->format('d.m.Y H:i') }}</span>
                </div>
                @if($post->caption)
                    <p class="admin-ops-meta" style="padding:0 .75rem .5rem;">{{ \Illuminate\Support\Str::limit($post->caption, 120) }}</p>
                @endif
                <form method="POST" action="{{ route('admin.content.posts.destroy', $post) }}"
                      onsubmit="return confirm('Bu gönderi silinsin mi?');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="search" value="{{ $search }}">
                    <input type="hidden" name="page" value="{{ request('page') }}">
                    <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                </form>
            </article>
        @empty
            <div class="admin-panel admin-panel--glass">
                <p class="admin-ops-empty">Gösterilecek gönderi yok.</p>
            </div>
        @endforelse
    </div>
@else
    <div class="admin-gallery-grid">
        @forelse($items as $story)
            <article class="admin-gallery-card">
                @if($story->media_url)
                    @if(($story->media_type ?? '') === 'video')
                        <video src="{{ $story->media_url }}" controls muted playsinline style="width:100%;max-height:280px;object-fit:cover;"></video>
                    @else
                        <img src="{{ $story->media_url }}" alt="{{ $story->user?->username }}" loading="lazy">
                    @endif
                @endif
                <div class="admin-gallery-card__meta">
                    <strong>{{ $story->user?->username ?? '—' }}</strong>
                    <span>{{ $story->created_at?->format('d.m.Y H:i') }}</span>
                </div>
                <p class="admin-ops-meta" style="padding:0 .75rem .5rem;">
                    Hedef: {{ \App\Models\Story::audienceLabel($story->audience ?? null) }}
                    @if($story->expires_at)
                        · Bitiş: {{ $story->expires_at->format('d.m.Y H:i') }}
                    @endif
                </p>
                <form method="POST" action="{{ route('admin.content.stories.destroy', $story) }}"
                      onsubmit="return confirm('Bu hikâye silinsin mi?');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="search" value="{{ $search }}">
                    <input type="hidden" name="page" value="{{ request('page') }}">
                    <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                </form>
            </article>
        @empty
            <div class="admin-panel admin-panel--glass">
                <p class="admin-ops-empty">Gösterilecek hikâye yok.</p>
            </div>
        @endforelse
    </div>
@endif

{{ $items->links() }}
@endsection

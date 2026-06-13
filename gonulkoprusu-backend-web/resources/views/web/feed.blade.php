@extends('layouts.app')
@section('title', 'Akış · Gönül Köprüsü')

@section('content')
<div class="gk-container" style="max-width:600px;">
    <h2>Akış</h2>
    @forelse ($posts as $post)
        <article class="gk-card" style="margin-bottom:22px; padding:0; overflow:hidden;">
            {{-- Header: username on the LEFT, city-district box on the RIGHT --}}
            <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 16px;">
                <strong>{{ $post->user->username }}</strong>
                <span class="gk-location-box">{{ $post->user->city }} <span class="gk-sep">·</span> {{ $post->user->district }}</span>
            </div>
            {{-- Photo --}}
            <img src="{{ $post->image_url }}" alt="Gönderi" style="width:100%; display:block; background:var(--gk-cream-2);">
            {{-- Actions: only Like. Comments are CLOSED. --}}
            <div style="padding:12px 16px;">
                <button class="gk-btn gk-btn--ghost" data-like="{{ $post->id }}">♥ Beğen ({{ $post->likes_count }})</button>
                @if ($post->caption)<p style="margin:10px 0 0; color:var(--gk-text-soft);">{{ $post->caption }}</p>@endif
                {{-- No comment box rendered: comments are disabled platform-wide. --}}
            </div>
        </article>
    @empty
        <div class="gk-card">Henüz gösterilecek gönderi yok.</div>
    @endforelse
    {{ $posts->links() }}
</div>

<script>
document.querySelectorAll('[data-like]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        fetch('/api/v1/posts/' + btn.dataset.like + '/like', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        });
    });
});
</script>
@endsection

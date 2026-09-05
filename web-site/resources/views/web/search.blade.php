@extends('layouts.content-page')

@section('title', 'Üye Ara — Gönül Köprüsü')
@section('page-eyebrow', 'Keşif')
@section('page-title', 'Üye Ara')
@section('page-lead', 'Yaş, şehir, hobi ve daha fazlasıyla üye keşfet.')

@section('page-content')
@php
    $filters = $filters ?? [];
    $hobbyOptions = $hobbyOptions ?? [];
    $relationshipOptions = $relationshipOptions ?? [];
@endphp
<form method="GET" action="{{ route('search') }}" class="search-page-form discovery-filter-form" role="search" data-search-form data-suggest-url="{{ $suggestUrl }}">
    <label for="search-q" class="sr-only">Arama</label>
    <div class="search-page-field">
        <input type="search" id="search-q" name="q" value="{{ $filters['q'] ?? $q ?? '' }}" placeholder="Kullanıcı adı, şehir veya ilçe…" maxlength="80" autocomplete="off" class="search-page-input" data-search-input aria-autocomplete="list" aria-controls="search-suggest" aria-expanded="false">
        <ul id="search-suggest" class="search-page-suggest" role="listbox" hidden data-search-suggest></ul>
        <button type="submit" class="btn btn-primary">Ara</button>
    </div>
    <div class="discovery-filters" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:0.75rem;margin-top:0.85rem;">
        <label>Min yaş
            <input type="number" name="age_min" min="18" max="80" value="{{ $filters['age_min'] ?? '' }}" class="search-page-input" placeholder="18">
        </label>
        <label>Max yaş
            <input type="number" name="age_max" min="18" max="80" value="{{ $filters['age_max'] ?? '' }}" class="search-page-input" placeholder="50">
        </label>
        <label>Şehir
            <input type="text" name="city" value="{{ $filters['city'] ?? '' }}" class="search-page-input" placeholder="İstanbul" maxlength="80">
        </label>
        <label>İlçe
            <input type="text" name="district" value="{{ $filters['district'] ?? '' }}" class="search-page-input" placeholder="Kadıköy" maxlength="80">
        </label>
        <label>İlgi alanı
            <select name="hobby" class="search-page-input">
                <option value="">Tümü</option>
                @foreach($hobbyOptions as $hobby)
                    <option value="{{ $hobby['id'] }}" @selected(($filters['hobby'] ?? '') === $hobby['id'])>{{ $hobby['label'] }}</option>
                @endforeach
            </select>
        </label>
        <label>İlişki durumu
            <select name="relationship_status" class="search-page-input">
                <option value="">Tümü</option>
                @foreach($relationshipOptions as $statusKey => $status)
                    <option value="{{ $statusKey }}" @selected(($filters['relationship_status'] ?? '') === $statusKey)>{{ is_array($status) ? ($status['label'] ?? $statusKey) : $status }}</option>
                @endforeach
            </select>
        </label>
        <label style="display:flex;align-items:center;gap:0.4rem;margin-top:1.4rem;">
            <input type="checkbox" name="online" value="1" @checked(!empty($filters['online']))> Çevrimiçi
        </label>
        <label style="display:flex;align-items:center;gap:0.4rem;margin-top:1.4rem;">
            <input type="checkbox" name="with_photo" value="1" @checked(!empty($filters['with_photo']))> Fotoğraflı
        </label>
    </div>
</form>

@if($users && $users->total() > 0)
    <p class="search-page-count">{{ number_format($users->total()) }} üye</p>
    <div class="users-browse-grid search-page-grid">
        @include('partials.users-browse-grid-items', [
            'users' => $users,
            'likedUserIds' => $likedUserIds ?? [],
            'followingUserIds' => $followingUserIds ?? [],
        ])
    </div>
    @if($users->hasPages())
        <div class="users-browse-pagination">
            {{ $users->links() }}
        </div>
    @endif
@else
    @include('partials.empty-state', [
        'title' => 'Sonuç yok',
        'text' => $emptyMessage,
        'icon' => 'search',
        'ctaUrl' => route('search'),
        'ctaLabel' => 'Filtreleri temizle',
    ])
@endif
@endsection

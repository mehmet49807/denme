@extends('layouts.app-with-sidebar')

@php $activeNav = 'users'; @endphp

@section('title', $locationLabel . ' — ' . __('app.brand'))

@push('head')
@include('partials.asset', ['path' => 'css/location-search.min.css'])
@endpush

@section('app-content')
<div class="users-browse-page location-users-page">
    <header class="users-browse-hero">
        <div class="users-browse-hero-glow" aria-hidden="true"></div>
        <div class="users-browse-hero-inner">
            <div class="users-browse-hero-row">
                <div class="users-browse-hero-title">
                    <span class="users-browse-badge">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        {{ $showResults ? 'Konum' : 'Keşfet' }}
                    </span>
                    <h1>{{ $showResults ? $locationLabel : 'Konuma Göre Üye Ara' }}</h1>
                </div>
                @if($showResults && $users)
                <div class="users-browse-stats">
                    <span class="users-browse-stat">
                        <strong>{{ number_format($users->total()) }}</strong>
                        <span>{{ __('app.users.stat_members') }}</span>
                    </span>
                    @if($users->hasPages())
                    <span class="users-browse-stat">
                        <strong>{{ $users->currentPage() }}</strong>
                        <span>/ {{ $users->lastPage() }}</span>
                    </span>
                    @endif
                </div>
                @endif
            </div>
            <p class="location-users-lead">Ülke, şehir ve isteğe bağlı ilçe seçerek bölgedeki üyeleri listeleyin.</p>
        </div>
    </header>

    <section class="location-search-panel" aria-label="Konum arama">
        @include('partials.location-search-form', [
            'country' => $country,
            'city' => $city,
            'district' => $district,
        ])
    </section>

    @if($showResults && $users)
        @if($users->isNotEmpty())
        <div class="users-browse-grid">
            @include('partials.users-browse-grid-items', ['users' => $users])
        </div>

        <div class="users-browse-pagination">
            {{ $users->links() }}
        </div>
        @else
        <div class="users-browse-empty">
            <div class="users-browse-empty-icon" aria-hidden="true">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
            </div>
            <h2>Bu bölgede üye yok</h2>
            <p>{{ $locationLabel }} için henüz kayıtlı üye bulunmuyor.</p>
        </div>
        @endif
    @else
    <div class="location-search-hint">
        <p>Aramaya başlamak için yukarıdan ülke ve şehir seçin.</p>
    </div>
    @endif
</div>

@endsection

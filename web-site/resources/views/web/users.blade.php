@extends('layouts.app-with-sidebar')

@php $activeNav = 'users'; @endphp

@section('title', __('app.nav.users') . ' — ' . __('app.brand'))

@section('app-content')
<div class="users-browse-page">
    <header class="users-browse-hero">
        <div class="users-browse-hero-glow" aria-hidden="true"></div>
        <div class="users-browse-hero-inner">
            <div class="users-browse-hero-row">
                <div class="users-browse-hero-title">
                    <span class="users-browse-badge">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        {{ __('app.users.explore') }}
                    </span>
                    <h1>{{ __('app.nav.users') }}</h1>
                </div>
                <div class="users-browse-stats">
                    <span class="users-browse-stat">
                        <strong>{{ number_format($users->total()) }}</strong>
                        <span>{{ __('app.users.stat_members') }}</span>
                    </span>
                    @if($users->hasPages())
                    <span class="users-browse-stat">
                        <strong>{{ $users->currentPage() }}</strong>
                        <span>/ {{ $users->lastPage() }} {{ __('app.users.page') }}</span>
                    </span>
                    @endif
                </div>
            </div>
            <p class="users-browse-hero-lead">{{ __('app.users.hero_lead') }}</p>
            <p class="users-browse-pkg-legend" aria-label="{{ __('app.users.pkg_legend') }}">
                <span class="users-browse-pkg-legend__item users-browse-pkg-legend__item--platinum">Platinum</span>
                <span class="users-browse-pkg-legend__item users-browse-pkg-legend__item--gold">Gold</span>
                <span class="users-browse-pkg-legend__item users-browse-pkg-legend__item--pro">Pro</span>
            </p>
            @php $filter = $filter ?? 'all'; @endphp
            <nav class="users-browse-filters" aria-label="Üye filtresi">
                <a href="{{ route('users.index', ['filter' => 'all']) }}" class="{{ $filter === 'all' ? 'is-active' : '' }}">Tümü</a>
                <a href="{{ route('users.index', ['filter' => 'online']) }}" class="{{ $filter === 'online' ? 'is-active' : '' }}">Çevrimiçi</a>
                @if(filled($viewer->city ?? null))
                    <a href="{{ route('users.index', ['filter' => 'city']) }}" class="{{ $filter === 'city' ? 'is-active' : '' }}">{{ $viewer->city }}</a>
                @endif
            </nav>
        </div>
    </header>

    @if($users->isNotEmpty())
    <div class="users-browse-grid">
        @include('partials.users-browse-grid-items', ['users' => $users])
    </div>

    <div class="users-browse-pagination">
        {{ $users->links() }}
    </div>
    @else
    @include('partials.empty-state', [
        'class' => 'users-browse-empty',
        'icon' => 'users',
        'title' => __('app.users.empty_title'),
        'text' => __('app.users.empty_text'),
        'ctaUrl' => route('users.index', ['filter' => 'all']),
        'ctaLabel' => __('app.users.discover_badge'),
    ])
    @endif
</div>
@endsection

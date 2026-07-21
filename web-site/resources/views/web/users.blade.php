@extends('layouts.app-with-sidebar')

@php $activeNav = 'users'; @endphp

@section('title', __('app.nav.users') . ' — ' . __('app.brand'))

@section('app-content')
@php
    $search = $search ?? '';
    $filters = $filters ?? [];
    $filtersActive = ! empty($filters['active']);
@endphp
<div class="users-browse-page"@if($search !== '' || $filtersActive) data-users-search="1"@endif>
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
            <form class="users-browse-search users-browse-filters" method="get" action="{{ route('users.index') }}" role="search">
                <label class="users-browse-search__label" for="users-search-q">{{ __('app.users.search_label') }}</label>
                <div class="users-browse-search__row">
                    <input
                        id="users-search-q"
                        class="users-browse-search__input"
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="{{ __('app.users.search_placeholder') }}"
                        maxlength="80"
                        autocomplete="off"
                        enterkeyhint="search"
                    >
                    <button type="submit" class="btn btn-primary users-browse-search__btn">
                        {{ __('app.users.search_button') }}
                    </button>
                    @if($filtersActive)
                        <a href="{{ route('users.index') }}" class="users-browse-search__clear">{{ __('app.users.search_clear') }}</a>
                    @endif
                </div>
                <div class="users-browse-filter-grid">
                    <label>
                        <span>Yaş min</span>
                        <input type="number" name="age_min" min="18" max="80" value="{{ $filters['age_min'] ?? '' }}" placeholder="18">
                    </label>
                    <label>
                        <span>Yaş max</span>
                        <input type="number" name="age_max" min="18" max="80" value="{{ $filters['age_max'] ?? '' }}" placeholder="45">
                    </label>
                    <label>
                        <span>Şehir</span>
                        <input type="text" name="city" value="{{ $filters['city'] ?? '' }}" placeholder="İstanbul" maxlength="80">
                    </label>
                    <label>
                        <span>İlçe</span>
                        <input type="text" name="district" value="{{ $filters['district'] ?? '' }}" placeholder="Kadıköy" maxlength="80">
                    </label>
                    <label>
                        <span>İlişki durumu</span>
                        <select name="relationship_status">
                            <option value="">Tümü</option>
                            @foreach(($relationshipStatuses ?? []) as $key => $meta)
                                <option value="{{ $key }}" @selected(($filters['relationship_status'] ?? '') === $key)>{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>Beklenti</span>
                        <input type="text" name="relationship_expectation" value="{{ $filters['relationship_expectation'] ?? '' }}" placeholder="ciddi ilişki" maxlength="80">
                    </label>
                    <label class="users-browse-filter-check">
                        <input type="checkbox" name="online" value="1" @checked(!empty($filters['online']))>
                        <span>Çevrimiçi</span>
                    </label>
                    <label class="users-browse-filter-check">
                        <input type="checkbox" name="with_photo" value="1" @checked(!empty($filters['with_photo']))>
                        <span>Fotoğraflı</span>
                    </label>
                </div>
            </form>
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
        'title' => $filtersActive ? __('app.users.search_empty_title') : __('app.users.empty_title'),
        'text' => $filtersActive ? __('app.users.search_empty_text') : __('app.users.empty_text'),
        'ctaUrl' => route('users.index'),
        'ctaLabel' => __('app.users.discover_badge'),
    ])
    @endif
</div>
@endsection

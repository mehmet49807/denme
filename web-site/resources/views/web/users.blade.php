@extends('layouts.app-with-sidebar')

@php $activeNav = 'users'; @endphp

@section('title', __('app.nav.users') . ' — ' . __('app.brand'))

@section('app-content')
<div class="users-browse-page">
    <header class="users-browse-hero">
        <div class="users-browse-hero-glow" aria-hidden="true"></div>
        <div class="users-browse-hero-inner">
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
            <p class="users-browse-hero-lead">{{ __('app.users.hero_lead') }}</p>
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
    </header>

    {{-- En önde: paket özel çerçeveli önerilen üyeler --}}
    @include('partials.feed-recommended-users', [
        'recommendedUsers' => $recommendedUsers ?? collect(),
        'variant' => 'members',
    ])

    @if($users->isNotEmpty())
    <div class="users-browse-grid">
        @foreach($users as $user)
        <a href="{{ route('users.show', $user->username) }}" class="users-browse-card">
            <div class="users-browse-card-top">
                <div class="users-browse-avatar-ring">
                    <div class="users-browse-avatar">
                        @if($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->username }}" width="72" height="72" loading="lazy" decoding="async">
                        @else
                            <span class="users-browse-initial">{{ strtoupper(substr($user->username, 0, 1)) }}</span>
                        @endif
                    </div>
                </div>
                @if($user->isPremium())
                    <span class="users-browse-premium" title="Premium">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                    </span>
                @endif
            </div>
            <div class="users-browse-meta">
                <strong class="users-browse-name">{{ $user->username }}</strong>
                @if($user->city)
                <span class="users-browse-location">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    {{ $user->city }}{{ $user->district ? ', '.$user->district : '' }}
                </span>
                @endif
                <span class="users-browse-posts">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    {{ $user->posts_count }} {{ __('app.users.posts_label') }}
                </span>
            </div>
            <span class="users-browse-cta">{{ __('app.users.view_profile') }}</span>
        </a>
        @endforeach
    </div>

    <div class="users-browse-pagination">
        {{ $users->links() }}
    </div>
    @else
    <div class="users-browse-empty">
        <div class="users-browse-empty-icon" aria-hidden="true">
            <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <h2>{{ __('app.users.empty_title') }}</h2>
        <p>{{ __('app.users.empty_text') }}</p>
    </div>
    @endif
</div>
@endsection

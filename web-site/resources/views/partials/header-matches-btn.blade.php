<a
    href="{{ route('matches.index') }}"
    class="header-matches-btn {{ request()->routeIs('matches.index') ? 'header-matches-btn--active' : '' }}"
>
    <span class="header-matches-btn-icon" aria-hidden="true">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
        </svg>
    </span>
    <span class="header-matches-btn-label">{{ __('app.nav.matches') }}</span>
</a>

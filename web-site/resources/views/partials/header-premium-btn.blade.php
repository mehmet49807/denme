<a href="{{ route('premium') }}" class="header-premium-btn {{ request()->routeIs('premium') ? 'header-premium-btn--active' : '' }}">
    <span class="header-premium-btn-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 19h20"/>
            <path d="M4 19V9l4 3 4-6 4 6 4-3v10"/>
        </svg>
    </span>
    <span class="header-premium-btn-label">Premium</span>
</a>

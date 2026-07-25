@php
    $hasMatchActivity = (bool) ($headerMatchesHasActivity ?? false);
@endphp
<a
    href="{{ route('matches.index') }}"
    class="header-matches-btn {{ request()->routeIs('matches.index') ? 'header-matches-btn--active' : '' }} {{ $hasMatchActivity ? 'header-matches-btn--filled' : 'header-matches-btn--empty' }}"
    @if($hasMatchActivity) title="Eşleşme veya beğeni var" @else title="Henüz eşleşme veya beğeni yok" @endif
>
    <span class="header-matches-btn-icon" aria-hidden="true">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
        </svg>
    </span>
    <span class="header-matches-btn-label">{{ __('app.nav.matches') }}</span>
</a>

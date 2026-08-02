@if($user->showsTrustBadge())
@php
    $size = $size ?? 'md';
    $tickUid = 'gktrust-'.($user->id ?? 'x').'-'.$size;
@endphp
<span class="trust-badge trust-badge--{{ $size }} trust-badge--verified" title="Doğrulanmış üye" aria-label="Doğrulanmış üye">
    <span class="trust-badge__glow" aria-hidden="true"></span>
    <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <defs>
            <linearGradient id="{{ $tickUid }}-ring" x1="4" y1="2" x2="24" y2="26" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#93C5FD"/>
                <stop offset="48%" stop-color="#3B82F6"/>
                <stop offset="100%" stop-color="#1D4ED8"/>
            </linearGradient>
            <linearGradient id="{{ $tickUid }}-core" x1="6" y1="5" x2="22" y2="24" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#2563EB"/>
                <stop offset="100%" stop-color="#1E40AF"/>
            </linearGradient>
            <linearGradient id="{{ $tickUid }}-shine" x1="7" y1="5" x2="18" y2="16" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#fff" stop-opacity="0.85"/>
                <stop offset="100%" stop-color="#fff" stop-opacity="0"/>
            </linearGradient>
        </defs>
        <circle cx="14" cy="14" r="12.2" fill="url(#{{ $tickUid }}-ring)"/>
        <circle cx="14" cy="14" r="9.4" fill="url(#{{ $tickUid }}-core)"/>
        <circle cx="14" cy="14" r="9.4" fill="url(#{{ $tickUid }}-shine)"/>
        <path
            d="M8.6 14.35l3.05 3.05 7.2-7.45"
            stroke="#fff"
            stroke-width="2.35"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
</span>
@elseif($user->showsSafetyBadge() && ! $user->showsPremiumVerifiedTick())
@php
    $size = $size ?? 'md';
    $tickUid = 'gksafe-'.($user->id ?? 'x').'-'.$size;
@endphp
<span class="safety-badge safety-badge--{{ $size }} safety-badge--verified" title="Güvenli üye" aria-label="Güvenli üye">
    <span class="safety-badge__glow" aria-hidden="true"></span>
    <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <defs>
            <linearGradient id="{{ $tickUid }}-ring" x1="4" y1="2" x2="24" y2="26" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#93C5FD"/>
                <stop offset="48%" stop-color="#3B82F6"/>
                <stop offset="100%" stop-color="#1D4ED8"/>
            </linearGradient>
            <linearGradient id="{{ $tickUid }}-core" x1="6" y1="5" x2="22" y2="24" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#2563EB"/>
                <stop offset="100%" stop-color="#1E40AF"/>
            </linearGradient>
            <linearGradient id="{{ $tickUid }}-shine" x1="7" y1="5" x2="18" y2="16" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#fff" stop-opacity="0.85"/>
                <stop offset="100%" stop-color="#fff" stop-opacity="0"/>
            </linearGradient>
        </defs>
        <circle cx="14" cy="14" r="12.2" fill="url(#{{ $tickUid }}-ring)"/>
        <circle cx="14" cy="14" r="9.4" fill="url(#{{ $tickUid }}-core)"/>
        <circle cx="14" cy="14" r="9.4" fill="url(#{{ $tickUid }}-shine)"/>
        <path
            d="M8.6 14.35l3.05 3.05 7.2-7.45"
            stroke="#fff"
            stroke-width="2.35"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
</span>
@endif

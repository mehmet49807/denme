@if($user->showsTrustBadge())
@php $size = $size ?? 'md'; @endphp
<span class="trust-badge trust-badge--{{ $size }} trust-badge--premium" title="Doğrulanmış üye" aria-label="Doğrulanmış üye">
    <span class="trust-badge__glow" aria-hidden="true"></span>
    <svg viewBox="0 0 28 28" fill="none" aria-hidden="true">
        <defs>
            <linearGradient id="gkTrustRing-{{ $size }}" x1="3" y1="2" x2="25" y2="26" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#5EEAD4"/>
                <stop offset="50%" stop-color="#0D9488"/>
                <stop offset="100%" stop-color="#FBBF24"/>
            </linearGradient>
            <linearGradient id="gkTrustCore-{{ $size }}" x1="7" y1="6" x2="22" y2="23" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#14B8A6"/>
                <stop offset="100%" stop-color="#0F766E"/>
            </linearGradient>
        </defs>
        <path d="M14 2.4l2.7 2.5 3.6-.2.9 3.5 3.2 1.6-1.4 3.3.8 3.5-3.4 1.5-1.4 3.5-3.5-.9L14 25.6l-1.5-3.4-3.5.9-1.4-3.5-3.4-1.5.8-3.5L4 8.3l3.2-1.6.9-3.5 3.6.2L14 2.4z" fill="url(#gkTrustRing-{{ $size }})"/>
        <circle cx="14" cy="14" r="7.2" fill="url(#gkTrustCore-{{ $size }})"/>
        <path d="M10.1 14.15l2.35 2.35 5.1-5.2" stroke="#fff" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</span>
@elseif($user->showsSafetyBadge() && ! $user->showsPremiumVerifiedTick())
@php $size = $size ?? 'md'; @endphp
<span class="safety-badge safety-badge--{{ $size }} safety-badge--premium" title="Güvenli üye" aria-label="Güvenli üye">
    <span class="safety-badge__glow" aria-hidden="true"></span>
    <svg viewBox="0 0 28 28" fill="none" aria-hidden="true">
        <defs>
            <linearGradient id="gkSafeRing-{{ $size }}" x1="4" y1="2" x2="24" y2="26" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#F9A8D4"/>
                <stop offset="45%" stop-color="#A78BFA"/>
                <stop offset="100%" stop-color="#FBBF24"/>
            </linearGradient>
            <linearGradient id="gkSafeCore-{{ $size }}" x1="7" y1="5" x2="21" y2="24" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#C084FC"/>
                <stop offset="100%" stop-color="#7C3AED"/>
            </linearGradient>
        </defs>
        <path d="M14 3.2l8 3.4v6.2c0 5.1-3.4 8.9-8 10.3-4.6-1.4-8-5.2-8-10.3V6.6l8-3.4z" fill="url(#gkSafeRing-{{ $size }})"/>
        <path d="M14 5.4l5.8 2.5v4.6c0 3.7-2.5 6.5-5.8 7.6-3.3-1.1-5.8-3.9-5.8-7.6V7.9L14 5.4z" fill="url(#gkSafeCore-{{ $size }})"/>
        <path d="M10.4 14.1l2.2 2.2 4.6-4.7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</span>
@endif

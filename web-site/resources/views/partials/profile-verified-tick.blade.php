@if($user->showsPremiumVerifiedTick())
@php
    $size = $size ?? 'md';
    $pkg = method_exists($user, 'activePackageType') ? $user->activePackageType() : null;
    $tone = in_array($pkg, ['pro', 'gold', 'platinum'], true) ? $pkg : 'premium';
    $tickUid = 'gkvt-'.($user->id ?? 'x').'-'.$size.'-'.substr(md5((string) ($user->id ?? uniqid('', true)).$tone.$size), 0, 6);
@endphp
<span
    class="profile-verified-tick profile-verified-tick--{{ $size }} profile-verified-tick--{{ $tone }}"
    title="{{ __('app.premium.verified') }}"
    aria-label="{{ __('app.premium.verified') }}"
>
    <span class="profile-verified-tick__glow" aria-hidden="true"></span>
    <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <defs>
            <linearGradient id="{{ $tickUid }}-ring" x1="4" y1="2" x2="24" y2="26" gradientUnits="userSpaceOnUse">
                <stop class="gk-tick-stop-a" offset="0%"/>
                <stop class="gk-tick-stop-b" offset="48%"/>
                <stop class="gk-tick-stop-c" offset="100%"/>
            </linearGradient>
            <linearGradient id="{{ $tickUid }}-core" x1="6" y1="5" x2="22" y2="24" gradientUnits="userSpaceOnUse">
                <stop class="gk-tick-core-a" offset="0%"/>
                <stop class="gk-tick-core-b" offset="100%"/>
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

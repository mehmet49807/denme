@if($user->showsPremiumVerifiedTick())
@php $size = $size ?? 'md'; @endphp
<span class="profile-verified-tick profile-verified-tick--{{ $size }}" title="{{ __('app.premium.verified') }}" aria-label="{{ __('app.premium.verified') }}">
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <circle cx="12" cy="12" r="10" fill="#1D9BF0"/>
        <path d="M7.5 12.2l2.4 2.4 6.6-6.8" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</span>
@endif

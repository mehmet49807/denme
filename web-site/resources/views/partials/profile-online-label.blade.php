@if($user->isOnline())
@php
    $compact = !empty($compact);
@endphp
<span
    class="profile-online-btn {{ $compact ? 'profile-online-btn--compact' : '' }}"
    role="status"
    aria-label="{{ __('app.user.online') }}"
>
    <span class="profile-online-btn-dot" aria-hidden="true"></span>
    <span class="profile-online-btn-label">{{ __('app.user.online') }}</span>
</span>
@endif

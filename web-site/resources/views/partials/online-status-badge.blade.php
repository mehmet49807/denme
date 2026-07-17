@php
    $size = $size ?? 'md';
    if (! isset($online)) {
        $online = isset($user) ? $user->isOnline() : false;
    }
@endphp
@if($online)
<span class="online-status-dot online-status-dot--{{ $size }}" title="{{ __('app.user.online') }}" aria-label="{{ __('app.user.online') }}"></span>
@endif

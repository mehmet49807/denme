@php
    $user = $user ?? null;
    $size = (int) ($size ?? 28);
    $class = trim('chat-user-avatar ' . ($class ?? ''));
    $href = $href ?? null;
    $showOnline = $showOnline ?? false;
    $initial = strtoupper(substr($user->username ?? '?', 0, 1));
@endphp
<span class="avatar-with-status avatar-with-status--chat" style="width:{{ $size }}px;height:{{ $size }}px;">
@if($href)
    <a href="{{ $href }}" class="{{ $class }}" style="width:{{ $size }}px;height:{{ $size }}px;" @if(!empty($ariaLabel)) aria-label="{{ $ariaLabel }}" @endif>
@else
    <span class="{{ $class }}" style="width:{{ $size }}px;height:{{ $size }}px;" aria-hidden="true">
@endif
        @if(!empty($user?->profile_photo_url))
            <img src="{{ $user->profile_photo_url }}" alt="" width="{{ $size }}" height="{{ $size }}" loading="lazy" decoding="async">
        @else
            <span class="chat-user-avatar-fallback">{{ $initial }}</span>
        @endif
@if($href)
    </a>
@else
    </span>
@endif
    @if($showOnline && $user)
        @include('partials.online-status-badge', ['user' => $user, 'size' => 'xs'])
    @endif
</span>

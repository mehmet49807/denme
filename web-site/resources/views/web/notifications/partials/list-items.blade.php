@foreach($items as $item)
@php
    $createdAt = $item['created_at'] ?? null;
    $createdTs = $createdAt instanceof \DateTimeInterface ? $createdAt->getTimestamp() : 0;
    $type = $item['type'] ?? 'broadcast';
    $iconClass = match ($type) {
        'post_like' => 'notification-icon--like',
        'new_message' => 'notification-icon--message',
        'report_update' => 'notification-icon--report',
        default => 'notification-icon--broadcast',
    };
    $tagClass = match ($type) {
        'post_like' => 'notification-tag--like',
        'new_message' => 'notification-tag--message',
        'report_update' => 'notification-tag--report',
        default => 'notification-tag--broadcast',
    };
@endphp
<li class="notification-item notification-item--{{ $type === 'broadcast' ? 'broadcast' : $type }} {{ $item['is_read'] ? '' : 'notification-item--unread' }}" data-notification-ts="{{ $createdTs }}" data-notification-type="{{ $type }}">
    <div class="notification-icon {{ $iconClass }}" aria-hidden="true">
        @if(($item['type'] ?? '') === 'post_like')
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 20.5s-7.2-4.7-9.2-8.8C1.2 8.2 3.4 5 6.8 5c1.8 0 3.2.9 4 2.1.8-1.2 2.2-2.1 4-2.1 3.4 0 5.6 3.2 4 6.7-2 4.1-9.2 8.8-9.2 8.8z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
        </svg>
        @elseif(($item['type'] ?? '') === 'new_message')
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 6.5A3.5 3.5 0 018.5 3h7A3.5 3.5 0 0119 6.5v7A3.5 3.5 0 0115.5 17H10l-4.5 3.5V17H8.5A3.5 3.5 0 015 13.5v-7z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
        </svg>
        @elseif(($item['type'] ?? '') === 'report_update')
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 5v14l3-1.5L11 19l3-1.5L17 19V5H5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <path d="M9 9h6M9 12h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @else
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 3a5 5 0 00-5 5v3.5L5 14.5h14l-2-3V8a5 5 0 00-5-5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <path d="M10 18a2 2 0 004 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @endif
    </div>
    <div class="notification-body">
        <div class="notification-top">
            <span class="notification-tag {{ $tagClass }}">
                @if($type === 'post_like')
                    {{ __('app.notifications.like') }}
                @elseif($type === 'new_message')
                    {{ __('app.notifications.message') }}
                @elseif($type === 'report_update')
                    {{ __('app.notifications.report') }}
                @else
                    {{ __('app.notifications.broadcast') }}
                @endif
            </span>
            @if(!empty($item['created_at']))
            <time datetime="{{ $item['created_at'] instanceof \DateTimeInterface ? $item['created_at']->format('c') : '' }}">
                {{ $item['created_at'] instanceof \DateTimeInterface ? $item['created_at']->format('d.m.Y H:i') : '' }}
            </time>
            @endif
        </div>
        <h2 class="notification-title">{{ $item['title'] }}</h2>
        <p class="notification-text">
            @if(($item['type'] ?? '') === 'post_like' && !empty($item['actor_username']) && !empty($item['profile_url']))
                <a href="{{ $item['profile_url'] }}" class="notification-actor-link">{{ $item['actor_username'] }}</a>
                {{ __('app.notifications.liked_post') }}
            @elseif(($item['type'] ?? '') === 'new_message' && !empty($item['actor_username']))
                <a href="{{ $item['profile_url'] ?? '#' }}" class="notification-actor-link">{{ $item['actor_username'] }}</a>
                {{ __('app.notifications.sent_message') }}
            @else
                {{ $item['message_text'] }}
            @endif
        </p>
        @if(($item['type'] ?? '') === 'post_like')
        <a href="{{ route('profile') }}" class="notification-action-link">{{ __('app.notifications.go_posts') }}</a>
        @elseif(($item['type'] ?? '') === 'new_message' && !empty($item['messages_url']))
        <a href="{{ $item['messages_url'] }}" class="notification-action-link">{{ __('app.notifications.open_message') }}</a>
        @endif
    </div>
</li>
@endforeach


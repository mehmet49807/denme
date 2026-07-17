@if($conversations->isNotEmpty())
<ul class="conversation-list">
    @foreach($conversations as $conversation)
    @php
        $user = $conversation['user'];
        $isActive = isset($activeUsername) && $activeUsername === $user->username;
    @endphp
    <li>
        <a href="{{ route('messages.show', $user->username) }}" class="conversation-item {{ $conversation['unread_count'] > 0 ? 'conversation-item--unread' : '' }} {{ $isActive ? 'conversation-item--active' : '' }}">
            <div class="conversation-avatar">
                @if($user->profile_photo_url)
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->username }}" width="56" height="56" loading="lazy" decoding="async">
                @else
                    {{ strtoupper(substr($user->username, 0, 1)) }}
                @endif
                @include('partials.online-status-badge', ['user' => $user, 'size' => 'sm'])
            </div>
            <div class="conversation-body">
                <div class="conversation-top">
                    <span class="conversation-name">{{ $user->username }}</span>
                    @if($conversation['last_message_at'])
                        <time class="conversation-time" datetime="{{ $conversation['last_message_at']->toIso8601String() }}">
                            {{ $conversation['last_message_at']->format('d.m.Y H:i') }}
                        </time>
                    @endif
                </div>
                <p class="conversation-preview">
                    @if($conversation['last_sender_name'])
                        <span class="conversation-sender">{{ $conversation['last_sender_name'] }}:</span>
                    @endif
                    {{ Str::limit($conversation['last_message'], 70) }}
                </p>
            </div>
            @if($conversation['unread_count'] > 0)
                <span class="conversation-badge" aria-label="{{ __('app.messages.unread', ['count' => $conversation['unread_count']]) }}">{{ $conversation['unread_count'] }}</span>
            @endif
        </a>
    </li>
    @endforeach
</ul>
@else
<div class="messages-empty">
    <p>{{ __('app.messages.empty_list') }}</p>
    <p class="messages-empty-hint">{{ __('app.messages.empty_hint') }}</p>
    <a href="{{ route('feed') }}" class="btn btn-primary btn-sm">{{ __('app.messages.go_feed') }}</a>
</div>
@endif

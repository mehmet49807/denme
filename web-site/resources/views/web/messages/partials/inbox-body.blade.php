@if($conversations->isNotEmpty())
<ul class="conversation-list" data-inbox-swipe-list>
    @foreach($conversations as $conversation)
    @php
        $user = $conversation['user'];
        $isActive = isset($activeUsername) && $activeUsername === $user->username;
    @endphp
    <li class="conversation-row" data-username="{{ $user->username }}" data-swipe-row>
        <div class="conversation-swipe-rail" aria-hidden="true">
            <form
                method="POST"
                action="{{ route('messages.block', $user->username) }}"
                class="conversation-swipe-block-form"
                data-inbox-block
                data-confirm="{{ __('app.messages.block_confirm', ['name' => $user->username]) }}"
            >
                @csrf
                <button type="submit" class="conversation-swipe-action conversation-swipe-action--block" tabindex="-1">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.75"/>
                        <path d="M5.5 5.5l13 13" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    </svg>
                    <span>{{ __('app.messages.block') }}</span>
                </button>
            </form>
            <button
                type="button"
                class="conversation-swipe-action conversation-swipe-action--delete"
                data-inbox-clear
                data-clear-url="{{ route('messages.clear', $user->username) }}"
                data-confirm="{{ __('app.messages.clear_confirm') }}"
                data-failed="{{ __('app.messages.clear_failed') }}"
                tabindex="-1"
            >
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 7h12M9 7V5h6v2M10 11v6M14 11v6M8 7l1 12h6l1-12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>{{ __('app.messages.delete') }}</span>
            </button>
        </div>
        <div class="conversation-swipe-front">
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
        </div>
    </li>
    @endforeach
</ul>
@else
@include('partials.empty-state', [
    'class' => 'messages-empty',
    'icon' => 'messages',
    'title' => __('app.messages.empty_list'),
    'text' => __('app.messages.empty_hint'),
    'ctaUrl' => route('users.index'),
    'ctaLabel' => 'Üyeleri keşfet',
])
@endif

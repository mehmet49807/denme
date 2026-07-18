@if($conversations->isNotEmpty())
<ul class="conversation-list">
    @foreach($conversations as $conversation)
    @php
        $user = $conversation['user'];
        $isActive = isset($activeUsername) && $activeUsername === $user->username;
    @endphp
    <li class="conversation-row" data-username="{{ $user->username }}">
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
        <div class="conversation-actions" role="group" aria-label="{{ __('app.messages.title') }}">
            <button
                type="button"
                class="conversation-action conversation-action--delete"
                data-inbox-clear
                data-clear-url="{{ route('messages.clear', $user->username) }}"
                data-confirm="{{ __('app.messages.clear_confirm') }}"
                data-failed="{{ __('app.messages.clear_failed') }}"
                title="{{ __('app.messages.clear_chat') }}"
                aria-label="{{ __('app.messages.clear_chat') }}"
            >
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 7h12M9 7V5h6v2M10 11v6M14 11v6M8 7l1 12h6l1-12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>{{ __('app.messages.delete') }}</span>
            </button>
            <form
                method="POST"
                action="{{ route('messages.block', $user->username) }}"
                class="conversation-block-form"
                data-inbox-block
                data-confirm="{{ __('app.messages.block_confirm', ['name' => $user->username]) }}"
            >
                @csrf
                <button
                    type="submit"
                    class="conversation-action conversation-action--block"
                    title="{{ __('app.messages.block_title', ['name' => $user->username]) }}"
                    aria-label="{{ __('app.messages.block_title', ['name' => $user->username]) }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.75"/>
                        <path d="M5.5 5.5l13 13" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    </svg>
                    <span>{{ __('app.messages.block') }}</span>
                </button>
            </form>
        </div>
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

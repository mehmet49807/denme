@php
    $isSent = $message->sender_id === $viewer->id;
    $sender = $isSent ? $viewer : $partner;
    $isEmojiOnly = \App\Support\ChatMessageHelper::isEmojiOnly($message->message_text ?? '');
    $sentAt = $message->created_at;
@endphp
<div class="chat-msg {{ $isSent ? 'chat-msg--sent' : 'chat-msg--received' }}" data-message-id="{{ $message->id }}">
    @if(!$isSent)
        @include('partials.chat-user-avatar', [
            'user' => $partner,
            'size' => 28,
            'href' => route('users.show', $partner->username),
            'ariaLabel' => $partner->username,
            'showOnline' => true,
        ])
    @endif
    <div class="chat-msg-body">
        <div class="chat-bubble {{ $isSent ? 'chat-bubble--sent' : 'chat-bubble--received' }} {{ $isEmojiOnly ? 'chat-bubble--emoji' : '' }}">
            <p class="chat-bubble-text {{ $isEmojiOnly ? 'chat-bubble-text--emoji' : '' }}">{{ $message->message_text }}</p>
            @if($sentAt)
            <time class="chat-bubble-time" datetime="{{ $sentAt->toIso8601String() }}">
                {{ $sentAt->format('d.m.Y H:i') }}
            </time>
            @endif
        </div>
        <button type="button" class="chat-msg-delete" data-delete-message="{{ $message->id }}" aria-label="{{ __('app.messages.delete') }}" title="{{ __('app.messages.delete') }}">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 7h12M9 7V5h6v2M10 11v6M14 11v6M8 7l1 12h6l1-12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>
    @if($isSent)
        @include('partials.chat-user-avatar', [
            'user' => $viewer,
            'size' => 28,
            'href' => route('profile'),
            'ariaLabel' => __('app.messages.you'),
            'showOnline' => true,
        ])
    @endif
</div>

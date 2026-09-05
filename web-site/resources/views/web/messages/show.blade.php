@extends('layouts.app-with-sidebar')

@php $activeNav = 'messages'; @endphp

@section('title', __('app.messages.with_user', ['name' => $partner->username]))

@push('head-meta')
<meta name="inbox-poll-url" content="{{ route('messages.inbox.poll', ['active' => $partner->username]) }}">
@endpush

@section('app-content')
<div class="dm-shell dm-shell--thread-open">
    @include('web.messages.partials.dm-inbox', [
        'conversations' => $conversations,
        'activeUsername' => $partner->username,
    ])

    <section class="dm-thread">
        <div class="chat-page">
            <header class="chat-header">
                <a href="{{ route('messages.index') }}" class="chat-back dm-back-mobile" aria-label="{{ __('app.messages.back') }}">←</a>
                <a href="{{ route('users.show', $partner->username) }}" class="chat-partner">
                    @php $_partnerPkg = method_exists($partner, 'activePackageType') ? $partner->activePackageType() : null; @endphp
                    <div class="chat-partner-avatar{{ in_array($_partnerPkg, ['pro','gold','platinum']) ? ' is-premium-'.$_partnerPkg : '' }}">
                        @if($partner->profile_photo_url)
                            <img src="{{ $partner->profile_photo_url }}" alt="" width="40" height="40" loading="lazy" decoding="async">
                        @else
                            <span class="chat-partner-avatar-fallback">{{ strtoupper(substr($partner->username, 0, 1)) }}</span>
                        @endif
                        @include('partials.online-status-badge', ['user' => $partner, 'size' => 'sm'])
                    </div>
                    <div class="chat-partner-meta">
                        <span class="chat-partner-name">
                            {{ $partner->username }}
                            @include('partials.profile-verified-tick', ['user' => $partner, 'size' => 'sm'])
                        </span>
                        <div class="chat-partner-sub">
                            @include('partials.profile-online-label', ['user' => $partner, 'compact' => true])
                            @if($partner->city || $partner->district)
                            <span class="chat-partner-location">
                                {{ collect([$partner->city, $partner->district])->filter()->implode(' — ') }}
                            </span>
                            @endif
                        </div>
                    </div>
                </a>
            </header>

            @if(session('success'))
                <p class="chat-flash-success">{{ session('success') }}</p>
            @endif

            <div class="chat-messages" id="chat-messages">
                @forelse($messages as $message)
                    @include('partials.chat-message-bubble', [
                        'message' => $message,
                        'viewer' => $viewer,
                        'partner' => $partner,
                    ])
                @empty
                <p class="chat-empty">{{ __('app.messages.empty') }}</p>
                @endforelse
            </div>

            <div class="chat-typing" id="chatTyping" hidden aria-live="polite">
                @include('partials.chat-user-avatar', [
                    'user' => $partner,
                    'size' => 28,
                    'href' => route('users.show', $partner->username),
                ])
                <div class="chat-typing-bubble">
                    <span class="chat-typing-dots" aria-hidden="true"><span></span><span></span><span></span></span>
                    <span class="chat-typing-label" id="chatTypingLabel"></span>
                </div>
            </div>

            @if($viewer->canSendMessages())
            @php
                $isFirstMessage = $messages->isEmpty();
                $quickMessages = \App\Support\QuickMessages::forThread($isFirstMessage);
            @endphp
            @include('partials.chat-safety-tip', ['isFirstMessage' => $isFirstMessage])
            <div class="chat-greetings chat-quick-replies" id="chatGreetings" aria-label="{{ __('app.messages.quick_replies') }}">
                <p class="chat-greetings-label">{{ __('app.messages.quick_replies') }}</p>
                <div class="chat-greetings-list">
                    @foreach($quickMessages as $quick)
                        <button type="button" class="chat-greeting-chip" data-greeting="{{ $quick }}">{{ \Illuminate\Support\Str::limit($quick, 36) }}</button>
                    @endforeach
                </div>
            </div>
            <form method="POST" action="{{ route('messages.store', $partner->username) }}" class="chat-compose" id="chatComposeForm" enctype="multipart/form-data">
                @csrf
                @include('partials.chat-emoji-picker')
                <div class="chat-compose-row">
                    <label class="chat-attach-btn" title="Görsel ekle">
                        <input type="file" name="attachment" accept="image/jpeg,image/png,image/gif,image/webp,audio/*" hidden>
                        <span aria-hidden="true">📎</span>
                    </label>
                    <button type="button" class="chat-emoji-toggle" id="chatEmojiToggle" aria-expanded="false" aria-controls="chatEmojiPanel" title="{{ __('app.messages.emoji_send') }}">
                        <span class="chat-emoji-toggle-icon" aria-hidden="true">😊</span>
                    </button>
                    <label for="message_text" class="sr-only">{{ __('app.messages.label') }}</label>
                    <textarea id="message_text" name="message_text" class="chat-input {{ $errors->has('message_text') ? 'chat-input--error' : '' }}" rows="1" maxlength="2000" placeholder="{{ __('app.messages.placeholder') }}">{{ old('message_text') }}</textarea>
                    <button type="submit" class="chat-send" aria-label="{{ __('app.messages.send') }}">
                        <span class="chat-send-label">{{ __('app.messages.send') }}</span>
                    </button>
                </div>
                @error('message_text') <small class="form-error chat-error">{{ $message }}</small> @enderror
                @error('attachment') <small class="form-error chat-error">{{ $message }}</small> @enderror
            </form>
            <script>
            (function () {
                document.querySelectorAll('[data-greeting]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var input = document.getElementById('message_text');
                        if (input) {
                            input.value = btn.getAttribute('data-greeting') || '';
                            input.focus();
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });
                });
            })();
            </script>
            @else
            <div class="chat-compose chat-compose--locked">
                <div class="premium-feed-banner premium-feed-banner--compact chat-premium-banner">
                    @if($viewer->isOnTrial())
                        <p>{{ __('app.messages.premium_hint_trial', ['hours' => $viewer->trialHoursRemaining()]) }}</p>
                        <a href="{{ route('premium') }}#premium-packages" class="btn btn-outline btn-sm">Paketleri gör</a>
                    @else
                        <p>{{ __('app.messages.premium_hint') }}</p>
                        <a href="{{ route('premium') }}#premium-packages" class="btn btn-primary btn-sm">{{ __('app.common.review') }}</a>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </section>
</div>

<script>
window.__gk_i18n = {!! json_encode([
    'you' => __('app.messages.you'),
    'now' => __('app.common.now'),
    'failed' => __('app.messages.failed'),
    'typing' => __('app.messages.typing', ['name' => $partner->username]),
    'empty' => __('app.messages.empty'),
], JSON_UNESCAPED_UNICODE) !!};
window.__gk_chat = {!! json_encode([
    'typingPingUrl' => route('messages.typing.ping', $partner->username),
    'typingStatusUrl' => route('messages.typing.status', $partner->username),
    'messagesPollUrl' => route('messages.poll', $partner->username),
    'viewerId' => $viewer->id,
    'partnerName' => $partner->username,
    'lastMessageId' => (int) ($messages->last()?->id ?? 0),
], JSON_UNESCAPED_UNICODE) !!};
</script>
@include('partials.asset', ['path' => 'js/chat.min.js', 'defer' => true])
@endsection

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
                    <div class="chat-partner-avatar">
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
                <div class="chat-header-actions">
                    <button
                        type="button"
                        class="chat-clear-btn"
                        id="chatClearBtn"
                        title="{{ __('app.messages.clear_chat') }}"
                        aria-label="{{ __('app.messages.clear_chat') }}"
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 7h12M9 7V5h6v2M10 11v6M14 11v6M8 7l1 12h6l1-12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="chat-clear-btn-label">{{ __('app.messages.delete') }}</span>
                    </button>
                    <details class="chat-safety-menu">
                        <summary class="chat-safety-toggle chat-block-btn" title="{{ __('app.messages.block') }}" aria-label="{{ __('app.messages.block') }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.75"/>
                                <path d="M5.5 5.5l13 13" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                            </svg>
                            <span class="chat-block-btn-label">{{ __('app.messages.block') }}</span>
                        </summary>
                        <div class="chat-safety-panel">
                            <form method="POST" action="{{ route('messages.block', $partner->username) }}" data-block-confirm="{{ __('app.messages.block_confirm', ['name' => $partner->username]) }}" class="chat-safety-form" id="chatBlockForm">
                                @csrf
                                <button type="submit" class="chat-safety-action chat-safety-action--danger">{{ __('app.messages.block') }}</button>
                            </form>
                            <form method="POST" action="{{ route('users.report', $partner->username) }}" class="chat-safety-form" id="chatReportForm">
                                @csrf
                                <input type="hidden" name="reason" value="Sohbet içinden hızlı şikayet">
                                <button type="submit" class="chat-safety-action">Şikayet et</button>
                            </form>
                            <a href="{{ route('complaints') }}" class="chat-safety-action">Güvenlik politikası</a>
                        </div>
                    </details>
                </div>
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
                    <textarea
                        id="message_text"
                        name="message_text"
                        class="chat-input {{ $errors->has('message_text') ? 'chat-input--error' : '' }}"
                        rows="1"
                        maxlength="2000"
                        placeholder="{{ __('app.messages.placeholder') }}"
                    >{{ old('message_text') }}</textarea>
                    <button type="submit" class="chat-send" aria-label="{{ __('app.messages.send') }}">
                        <span class="chat-send-label">{{ __('app.messages.send') }}</span>
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 12L20 4l-3 16-5-6-8-2z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
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
                    <p>{{ __('app.messages.premium_hint') }}</p>
                    <a href="{{ route('premium') }}" class="btn btn-primary btn-sm">{{ __('app.common.review') }}</a>
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
    'emojiFailed' => __('app.messages.emoji_failed'),
    'connectionError' => __('app.messages.connection_error'),
    'typing' => __('app.messages.typing', ['name' => $partner->username]),
    'delete' => __('app.messages.delete'),
    'deleteConfirm' => __('app.messages.delete_confirm'),
    'deleteFailed' => __('app.messages.delete_failed'),
    'clearChat' => __('app.messages.clear_chat'),
    'clearConfirm' => __('app.messages.clear_confirm'),
    'clearFailed' => __('app.messages.clear_failed'),
    'empty' => __('app.messages.empty'),
], JSON_UNESCAPED_UNICODE) !!};
window.__gk_chat = {!! json_encode([
    'typingPingUrl' => route('messages.typing.ping', $partner->username),
    'typingStatusUrl' => route('messages.typing.status', $partner->username),
    'messagesPollUrl' => route('messages.poll', $partner->username),
    'deleteMessageUrl' => url('/messages/'.$partner->username),
    'clearChatUrl' => route('messages.clear', $partner->username),
    'viewerId' => $viewer->id,
    'partnerName' => $partner->username,
    'lastMessageId' => (int) ($messages->last()?->id ?? 0),
    'viewerPhotoUrl' => $viewer->profile_photo_url,
    'partnerPhotoUrl' => $partner->profile_photo_url,
    'viewerInitial' => strtoupper(substr($viewer->username, 0, 1)),
    'partnerInitial' => strtoupper(substr($partner->username, 0, 1)),
    'viewerProfileUrl' => route('profile'),
    'partnerProfileUrl' => route('users.show', $partner->username),
], JSON_UNESCAPED_UNICODE) !!};
(function () {
    var blockForm = document.getElementById('chatBlockForm');
    if (blockForm) {
        blockForm.addEventListener('submit', function (e) {
            var msg = blockForm.getAttribute('data-block-confirm') || '';
            if (!window.confirm(msg)) {
                e.preventDefault();
                return;
            }
            var btn = blockForm.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
        });
    }

    var clearBtn = document.getElementById('chatClearBtn');
    var chatConfig = window.__gk_chat || {};
    var i18n = window.__gk_i18n || {};
    var csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    if (clearBtn && chatConfig.clearChatUrl && csrf) {
        clearBtn.addEventListener('click', function () {
            var confirmMsg = i18n.clearConfirm || 'Bu sohbetteki tüm mesajları silmek istediğinize emin misiniz?';
            if (!window.confirm(confirmMsg)) return;

            clearBtn.disabled = true;
            fetch(chatConfig.clearChatUrl, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).then(function (res) {
                if (!res.ok) throw new Error('clear failed');
                return res.json();
            }).then(function (data) {
                if (!data || data.ok !== true) throw new Error('clear failed');
                window.location.reload();
            }).catch(function () {
                clearBtn.disabled = false;
                window.alert(i18n.clearFailed || 'Sohbet temizlenemedi.');
            });
        });
    }
})();
</script>
<script src="{{ asset('js/chat.js') }}?v=chat-ig-v3"></script>
@endsection

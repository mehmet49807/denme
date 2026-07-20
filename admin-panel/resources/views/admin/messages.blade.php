@extends('layouts.admin')



@section('title', 'Mesaj Denetimi')

@section('lead', 'Platform mesajlarını konuşma bazında inceleyin ve denetleyin.')



@section('content')

<div class="admin-messages-toolbar">

    <div class="admin-messages-stat admin-messages-stat--violet">

        <span class="admin-messages-stat-icon" aria-hidden="true">💬</span>

        <div>

            <strong>{{ $threadPaginator->total() }}</strong>

            <span>konuşma</span>

        </div>

    </div>

    <div class="admin-messages-stat admin-messages-stat--pink">

        <div>

            <strong>{{ $totalMessages }}</strong>

            <span>mesaj · sayfa {{ $threadPaginator->currentPage() }}/{{ $threadPaginator->lastPage() }}</span>

        </div>

    </div>

</div>

<div class="admin-panel admin-panel--glass" style="margin-bottom:1rem">
    <form method="GET" action="{{ route('admin.messages') }}" class="admin-users-filter" role="search">
        <div class="admin-users-filter-field">
            <label for="msg-username">Kullanıcı</label>
            <input type="search" id="msg-username" name="username" value="{{ $filters['username'] ?? request('username') }}" placeholder="kullanıcı adı">
        </div>
        <div class="admin-users-filter-field admin-users-filter-field--grow">
            <label for="msg-keyword">Kelime</label>
            <input type="search" id="msg-keyword" name="keyword" value="{{ $filters['keyword'] ?? request('keyword') }}" placeholder="mesaj içinde ara…">
        </div>
        <div class="admin-users-filter-field">
            <label for="msg-from">Başlangıç</label>
            <input type="date" id="msg-from" name="date_from" value="{{ $filters['date_from'] ?? request('date_from') }}">
        </div>
        <div class="admin-users-filter-field">
            <label for="msg-to">Bitiş</label>
            <input type="date" id="msg-to" name="date_to" value="{{ $filters['date_to'] ?? request('date_to') }}">
        </div>
        <div class="admin-users-filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">Filtrele</button>
            <a href="{{ route('admin.messages') }}" class="btn btn-outline btn-sm">Temizle</a>
        </div>
    </form>
</div>

<div class="admin-panel admin-panel--glass admin-messages-panel">

    <div class="admin-thread-list">

        @forelse($threads as $thread)

            @php

                $userA = $thread['user_a'];

                $userB = $thread['user_b'];

                $idA = $userA->id ?? null;

                $idB = $userB->id ?? null;

                $preview = $thread['messages']->first();

                $previewHasImage = ($preview?->attachment_type ?? null) === 'image' && !empty($preview?->attachment_url);
                $previewText = trim((string) ($preview?->message_text ?? ''));
                $previewLabel = $previewText !== ''
                    ? $previewText
                    : ($previewHasImage ? 'Fotoğraf gönderildi' : '');

            @endphp

            <details class="admin-thread">

                <summary class="admin-thread-summary">

                    <span class="admin-thread-chevron" aria-hidden="true">

                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>

                    </span>



                    <span class="admin-thread-avatars" aria-hidden="true">

                        <span class="admin-thread-avatar admin-thread-avatar--a">

                            @if($userA?->profile_photo_url)

                                <img src="{{ $userA->profile_photo_url }}" alt="" width="34" height="34" loading="lazy" decoding="async">

                            @else

                                {{ strtoupper(substr($userA->username ?? '?', 0, 1)) }}

                            @endif

                        </span>

                        <span class="admin-thread-avatar admin-thread-avatar--b">

                            @if($userB?->profile_photo_url)

                                <img src="{{ $userB->profile_photo_url }}" alt="" width="34" height="34" loading="lazy" decoding="async">

                            @else

                                {{ strtoupper(substr($userB->username ?? '?', 0, 1)) }}

                            @endif

                        </span>

                    </span>



                    <span class="admin-thread-main">

                        <span class="admin-thread-users">

                            <strong>{{ $userA->username ?? '—' }}</strong>

                            <span class="admin-thread-link-icon" aria-hidden="true">

                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2.5 7h9M7 2.5L11.5 7 7 11.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>

                            </span>

                            <strong>{{ $userB->username ?? '—' }}</strong>

                        </span>

                        <span class="admin-thread-preview">{{ Str::limit($previewLabel, 72) ?: '—' }}</span>

                    </span>



                    <span class="admin-thread-meta">

                        <span class="admin-thread-count">{{ $thread['count'] }} mesaj</span>

                        <time>{{ $thread['last_at']?->format('d.m.Y H:i') ?? '—' }}</time>

                    </span>

                </summary>



                <div class="admin-thread-body">

                    <div class="admin-thread-chat">

                        @foreach($thread['messages']->sortBy('created_at') as $msg)

                            @php

                                $isA = $idA && (int) $msg->sender_id === (int) $idA;

                                $sender = $msg->sender;

                                $senderName = $sender->username ?? '—';
                                $messageText = trim((string) ($msg->message_text ?? ''));
                                $hasImageAttachment = ($msg->attachment_type ?? null) === 'image' && !empty($msg->attachment_url);

                            @endphp

                            <article class="admin-chat-msg {{ $isA ? 'admin-chat-msg--a' : 'admin-chat-msg--b' }}">

                                <span class="admin-chat-avatar">

                                    @if($sender?->profile_photo_url)

                                        <img src="{{ $sender->profile_photo_url }}" alt="" width="32" height="32" loading="lazy" decoding="async">

                                    @else

                                        {{ strtoupper(substr($senderName, 0, 1)) }}

                                    @endif

                                </span>

                                <div class="admin-chat-bubble">

                                    <header class="admin-chat-head">

                                        <strong>{{ $senderName }}</strong>

                                        <span>→ {{ $msg->receiver->username ?? '—' }}</span>

                                    </header>

                                    @if($hasImageAttachment)
                                        <a href="{{ $msg->attachment_url }}" class="admin-chat-photo-link" target="_blank" rel="noopener">
                                            <img src="{{ $msg->attachment_url }}" class="admin-chat-photo" alt="Mesajla gönderilen fotoğraf" loading="lazy" decoding="async">
                                        </a>
                                    @endif

                                    @if($messageText !== '')
                                        <p class="admin-chat-text">{{ $messageText }}</p>
                                    @elseif($hasImageAttachment)
                                        <p class="admin-chat-text admin-chat-text--muted">Fotoğraf mesajı</p>
                                    @else
                                        <p class="admin-chat-text admin-chat-text--muted">Boş mesaj</p>
                                    @endif

                                    <footer class="admin-chat-foot">

                                        <time>{{ $msg->created_at?->format('d.m.Y H:i') }}</time>

                                        <span>#{{ $msg->id }}</span>

                                        @if($msg->hidden_for_sender_at || $msg->hidden_for_receiver_at)
                                            <span class="admin-chat-hidden-badge">
                                                @if($msg->hidden_for_sender_at) gönderen sildi @endif
                                                @if($msg->hidden_for_sender_at && $msg->hidden_for_receiver_at) · @endif
                                                @if($msg->hidden_for_receiver_at) alıcı sildi @endif
                                            </span>
                                        @endif

                                    </footer>

                                </div>

                            </article>

                        @endforeach

                    </div>

                </div>

            </details>

        @empty

            <div class="admin-messages-empty">

                <span class="admin-messages-empty-icon" aria-hidden="true">💬</span>

                <p>Henüz mesaj yok.</p>

            </div>

        @endforelse

    </div>

    @if($threadPaginator->hasPages())
        <div class="admin-content-pagination admin-messages-pagination">
            {{ $threadPaginator->links() }}
        </div>
    @endif

</div>

<style>
.admin-chat-photo-link {
    background: rgba(255, 255, 255, .72);
    border: 1px solid rgba(148, 163, 184, .28);
    border-radius: 16px;
    display: inline-block;
    line-height: 0;
    margin: 8px 0 6px;
    overflow: hidden;
}
.admin-chat-photo {
    display: block;
    max-height: 260px;
    max-width: min(320px, 52vw);
    object-fit: cover;
    width: 100%;
}
.admin-chat-text--muted {
    color: #7c8798;
    font-style: italic;
}
@media (max-width: 720px) {
    .admin-chat-photo {
        max-width: min(250px, 68vw);
    }
}
</style>

@endsection


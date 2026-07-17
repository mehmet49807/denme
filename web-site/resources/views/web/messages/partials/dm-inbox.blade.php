<aside class="dm-inbox" aria-label="{{ __('app.messages.title') }}">
    <header class="dm-inbox-header">
        <h1 class="dm-inbox-title">{{ __('app.messages.title') }}</h1>
    </header>
    <div class="dm-inbox-body" id="inboxPollRoot">
        @include('web.messages.partials.inbox-body', [
            'conversations' => $conversations,
            'activeUsername' => $activeUsername ?? null,
        ])
    </div>
</aside>

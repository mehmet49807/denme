<aside class="dm-inbox" aria-label="{{ __('app.messages.title') }}">
    <header class="dm-inbox-header">
        <h1 class="dm-inbox-title">{{ __('app.messages.title') }}</h1>
    </header>
    @if(session('success'))
        <p class="dm-inbox-flash" role="status">{{ session('success') }}</p>
    @endif
    <div class="dm-inbox-hint" id="inboxSwipeHint" data-inbox-swipe-hint>
        <div class="dm-inbox-hint-body">
            <p class="dm-inbox-hint-title">{{ __('app.messages.swipe_hint_title') }}</p>
            <ul class="dm-inbox-hint-list">
                <li>
                    <span class="dm-inbox-hint-chip dm-inbox-hint-chip--delete">{{ __('app.messages.delete') }}</span>
                    <span>{{ __('app.messages.swipe_hint_delete') }}</span>
                </li>
                <li>
                    <span class="dm-inbox-hint-chip dm-inbox-hint-chip--block">{{ __('app.messages.block') }}</span>
                    <span>{{ __('app.messages.swipe_hint_block') }}</span>
                </li>
            </ul>
        </div>
        <button type="button" class="dm-inbox-hint-dismiss" data-inbox-swipe-hint-dismiss aria-label="{{ __('app.common.close') }}">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </button>
    </div>
    <div class="dm-inbox-body" id="inboxPollRoot">
        @include('web.messages.partials.inbox-body', [
            'conversations' => $conversations,
            'activeUsername' => $activeUsername ?? null,
        ])
    </div>
</aside>
@include('web.messages.partials.inbox-actions-script')

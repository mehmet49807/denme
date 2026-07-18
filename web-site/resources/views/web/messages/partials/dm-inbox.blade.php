<aside class="dm-inbox" aria-label="{{ __('app.messages.title') }}">
    <header class="dm-inbox-header">
        <h1 class="dm-inbox-title">{{ __('app.messages.title') }}</h1>
    </header>
    @if(session('success'))
        <p class="dm-inbox-flash" role="status">{{ session('success') }}</p>
    @endif
    <div class="dm-inbox-info-card" id="inboxSwipeHint" data-inbox-swipe-hint role="note">
        <span class="dm-inbox-info-card__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/>
                <path d="M12 10.5v5M12 7.8h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </span>
        <div class="dm-inbox-info-card__body">
            <p class="dm-inbox-info-card__title">{{ __('app.messages.swipe_hint_title') }}</p>
            <div class="dm-inbox-info-card__rows">
                <div class="dm-inbox-info-card__row">
                    <span class="dm-inbox-info-card__arrow" aria-hidden="true">→</span>
                    <span class="dm-inbox-info-card__chip dm-inbox-info-card__chip--block">{{ __('app.messages.block') }}</span>
                    <span class="dm-inbox-info-card__text">{{ __('app.messages.swipe_hint_block') }}</span>
                </div>
                <div class="dm-inbox-info-card__row">
                    <span class="dm-inbox-info-card__arrow" aria-hidden="true">←</span>
                    <span class="dm-inbox-info-card__chip dm-inbox-info-card__chip--delete">{{ __('app.messages.delete') }}</span>
                    <span class="dm-inbox-info-card__text">{{ __('app.messages.swipe_hint_delete') }}</span>
                </div>
            </div>
        </div>
        <button type="button" class="dm-inbox-info-card__dismiss" data-inbox-swipe-hint-dismiss aria-label="{{ __('app.common.close') }}">
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

<div class="feed-toolbar feed-toolbar--premium feed-toolbar--compact">
    <button type="button" class="feed-create-btn feed-create-btn--post" data-open-compose="post" aria-label="{{ __('app.feed.add_post') }}">
        <span class="feed-create-icon feed-create-icon--post" aria-hidden="true">
            @include('partials.theme-icon', ['icon' => 'post'])
        </span>
        <span class="feed-create-label">{{ __('app.feed.post') }}</span>
    </button>
    @if($viewer->canPostStories())
    <button type="button" class="feed-create-btn feed-create-btn--story" data-open-compose="story" aria-label="{{ __('app.feed.add_story') }}">
        <span class="feed-create-icon feed-create-icon--story" aria-hidden="true">
            @include('partials.theme-icon', ['icon' => 'story'])
        </span>
        <span class="feed-create-label">{{ __('app.feed.story') }}</span>
    </button>
    @elseif($viewer->gender === 'male')
    <a href="{{ route('premium') }}#premium-packages" class="feed-create-btn feed-create-btn--premium" aria-label="{{ __('app.nav.premium') }}">
        <span class="feed-create-icon feed-create-icon--premium" aria-hidden="true">
            @include('partials.theme-icon', ['icon' => 'crown'])
        </span>
        <span class="feed-create-label">{{ __('app.nav.premium') }}</span>
    </a>
    @endif
</div>

@if(!empty($showFeedPromoBanner))
    @if($viewer->isOnTrial())
    <div class="premium-feed-banner premium-feed-banner--trial premium-feed-banner--compact {{ $viewer->trialHoursRemaining() <= 24 ? 'premium-feed-banner--urgent' : '' }}">
        <p>
            <strong>{{ __('app.feed.trial_banner') }}</strong>
            {{ __('app.common.days_left', ['count' => $viewer->trialDaysRemaining()]) }}
            · {{ $viewer->trialHoursRemaining() }} saat
            @if($viewer->trialHoursRemaining() <= 24)
                — {{ __('app.feed.trial_ending_soon') }}
            @endif
        </p>
        <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm" data-gk-event="trial_first_message_cta" data-gk-event-label="feed_banner">İlk mesaj</a>
        <a href="{{ route('premium') }}#premium-packages" class="btn btn-outline btn-sm" data-gk-event="trial_cta_click" data-gk-event-label="feed_trial_packages">{{ __('app.common.packages') }}</a>
    </div>
    @elseif($viewer->gender === 'male' && !$viewer->canSendMessages())
    <div class="premium-feed-banner premium-feed-banner--ended premium-feed-banner--compact" data-gk-event="trial_ended_banner_view">
        <div class="premium-feed-banner__copy">
            <strong>{{ __('app.feed.trial_ended_title') }}</strong>
            <p>{{ __('app.feed.trial_ended_lead') }}</p>
        </div>
        <div class="premium-feed-banner__actions">
            <a href="{{ route('premium') }}#premium-packages" class="btn btn-primary btn-sm" data-gk-event="trial_cta_click" data-gk-event-label="feed_locked">{{ __('app.common.review') }}</a>
            <a href="{{ route('matches.index') }}" class="btn btn-outline btn-sm">{{ __('app.nav.matches') }}</a>
        </div>
    </div>
    @elseif($viewer->gender === 'male' && !$viewer->canPostStories())
    <div class="premium-feed-banner premium-feed-banner--compact">
        <p>{{ __('app.premium.stories_lock') }}</p>
        <a href="{{ route('premium') }}#premium-packages" class="btn btn-primary btn-sm" data-gk-event="trial_cta_click" data-gk-event-label="feed_stories_lock">{{ __('app.common.review') }}</a>
    </div>
    @endif
@endif

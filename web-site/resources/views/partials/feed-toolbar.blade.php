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
    <a href="{{ route('premium') }}" class="feed-create-btn feed-create-btn--premium" aria-label="{{ __('app.nav.premium') }}">
        <span class="feed-create-icon feed-create-icon--premium" aria-hidden="true">
            @include('partials.theme-icon', ['icon' => 'crown'])
        </span>
        <span class="feed-create-label">{{ __('app.nav.premium') }}</span>
    </a>
    @endif
</div>

@if($viewer->isOnTrial())
<div class="premium-feed-banner premium-feed-banner--trial premium-feed-banner--compact">
    <p><strong>{{ __('app.feed.trial_banner') }}</strong> {{ __('app.common.days_left', ['count' => $viewer->trialDaysRemaining()]) }}</p>
    <a href="{{ route('premium') }}" class="btn btn-outline btn-sm">{{ __('app.common.packages') }}</a>
</div>
@elseif($viewer->gender === 'male' && !$viewer->canPostStories())
<div class="premium-feed-banner premium-feed-banner--compact">
    <p>{{ __('app.feed.premium_app_hint') }}</p>
    <a href="{{ route('premium') }}" class="btn btn-primary btn-sm">{{ __('app.common.review') }}</a>
</div>
@endif

@extends('layouts.app-with-sidebar')

@php
    $activeNav = 'premium';
    $featuredPackage = 'gold';
    $featureItems = [
        ['icon' => 'camera', 'title' => __('app.premium.feat_stories_title'), 'desc' => __('app.premium.feat_stories_desc'), 'visual' => 'premium-stories.svg'],
        ['icon' => 'eye', 'title' => __('app.premium.feat_who_viewed_title'), 'desc' => __('app.premium.feat_who_viewed_desc'), 'visual' => 'premium-spotlight.svg'],
        ['icon' => 'camera', 'title' => __('app.premium.feat_gallery_title'), 'desc' => __('app.premium.feat_gallery_desc'), 'visual' => 'premium-stories.svg'],
        ['icon' => 'star', 'title' => __('app.premium.feat_boost_title'), 'desc' => __('app.premium.feat_boost_desc'), 'visual' => 'premium-boost.svg'],
        ['icon' => 'heart', 'title' => __('app.premium.feat_likes_title'), 'desc' => __('app.premium.feat_likes_desc'), 'visual' => 'premium-spotlight.svg'],
        ['icon' => 'eye', 'title' => __('app.premium.feat_visibility_title'), 'desc' => __('app.premium.feat_visibility_desc'), 'visual' => 'premium-spotlight.svg'],
    ];
    $packageIcons = ['pro' => 'star', 'gold' => 'crown', 'platinum' => 'bolt'];
@endphp

@section('title', __('app.premium.page_title') . ' — ' . __('app.brand'))

@section('app-content')
<div class="premium-page">
    <section class="premium-hero">
        <div class="premium-hero-glow" aria-hidden="true"></div>
        <div class="premium-hero-grid">
            <div class="premium-hero-inner">
                <span class="premium-hero-badge">
                    @include('partials.theme-icon', ['icon' => 'crown'])
                    Premium
                </span>
                <h1>{{ __('app.premium.hero_title') }}</h1>
                <p class="premium-hero-lead">{{ __('app.premium.hero_lead') }}</p>
                <ul class="premium-hero-perks">
                    <li>@include('partials.theme-icon', ['icon' => 'check']) {{ __('app.premium.perk_stories') }}</li>
                    <li>@include('partials.theme-icon', ['icon' => 'check']) {{ __('app.premium.perk_who_viewed') }}</li>
                    <li>@include('partials.theme-icon', ['icon' => 'check']) {{ __('app.premium.perk_gallery') }}</li>
                    <li>@include('partials.theme-icon', ['icon' => 'check']) {{ __('app.premium.perk_profile') }}</li>
                </ul>
            </div>
            <div class="premium-hero-visual" aria-hidden="true">
                <img src="{{ asset('images/premium-hero-visual.svg') }}" alt="" width="280" height="245" loading="lazy">
            </div>
        </div>
    </section>

    @if($user->gender === 'female')
        <section class="premium-card premium-card--included glass-card">
            <div class="premium-card-visual">
                <img src="{{ asset('images/premium-spotlight.svg') }}" alt="" width="120" height="120" loading="lazy">
            </div>
            <div class="premium-card-copy">
                <div class="premium-card-icon">@include('partials.theme-icon', ['icon' => 'heart'])</div>
                <h2>{{ __('app.premium.female_title') }}</h2>
                <p>{{ __('app.premium.female_lead') }}</p>
                <ul class="premium-included-list">
                    @foreach($features as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
        </section>
    @else
        @if($user->isOnTrial())
            <section class="premium-status premium-status--trial glass-card">
                <div class="premium-status-icon">@include('partials.theme-icon', ['icon' => 'clock'])</div>
                <div class="premium-status-copy">
                    <strong>{{ __('app.premium.trial_status') }}</strong>
                    <p>{{ __('app.premium.trial_until', ['date' => $user->trial_ends_at->format('d.m.Y H:i'), 'days' => $user->trialDaysRemaining()]) }}</p>
                </div>
            </section>
        @elseif($activeSubscription)
            <section class="premium-status premium-status--active glass-card">
                <div class="premium-status-icon">@include('partials.theme-icon', ['icon' => 'crown'])</div>
                <div class="premium-status-copy">
                    <strong>{{ __('app.premium.active_package', ['name' => $packages[$activeSubscription->package_type]['name'] ?? ucfirst($activeSubscription->package_type)]) }}</strong>
                    <p>{{ __('app.premium.valid_until', ['date' => $activeSubscription->expires_at->format('d.m.Y H:i')]) }}</p>
                </div>
            </section>
        @else
            <section class="premium-status premium-status--expired glass-card">
                <div class="premium-status-icon">@include('partials.theme-icon', ['icon' => 'star'])</div>
                <div class="premium-status-copy">
                    <strong>{{ __('app.premium.expired_title') }}</strong>
                    <p>{{ __('app.premium.expired_lead') }}</p>
                </div>
            </section>
        @endif

        <section class="premium-section premium-section--packages">
            <h2 class="premium-section-title">{{ __('app.premium.packages_title') }}</h2>
            <p class="premium-section-sub">{{ __('app.premium.packages_sub') }}</p>
            <div class="premium-packages">
                @foreach($packages as $type => $pkg)
                    @php
                        $isFeatured = $type === $featuredPackage;
                        $isActive = $activeSubscription && $activeSubscription->package_type === $type;
                    @endphp
                    <article class="premium-package-card glass-card {{ $isFeatured ? 'premium-package-card--featured' : '' }} {{ $isActive ? 'premium-package-card--active' : '' }}">
                        @if($isActive)
                            <span class="premium-package-tag premium-package-tag--active">{{ __('app.premium.active_tag') }}</span>
                        @elseif($isFeatured)
                            <span class="premium-package-tag">{{ __('app.premium.most_popular') }}</span>
                        @endif
                        <div class="premium-package-icon premium-package-icon--{{ $type }}">
                            @include('partials.theme-icon', ['icon' => $packageIcons[$type] ?? 'star'])
                        </div>
                        <h3>{{ $pkg['name'] }}</h3>
                        <p class="premium-package-price">
                            {{ number_format($pkg['price_tl'], 0, ',', '.') }}
                            <span>TL</span>
                        </p>
                        <p class="premium-package-duration">{{ __('app.premium.days_access', ['days' => $pkg['duration_days']]) }}</p>
                        <ul class="premium-package-perks">
                            <li>{{ __('app.premium.perk_stories') }}</li>
                            <li>{{ __('app.premium.perk_who_viewed') }}</li>
                            <li>{{ __('app.premium.perk_gallery') }}</li>
                            <li>{{ __('app.premium.perk_profile') }}</li>
                            @if($type === 'gold' || $type === 'platinum')
                                <li>{{ __('app.premium.gold_badge') }}</li>
                            @endif
                            @if($type === 'platinum')
                                <li>{{ __('app.premium.top_visibility') }}</li>
                            @endif
                        </ul>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="premium-section">
            <h2 class="premium-section-title">{{ __('app.premium.features_title') }}</h2>
            <div class="premium-features-grid">
                @foreach($featureItems as $item)
                    <article class="premium-feature glass-card">
                        <div class="premium-feature-top">
                            <span class="premium-feature-icon">@include('partials.theme-icon', ['icon' => $item['icon']])</span>
                            <img class="premium-feature-visual" src="{{ asset('images/'.$item['visual']) }}" alt="" width="72" height="72" loading="lazy">
                        </div>
                        <h3>{{ $item['title'] }}</h3>
                        <p>{{ $item['desc'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="premium-app-cta glass-card">
            <div class="premium-app-cta-visual" aria-hidden="true">
                <img src="{{ asset('images/premium-hero-visual.svg') }}" alt="" width="160" height="140" loading="lazy">
            </div>
            <div class="premium-app-cta-copy">
                <h2>{{ __('app.premium.app_cta_title') }}</h2>
                <p>{{ __('app.premium.app_cta_lead') }}</p>
                @include('partials.store-badges')
            </div>
        </section>
    @endif
</div>
@endsection

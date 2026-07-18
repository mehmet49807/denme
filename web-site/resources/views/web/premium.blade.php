@extends('layouts.app-with-sidebar')

@php
    $activeNav = 'premium';
    $heroPerks = [
        __('app.premium.perk_stories'),
        __('app.premium.perk_who_viewed'),
        __('app.premium.perk_gallery'),
        __('app.premium.perk_profile'),
    ];
    $featuredPackage = $featuredPackage ?? 'gold';
@endphp

@section('title', __('app.premium.page_title') . ' — ' . __('app.brand'))

@push('head')
@include('partials.asset', ['path' => 'css/premium-page.min.css'])
@endpush

@section('app-content')
<div class="premium-theme premium-page">
    <header class="premium-theme-hero">
        <div class="premium-theme-hero__mesh" aria-hidden="true"></div>
        <div class="premium-theme-hero__orb premium-theme-hero__orb--1" aria-hidden="true"></div>
        <div class="premium-theme-hero__orb premium-theme-hero__orb--2" aria-hidden="true"></div>
        <div class="premium-theme-hero__content">
            <div class="premium-theme-hero__medallion" aria-hidden="true">
                @include('partials.theme-icon', ['icon' => 'crown'])
            </div>
            <span class="premium-theme-hero__badge">
                @include('partials.theme-icon', ['icon' => 'sparkles'])
                Premium
            </span>
            <h1 class="premium-theme-hero__title">{{ __('app.premium.hero_title') }}</h1>
            <p class="premium-theme-hero__lead">{{ __('app.premium.hero_lead') }}</p>
            <ul class="premium-theme-hero__perks" aria-label="{{ __('app.premium.features_title') }}">
                @foreach($heroPerks as $perk)
                    <li>@include('partials.theme-icon', ['icon' => 'check']) {{ $perk }}</li>
                @endforeach
            </ul>
            @if($user->gender !== 'female')
                <a href="#premium-packages" class="premium-theme-hero__cta">{{ __('app.premium.packages_title') }}</a>
            @endif
        </div>
    </header>

    @if($user->gender === 'female')
        <section class="premium-theme-included">
            <div class="premium-theme-included__icon" aria-hidden="true">
                @include('partials.theme-icon', ['icon' => 'heart'])
            </div>
            <div>
                <h2>{{ __('app.premium.female_title') }}</h2>
                <p>{{ __('app.premium.female_lead') }}</p>
                <ul class="premium-theme-included__list">
                    @foreach($features as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
        </section>
    @else
        @if($user->isOnTrial())
            <section class="premium-theme-status premium-theme-status--trial">
                <div class="premium-theme-status__icon">@include('partials.theme-icon', ['icon' => 'clock'])</div>
                <div class="premium-theme-status__copy">
                    <strong>{{ __('app.premium.trial_status') }}</strong>
                    <p>{{ __('app.premium.trial_until', ['date' => $user->trial_ends_at->format('d.m.Y H:i'), 'days' => $user->trialDaysRemaining()]) }}</p>
                </div>
            </section>
        @elseif($activeSubscription)
            <section class="premium-theme-status premium-theme-status--active">
                <div class="premium-theme-status__icon">@include('partials.theme-icon', ['icon' => 'crown'])</div>
                <div class="premium-theme-status__copy">
                    <strong>{{ __('app.premium.active_package', ['name' => $packages[$activeSubscription->package_type]['name'] ?? ucfirst($activeSubscription->package_type)]) }}</strong>
                    <p>{{ __('app.premium.valid_until', ['date' => $activeSubscription->expires_at->format('d.m.Y H:i')]) }}</p>
                </div>
            </section>
        @else
            <section class="premium-theme-status premium-theme-status--expired">
                <div class="premium-theme-status__icon">@include('partials.theme-icon', ['icon' => 'star'])</div>
                <div class="premium-theme-status__copy">
                    <strong>{{ __('app.premium.expired_title') }}</strong>
                    <p>{{ __('app.premium.expired_lead') }}</p>
                </div>
            </section>
        @endif

        <section class="premium-theme-section premium-theme-section--packages" id="premium-packages">
            <header class="premium-theme-section__head">
                <h2 class="premium-theme-section__title">{{ __('app.premium.packages_title') }}</h2>
                <p class="premium-theme-section__sub">{{ __('app.premium.packages_sub') }}</p>
            </header>
            <div class="premium-pkg-grid">
                @foreach($packages as $type => $pkg)
                    @php
                        $isFeatured = $type === $featuredPackage;
                        $isActive = $activeSubscription && $activeSubscription->package_type === $type;
                        $perDay = (int) round($pkg['price_tl'] / max(1, $pkg['duration_days']));
                    @endphp
                    <article class="premium-pkg premium-pkg--{{ $type }} {{ $isFeatured ? 'premium-pkg--featured' : '' }} {{ $isActive ? 'premium-pkg--active' : '' }}" style="--pkg-badge-from: {{ $pkg['gradient_from'] ?? '#7c3aed' }}; --pkg-badge-to: {{ $pkg['gradient_to'] ?? '#db2777' }};">
                        @if($isActive)
                            <span class="premium-pkg__flag premium-pkg__flag--active">{{ __('app.premium.active_tag') }}</span>
                        @elseif($isFeatured)
                            <span class="premium-pkg__flag">{{ __('app.premium.most_popular') }}</span>
                        @endif
                        <div class="premium-pkg__banner">
                            <div class="premium-pkg__tier">
                                <span class="premium-pkg__icon" aria-hidden="true">
                                    @include('partials.theme-icon', ['icon' => $pkg['badge_icon'] ?? 'star'])
                                </span>
                                <div class="premium-pkg__tier-copy">
                                    <h3>{{ $pkg['name'] }}</h3>
                                    <p>{{ __('app.premium.days_access', ['days' => $pkg['duration_days']]) }}</p>
                                </div>
                            </div>
                            <div class="premium-pkg__pricing">
                                <p class="premium-pkg__price">
                                    <span class="premium-pkg__price-value">{{ number_format($pkg['price_tl'], 0, ',', '.') }}</span>
                                    <span class="premium-pkg__price-currency">TL</span>
                                </p>
                                <p class="premium-pkg__per-day">{{ __('app.premium.per_day', ['price' => number_format($perDay, 0, ',', '.')]) }}</p>
                            </div>
                        </div>
                        @if(!empty($pkg['badge_enabled']))
                            <div class="premium-pkg__rozet">
                                <span class="premium-pkg__rozet-pill">
                                    @include('partials.theme-icon', ['icon' => $pkg['badge_icon'] ?? 'star'])
                                    {{ $pkg['rozet_label'] ?? $pkg['badge_label'] }}
                                </span>
                                @if(!empty($pkg['rozet_text']))
                                    <p class="premium-pkg__rozet-text">{{ $pkg['rozet_text'] }}</p>
                                @endif
                            </div>
                        @endif
                        <ul class="premium-pkg__features">
                            <li>@include('partials.theme-icon', ['icon' => 'check']) {{ __('app.premium.perk_stories') }}</li>
                            <li>@include('partials.theme-icon', ['icon' => 'check']) {{ __('app.premium.perk_who_viewed') }}</li>
                            <li>@include('partials.theme-icon', ['icon' => 'check']) {{ __('app.premium.perk_gallery') }}</li>
                            <li>@include('partials.theme-icon', ['icon' => 'check']) {{ __('app.premium.perk_profile') }}</li>
                            @if(!empty($pkg['rozet_label']))
                                <li>@include('partials.theme-icon', ['icon' => 'check']) {{ $pkg['rozet_label'] }}</li>
                            @endif
                        </ul>
                    </article>
                @endforeach
            </div>
        </section>

        @include('partials.premium-app-cta')

        @include('partials.premium-features')
    @endif
</div>
@endsection

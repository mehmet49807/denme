@extends('layouts.app-with-sidebar')

@php
    $activeNav = '';
    $featuredPackage = $featuredPackage ?? 'gold';
@endphp

@section('title', __('app.premium.page_title') . ' — ' . __('app.brand'))

@push('head-meta')
@include('partials.asset', ['path' => 'css/premium-page.min.css'])
@endpush

@section('app-content')
<div class="premium-page">
    <header class="pm-intro">
        <p class="pm-intro__brand">{{ __('app.brand') }}</p>
        <h1 class="pm-intro__title">{{ __('app.premium.page_title') }}</h1>
        <p class="pm-intro__lead">{{ __('app.premium.hero_lead') }}</p>
        @if($user->gender !== 'female')
            <a href="#premium-packages" class="pm-intro__cta">{{ __('app.premium.packages_title') }}</a>
        @endif
    </header>

    @if($user->gender === 'female')
        <section class="pm-notice pm-notice--included">
            <span class="pm-notice__icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'heart'])</span>
            <div class="pm-notice__body">
                <h2>{{ __('app.premium.female_title') }}</h2>
                <p>{{ __('app.premium.female_lead') }}</p>
                <ul class="pm-notice__list">
                    @foreach($features as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
        </section>
    @else
        @if($user->isOnTrial())
            <section class="pm-notice pm-notice--trial">
                <span class="pm-notice__icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'clock'])</span>
                <div class="pm-notice__body">
                    <strong>{{ __('app.premium.trial_status') }}</strong>
                    <p>{{ __('app.premium.trial_until', ['date' => $user->trial_ends_at->format('d.m.Y H:i'), 'days' => $user->trialDaysRemaining()]) }}</p>
                </div>
            </section>
        @elseif($activeSubscription)
            <section class="pm-notice pm-notice--active">
                <span class="pm-notice__icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'crown'])</span>
                <div class="pm-notice__body">
                    <strong>{{ __('app.premium.active_package', ['name' => $packages[$activeSubscription->package_type]['name'] ?? ucfirst($activeSubscription->package_type)]) }}</strong>
                    <p>{{ __('app.premium.valid_until', ['date' => $activeSubscription->expires_at->format('d.m.Y H:i')]) }}</p>
                </div>
            </section>
        @else
            <section class="pm-notice pm-notice--expired">
                <span class="pm-notice__icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'star'])</span>
                <div class="pm-notice__body">
                    <strong>{{ __('app.premium.expired_title') }}</strong>
                    <p>{{ __('app.premium.expired_lead') }}</p>
                </div>
            </section>
        @endif

        <section class="pm-section" id="premium-packages">
            <header class="pm-section__head">
                <h2 class="pm-section__title">{{ __('app.premium.packages_title') }}</h2>
                <p class="pm-section__sub">{{ __('app.premium.packages_sub') }}</p>
            </header>

            <div class="pm-plans">
                @foreach($packages as $type => $pkg)
                    @php
                        $isFeatured = $type === $featuredPackage;
                        $isActive = $activeSubscription && $activeSubscription->package_type === $type;
                        $perDay = (int) round($pkg['price_tl'] / max(1, $pkg['duration_days']));
                        $from = $pkg['gradient_from'] ?? '#e11d48';
                        $to = $pkg['gradient_to'] ?? '#f59e0b';
                    @endphp
                    <article class="pm-plan pm-plan--{{ $type }} {{ $isFeatured ? 'pm-plan--featured' : '' }} {{ $isActive ? 'pm-plan--active' : '' }}" style="--plan-from: {{ $from }}; --plan-to: {{ $to }};">
                        @if($isActive)
                            <span class="pm-plan__flag">{{ __('app.premium.active_tag') }}</span>
                        @elseif($isFeatured)
                            <span class="pm-plan__flag pm-plan__flag--hot">{{ __('app.premium.most_popular') }}</span>
                        @endif

                        <div class="pm-plan__top">
                            <span class="pm-plan__icon" aria-hidden="true">
                                @include('partials.theme-icon', ['icon' => $pkg['badge_icon'] ?? 'star'])
                            </span>
                            <div class="pm-plan__name">
                                <h3>{{ $pkg['name'] }}</h3>
                                <p>{{ __('app.premium.days_access', ['days' => $pkg['duration_days']]) }}</p>
                            </div>
                            <div class="pm-plan__price">
                                <strong>{{ number_format($pkg['price_tl'], 0, ',', '.') }}</strong>
                                <span>TL</span>
                                <small>{{ __('app.premium.per_day', ['price' => number_format($perDay, 0, ',', '.')]) }}</small>
                            </div>
                        </div>

                        @if(!empty($pkg['badge_enabled']))
                            <div class="pm-plan__badge">
                                <span>
                                    @include('partials.theme-icon', ['icon' => $pkg['badge_icon'] ?? 'star'])
                                    {{ $pkg['rozet_label'] ?? $pkg['badge_label'] }}
                                </span>
                                @if(!empty($pkg['rozet_text']))
                                    <p>{{ $pkg['rozet_text'] }}</p>
                                @endif
                            </div>
                        @endif

                        <ul class="pm-plan__list">
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

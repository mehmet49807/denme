@php
    $viewer = $user ?? auth()->user();
@endphp

@if($viewer && $viewer->gender === 'male')
    @php
        $canBoost = $viewer->canUseProfileBoost();
        $isBoosted = $viewer->isBoosted();
        $canBoostToday = $viewer->canBoostToday();
        $hours = $viewer->hasPackageAtLeast('platinum') ? 24 : 12;
    @endphp

    @if($canBoost)
    <section class="profile-boost {{ $isBoosted ? 'profile-boost--active' : '' }}" aria-label="{{ __('app.premium.boost_title') }}">
        <div class="profile-boost__icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'bolt'])</div>
        <div class="profile-boost__copy">
            <h2 class="profile-boost__title">{{ __('app.premium.boost_title') }}</h2>
            @if($isBoosted)
                <p class="profile-boost__text">{{ __('app.premium.boost_active', ['time' => $viewer->boost_until->diffForHumans()]) }}</p>
            @elseif($canBoostToday)
                <p class="profile-boost__text">{{ __('app.premium.boost_hint', ['hours' => $hours]) }}</p>
            @else
                <p class="profile-boost__text">{{ __('app.premium.boost_used') }}</p>
            @endif
        </div>
        @if(! $isBoosted && $canBoostToday)
            <form method="POST" action="{{ route('profile.boost') }}" class="profile-boost__form">
                @csrf
                <button type="submit" class="profile-boost__btn">{{ __('app.premium.boost_cta') }}</button>
            </form>
        @elseif($isBoosted)
            <span class="profile-boost__pill">{{ __('app.premium.boost_live') }}</span>
        @endif
    </section>
    @elseif($viewer->isPremium())
    <section class="profile-boost profile-boost--locked" aria-label="{{ __('app.premium.boost_title') }}">
        <div class="profile-boost__icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'bolt'])</div>
        <div class="profile-boost__copy">
            <h2 class="profile-boost__title">{{ __('app.premium.boost_title') }}</h2>
            <p class="profile-boost__text">{{ __('app.premium.boost_gold_required') }}</p>
        </div>
        <a href="{{ route('premium') }}#premium-packages" class="profile-boost__btn profile-boost__btn--link">Gold</a>
    </section>
    @endif
@endif

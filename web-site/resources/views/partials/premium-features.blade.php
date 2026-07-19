@php
    $perkItems = [
        ['icon' => 'heart', 'title' => __('app.premium.perk_unlimited_messages'), 'desc' => 'Pro, Gold ve Platinum'],
        ['icon' => 'post', 'title' => __('app.premium.perk_gallery'), 'desc' => 'Pro, Gold ve Platinum'],
        ['icon' => 'star', 'title' => __('app.premium.perk_featured_profile'), 'desc' => 'Gold ve Platinum'],
        ['icon' => 'camera', 'title' => __('app.premium.perk_stories'), 'desc' => 'Gold ve Platinum'],
        ['icon' => 'bolt', 'title' => __('app.premium.perk_top_featured'), 'desc' => 'Platinum'],
        ['icon' => 'sparkles', 'title' => __('app.premium.perk_story_boost'), 'desc' => 'Platinum'],
        ['icon' => 'eye', 'title' => __('app.premium.perk_who_viewed'), 'desc' => 'Platinum'],
    ];
@endphp

<section class="pm-section pm-benefits" aria-labelledby="pm-benefits-title">
    <header class="pm-section__head">
        <h2 id="pm-benefits-title" class="pm-section__title">{{ __('app.premium.features_title') }}</h2>
        <p class="pm-section__sub">{{ __('app.premium.features_sub') }}</p>
    </header>

    <ul class="pm-benefits__list">
        @foreach($perkItems as $item)
            <li class="pm-benefits__item">
                <span class="pm-benefits__icon" aria-hidden="true">
                    @include('partials.theme-icon', ['icon' => $item['icon']])
                </span>
                <div>
                    <h3>{{ $item['title'] }}</h3>
                    <p>{{ $item['desc'] }}</p>
                </div>
            </li>
        @endforeach
    </ul>

    <p class="pm-benefits__note">
        @include('partials.theme-icon', ['icon' => 'check'])
        {{ __('app.premium.features_foot') }}
    </p>
</section>

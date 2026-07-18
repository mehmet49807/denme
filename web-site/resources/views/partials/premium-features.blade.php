@php
    $perkItems = [
        ['icon' => 'camera', 'title' => __('app.premium.feat_stories_title'), 'desc' => __('app.premium.feat_stories_desc')],
        ['icon' => 'eye', 'title' => __('app.premium.feat_who_viewed_title'), 'desc' => __('app.premium.feat_who_viewed_desc')],
        ['icon' => 'post', 'title' => __('app.premium.feat_gallery_title'), 'desc' => __('app.premium.feat_gallery_desc')],
        ['icon' => 'star', 'title' => __('app.premium.feat_boost_title'), 'desc' => __('app.premium.feat_boost_desc')],
        ['icon' => 'heart', 'title' => __('app.premium.feat_likes_title'), 'desc' => __('app.premium.feat_likes_desc')],
        ['icon' => 'bolt', 'title' => __('app.premium.feat_visibility_title'), 'desc' => __('app.premium.feat_visibility_desc')],
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

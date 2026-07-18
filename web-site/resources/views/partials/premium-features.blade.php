@php
    $perkItems = [
        ['slug' => 'stories', 'icon' => 'camera', 'title' => __('app.premium.feat_stories_title'), 'desc' => __('app.premium.feat_stories_desc')],
        ['slug' => 'who-viewed', 'icon' => 'eye', 'title' => __('app.premium.feat_who_viewed_title'), 'desc' => __('app.premium.feat_who_viewed_desc')],
        ['slug' => 'gallery', 'icon' => 'post', 'title' => __('app.premium.feat_gallery_title'), 'desc' => __('app.premium.feat_gallery_desc')],
        ['slug' => 'boost', 'icon' => 'star', 'title' => __('app.premium.feat_boost_title'), 'desc' => __('app.premium.feat_boost_desc')],
        ['slug' => 'likes', 'icon' => 'heart', 'title' => __('app.premium.feat_likes_title'), 'desc' => __('app.premium.feat_likes_desc')],
        ['slug' => 'visibility', 'icon' => 'bolt', 'title' => __('app.premium.feat_visibility_title'), 'desc' => __('app.premium.feat_visibility_desc')],
    ];
@endphp

<section class="premium-perks" aria-labelledby="premium-perks-title">
    <div class="premium-perks__mesh" aria-hidden="true"></div>
    <header class="premium-perks__head">
        <span class="premium-perks__badge">
            @include('partials.theme-icon', ['icon' => 'sparkles'])
            Premium
        </span>
        <h2 id="premium-perks-title" class="premium-perks__title">{{ __('app.premium.features_title') }}</h2>
        <p class="premium-perks__sub">{{ __('app.premium.features_sub') }}</p>
    </header>

    <div class="premium-perks__grid">
        @foreach($perkItems as $index => $item)
            <article class="premium-perks__card premium-perks__card--{{ $item['slug'] }}">
                <span class="premium-perks__index" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                <span class="premium-perks__icon" aria-hidden="true">
                    @include('partials.theme-icon', ['icon' => $item['icon']])
                </span>
                <div class="premium-perks__copy">
                    <h3>{{ $item['title'] }}</h3>
                    <p>{{ $item['desc'] }}</p>
                </div>
            </article>
        @endforeach
    </div>

    <p class="premium-perks__foot">
        @include('partials.theme-icon', ['icon' => 'check'])
        {{ __('app.premium.features_foot') }}
    </p>
</section>

<section class="pm-app" aria-labelledby="pm-app-title">
    <header class="pm-app__head">
        <p class="pm-app__eyebrow">{{ __('app.premium.app_cta_badge') }}</p>
        <h2 id="pm-app-title">{{ __('app.premium.app_cta_title') }}</h2>
        <p>{{ __('app.premium.app_cta_lead') }}</p>
    </header>

    <ol class="pm-app__steps">
        <li><span>1</span>{{ __('app.premium.app_cta_step_download') }}</li>
        <li><span>2</span>{{ __('app.premium.app_cta_step_choose') }}</li>
        <li><span>3</span>{{ __('app.premium.app_cta_step_start') }}</li>
    </ol>

    <div class="pm-app__stores">
        <span class="pm-app__store" aria-label="Google Play — {{ __('app.premium.app_store_soon') }}">
            <img src="{{ asset('images/icon-google-play.svg') }}?v=2022" alt="" width="22" height="24" decoding="async">
            <span>
                <small>{{ __('app.premium.app_store_soon') }}</small>
                <strong>Google Play</strong>
            </span>
        </span>
        <span class="pm-app__store" aria-label="App Store — {{ __('app.premium.app_store_soon') }}">
            <img src="{{ asset('images/icon-apple.svg') }}" alt="" width="20" height="20" decoding="async">
            <span>
                <small>{{ __('app.premium.app_store_soon') }}</small>
                <strong>App Store</strong>
            </span>
        </span>
    </div>

    <p class="pm-app__trust">
        @include('partials.theme-icon', ['icon' => 'shield'])
        {{ __('app.premium.app_cta_trust') }}
    </p>
</section>

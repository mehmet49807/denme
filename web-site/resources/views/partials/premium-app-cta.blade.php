<section class="premium-theme-app" aria-labelledby="premium-app-cta-title">
    <div class="premium-theme-app__glow" aria-hidden="true"></div>
    <div class="premium-theme-app__inner">
        <header class="premium-theme-app__head">
            <span class="premium-theme-app__badge">
                @include('partials.theme-icon', ['icon' => 'bolt'])
                {{ __('app.premium.app_cta_badge') }}
            </span>
            <h2 id="premium-app-cta-title" class="premium-theme-app__title">{{ __('app.premium.app_cta_title') }}</h2>
            <p class="premium-theme-app__lead">{{ __('app.premium.app_cta_lead') }}</p>
        </header>

        <ol class="premium-theme-app__steps">
            <li>
                <span class="premium-theme-app__step-no">1</span>
                <span class="premium-theme-app__step-text">{{ __('app.premium.app_cta_step_download') }}</span>
            </li>
            <li>
                <span class="premium-theme-app__step-no">2</span>
                <span class="premium-theme-app__step-text">{{ __('app.premium.app_cta_step_choose') }}</span>
            </li>
            <li>
                <span class="premium-theme-app__step-no">3</span>
                <span class="premium-theme-app__step-text">{{ __('app.premium.app_cta_step_start') }}</span>
            </li>
        </ol>

        <div class="premium-theme-app__stores">
            <span class="premium-theme-app__store premium-theme-app__store--google" aria-label="Google Play — {{ __('app.premium.app_store_soon') }}">
                <span class="premium-theme-app__store-icon" aria-hidden="true">
                    <img src="{{ asset('images/icon-google-play.svg') }}?v=2022" alt="" width="28" height="30" decoding="async">
                </span>
                <span class="premium-theme-app__store-copy">
                    <span class="premium-theme-app__store-label">{{ __('app.premium.app_store_soon') }}</span>
                    <span class="premium-theme-app__store-name">Google Play</span>
                </span>
            </span>
            <span class="premium-theme-app__store premium-theme-app__store--apple" aria-label="App Store — {{ __('app.premium.app_store_soon') }}">
                <span class="premium-theme-app__store-icon" aria-hidden="true">
                    <img src="{{ asset('images/icon-apple.svg') }}" alt="" width="24" height="24" decoding="async">
                </span>
                <span class="premium-theme-app__store-copy">
                    <span class="premium-theme-app__store-label">{{ __('app.premium.app_store_soon') }}</span>
                    <span class="premium-theme-app__store-name">App Store</span>
                </span>
            </span>
        </div>

        <p class="premium-theme-app__trust">
            @include('partials.theme-icon', ['icon' => 'shield'])
            {{ __('app.premium.app_cta_trust') }}
        </p>
    </div>
</section>

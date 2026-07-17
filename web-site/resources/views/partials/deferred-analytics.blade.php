@php
    try {
        $settings = app(\App\Services\SiteSettingsService::class);
        $gaId = trim((string) $settings->get('google_analytics_id', '')) ?: 'G-7Z411GWG80';
        $gtmId = $settings->get('google_tag_manager_id') ?: 'GTM-57LJQ8PP';
    } catch (\Throwable) {
        $gaId = 'G-7Z411GWG80';
        $gtmId = 'GTM-57LJQ8PP';
    }
    $deferUntilInteraction = request()->routeIs('home') && auth()->guest();
    $signedUp = session()->pull('growth_signed_up');
    $signedUpMethod = session()->pull('growth_signed_up_method', 'email');
@endphp
<script>
(function () {
    var gaId = @json($gaId);
    var gtmId = @json($gtmId);
    var deferUntilInteraction = @json($deferUntilInteraction);
    var signedUp = @json((bool) $signedUp);
    var signedUpMethod = @json((string) $signedUpMethod);
    var loaded = false;

    window.dataLayer = window.dataLayer || [];

    window.gkTrack = function (eventName, params) {
        params = params || {};
        try {
            window.dataLayer.push(Object.assign({ event: eventName }, params));
            if (typeof window.gtag === 'function') {
                window.gtag('event', eventName, params);
            }
        } catch (e) {}
    };

    document.addEventListener('click', function (ev) {
        var el = ev.target && ev.target.closest ? ev.target.closest('[data-gk-event]') : null;
        if (!el) return;
        window.gkTrack(el.getAttribute('data-gk-event'), {
            event_label: el.getAttribute('data-gk-event-label') || '',
            event_category: 'growth'
        });
    }, true);

    function loadGtm() {
        if (!gtmId || window.__gkGtmLoaded) {
            return;
        }
        window.__gkGtmLoaded = true;
        window.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
        var script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(gtmId);
        document.head.appendChild(script);
    }

    function loadGa() {
        if (!gaId || gtmId || window.__gkGaLoaded) {
            return;
        }
        window.__gkGaLoaded = true;
        var script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(gaId);
        document.head.appendChild(script);
        function gtag(){window.dataLayer.push(arguments);}
        window.gtag = gtag;
        gtag('js', new Date());
        gtag('config', gaId);
    }

    function loadAnalytics() {
        if (loaded) {
            return;
        }
        loaded = true;
        loadGtm();
        loadGa();
        if (signedUp) {
            setTimeout(function () {
                window.gkTrack('sign_up', { method: signedUpMethod || 'email', event_category: 'growth' });
                if (signedUpMethod === 'google') {
                    window.gkTrack('google_complete', { method: 'google', event_category: 'growth' });
                }
            }, 400);
        }
    }

    function scheduleAnalytics() {
        if (deferUntilInteraction) {
            var events = ['scroll', 'pointerdown', 'keydown', 'touchstart'];
            var onInteract = function () {
                events.forEach(function (name) {
                    window.removeEventListener(name, onInteract, { capture: true });
                });
                loadAnalytics();
            };
            events.forEach(function (name) {
                window.addEventListener(name, onInteract, { once: true, passive: true, capture: true });
            });
            setTimeout(loadAnalytics, 8000);
            return;
        }

        if ('requestIdleCallback' in window) {
            requestIdleCallback(loadAnalytics, { timeout: 4000 });
        } else {
            window.addEventListener('load', loadAnalytics, { once: true });
        }
    }

    scheduleAnalytics();
})();
</script>

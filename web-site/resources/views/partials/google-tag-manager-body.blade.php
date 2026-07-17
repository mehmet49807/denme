@php
    try {
        $gtmId = app(\App\Services\SiteSettingsService::class)->get('google_tag_manager_id') ?: 'GTM-57LJQ8PP';
    } catch (\Throwable) {
        $gtmId = 'GTM-57LJQ8PP';
    }
@endphp
@if($gtmId)
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
@endif

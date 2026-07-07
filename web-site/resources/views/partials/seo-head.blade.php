@php
    $settings = app(\App\Services\SiteSettingsService::class);
    $seo = \App\Support\SeoHelper::all();
    $brand = (string) $settings->get('site_name', config('app.name', 'Gönül Köprüsü'));
    $siteUrl = rtrim((string) $settings->get('site_url', config('app.url', 'https://www.gonulkoprusu.com')), '/');
    $pageTitle = trim($__env->yieldContent('title'));
    $seoTitle = (string) ($seo['title'] ?? '');
    $fullTitle = $pageTitle !== '' && ! str_contains($pageTitle, $brand)
        ? $pageTitle
        : ($seoTitle !== '' ? $seoTitle.' — '.$brand : $brand);
    $description = (string) ($seo['description'] ?? $settings->get('default_description', ''));
    $keywords = (string) ($seo['keywords'] ?? $settings->get('default_keywords', ''));
    $canonical = (string) ($seo['canonical'] ?? url()->current());
    $ogImage = (string) ($seo['ogImage'] ?? $settings->get('og_image_url', $siteUrl.'/images/og-default.jpg'));
    $ogType = (string) ($seo['ogType'] ?? 'website');
    $noindex = (bool) ($seo['noindex'] ?? false);
    $robotsIndex = $settings->bool('robots_index', true);
    $twitterHandle = ltrim((string) $settings->get('twitter_handle', '@gonulkoprusucom'), '@');
    $googleVerification = trim((string) $settings->get('google_site_verification', ''));
    $bingVerification = trim((string) $settings->get('bing_site_verification', ''));
    $languages = ['tr', 'en', 'de', 'fr', 'hi'];
    $currentUrl = url()->current();
@endphp
@if($seoTitle !== '' && $pageTitle !== '' && $pageTitle !== $fullTitle)
<title>{{ $fullTitle }}</title>
@endif
<meta name="description" content="{{ $description }}">
@if($keywords !== '')
<meta name="keywords" content="{{ $keywords }}">
@endif
<meta name="author" content="{{ $brand }}">
@if($googleVerification !== '')
<meta name="google-site-verification" content="{{ $googleVerification }}">
@endif
@if($bingVerification !== '')
<meta name="msvalidate.01" content="{{ $bingVerification }}">
@endif
<meta name="robots" content="{{ ($noindex || ! $robotsIndex) ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' }}">
<link rel="canonical" href="{{ $canonical }}">
<meta name="format-detection" content="telephone=no">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ $brand }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ \Illuminate\Support\Str::limit($description, 120) }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:locale" content="tr_TR">
<meta property="og:site_name" content="{{ $brand }}">
@foreach(['en_US', 'de_DE', 'fr_FR', 'hi_IN'] as $altLocale)
<meta property="og:locale:alternate" content="{{ $altLocale }}">
@endforeach
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $ogImage }}">
<meta name="twitter:image:alt" content="{{ \Illuminate\Support\Str::limit($description, 120) }}">
@if($twitterHandle !== '')
<meta name="twitter:site" content="@{{ $twitterHandle }}">
@endif
@foreach($languages as $lang)
<link rel="alternate" hreflang="{{ $lang }}" href="{{ $lang === 'tr' ? $currentUrl : $currentUrl.(str_contains($currentUrl, '?') ? '&' : '?').'lang='.$lang }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $currentUrl }}">
<link rel="dns-prefetch" href="//www.googletagmanager.com">
<link rel="dns-prefetch" href="//www.google-analytics.com">
<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="dns-prefetch" href="//fonts.gstatic.com">

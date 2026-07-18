#!/usr/bin/env python3
"""Generate patch-web-live-sync.php — tüm canlı web güncellemelerini tek seferde uygular."""

from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-live-sync.php"

WEB = ROOT / "web-site"

files = {
    "css/mobile-bottom-nav.css": WEB / "public/css/mobile-bottom-nav.css",
    "css/nav-icon-animations.css": WEB / "public/css/nav-icon-animations.css",
    "css/profile-settings.css": WEB / "public/css/profile-settings.css",
    "js/mobile-bottom-nav.js": WEB / "public/js/mobile-bottom-nav.js",
    "js/profile-settings.js": WEB / "public/js/profile-settings.js",
    "resources/views/partials/app-sidebar.blade.php": WEB
    / "resources/views/partials/app-sidebar.blade.php",
    "resources/views/partials/sidebar-icon.blade.php": WEB
    / "resources/views/partials/sidebar-icon.blade.php",
    "resources/views/layouts/app-with-sidebar.blade.php": WEB
    / "resources/views/layouts/app-with-sidebar.blade.php",
    "resources/views/layouts/app.blade.php": WEB / "resources/views/layouts/app.blade.php",
    "resources/views/partials/logo-brand-css.blade.php": WEB
    / "resources/views/partials/logo-brand-css.blade.php",
    "resources/views/partials/asset.blade.php": WEB / "resources/views/partials/asset.blade.php",
    "resources/views/partials/critical-ui-css.blade.php": WEB
    / "resources/views/partials/critical-ui-css.blade.php",
    "resources/views/partials/logo.blade.php": WEB / "resources/views/partials/logo.blade.php",
    "resources/views/partials/profile-settings-open-btn.blade.php": WEB
    / "resources/views/partials/profile-settings-open-btn.blade.php",
    "resources/views/partials/profile-settings-panels.blade.php": WEB
    / "resources/views/partials/profile-settings-panels.blade.php",
    "resources/views/partials/profile-settings-sheet.blade.php": WEB
    / "resources/views/partials/profile-settings-sheet.blade.php",
    "resources/views/web/feed.blade.php": WEB / "resources/views/web/feed.blade.php",
    "resources/views/web/profile.blade.php": WEB / "resources/views/web/profile.blade.php",
    "resources/views/web/location-users.blade.php": WEB
    / "resources/views/web/location-users.blade.php",
    "resources/views/web/google-complete.blade.php": WEB
    / "resources/views/web/google-complete.blade.php",
    "resources/views/partials/profile-online-label.blade.php": WEB
    / "resources/views/partials/profile-online-label.blade.php",
    "css/feed-stories.css": WEB / "public/css/feed-stories.css",
    "css/profile-toolbar-mobile.css": WEB / "public/css/profile-toolbar-mobile.css",
    "css/feed-toolbar.css": WEB / "public/css/feed-toolbar.css",
    "css/profile-premium-sections.css": WEB / "public/css/profile-premium-sections.css",
    "css/location-search.css": WEB / "public/css/location-search.css",
    "resources/views/partials/feed-toolbar.blade.php": WEB
    / "resources/views/partials/feed-toolbar.blade.php",
    "resources/views/partials/theme-icon.blade.php": WEB
    / "resources/views/partials/theme-icon.blade.php",
    "resources/views/partials/profile-views.blade.php": WEB
    / "resources/views/partials/profile-views.blade.php",
    "resources/views/partials/profile-gallery.blade.php": WEB
    / "resources/views/partials/profile-gallery.blade.php",
    "resources/views/web/premium.blade.php": WEB / "resources/views/web/premium.blade.php",
    "resources/views/partials/premium-app-cta.blade.php": WEB
    / "resources/views/partials/premium-app-cta.blade.php",
    "resources/views/web/register.blade.php": WEB / "resources/views/web/register.blade.php",
    "resources/views/web/user-profile.blade.php": WEB / "resources/views/web/user-profile.blade.php",
    "resources/views/partials/profile-identity.blade.php": WEB
    / "resources/views/partials/profile-identity.blade.php",
    "resources/views/partials/relationship-status-picker.blade.php": WEB
    / "resources/views/partials/relationship-status-picker.blade.php",
    "resources/views/partials/birth-date-fields.blade.php": WEB
    / "resources/views/partials/birth-date-fields.blade.php",
    "css/profile-identity.css": WEB / "public/css/profile-identity.css",
    "css/app.min.css": WEB / "public/css/app.min.css",
    "css/app-shell.min.css": WEB / "public/css/app-shell.min.css",
    "css/premium-page.min.css": WEB / "public/css/premium-page.min.css",
    "css/feed-page.min.css": WEB / "public/css/feed-page.min.css",
    "css/profile-page.min.css": WEB / "public/css/profile-page.min.css",
    "css/user-profile.min.css": WEB / "public/css/user-profile.min.css",
    "css/location-search.min.css": WEB / "public/css/location-search.min.css",
    "css/profile-identity.min.css": WEB / "public/css/profile-identity.min.css",
    "js/core.min.js": WEB / "public/js/core.min.js",
    "js/app-shell.min.js": WEB / "public/js/app-shell.min.js",
    "js/feed-page.min.js": WEB / "public/js/feed-page.min.js",
    "js/profile-page.min.js": WEB / "public/js/profile-page.min.js",
    "js/register.min.js": WEB / "public/js/register.min.js",
    "js/rt-client.min.js": WEB / "public/js/rt-client.min.js",
    "js/locations.min.js": WEB / "public/js/locations.min.js",
    "app/Support/RelationshipStatus.php": WEB / "app/Support/RelationshipStatus.php",
    "app/Http/Controllers/Web/HomeController.php": WEB
    / "app/Http/Controllers/Web/HomeController.php",
    "app/Http/Controllers/Web/AuthPageController.php": WEB
    / "app/Http/Controllers/Web/AuthPageController.php",
    "app/Http/Controllers/Web/FeedPageController.php": WEB
    / "app/Http/Controllers/Web/FeedPageController.php",
    "app/Http/Controllers/Web/ProfilePageController.php": WEB
    / "app/Http/Controllers/Web/ProfilePageController.php",
    "app/Http/Controllers/Web/UserProfilePageController.php": WEB
    / "app/Http/Controllers/Web/UserProfilePageController.php",
    "app/Http/Controllers/Web/PremiumPageController.php": WEB
    / "app/Http/Controllers/Web/PremiumPageController.php",
    "app/Http/Controllers/Web/SetupController.php": WEB
    / "app/Http/Controllers/Web/SetupController.php",
    "app/Models/User.php": WEB / "app/Models/User.php",
    "app/Models/ProfileView.php": WEB / "app/Models/ProfileView.php",
    "app/Models/Referral.php": WEB / "app/Models/Referral.php",
    "app/Models/PremiumSubscription.php": WEB / "app/Models/PremiumSubscription.php",
    "app/Models/SiteSetting.php": WEB / "app/Models/SiteSetting.php",
    "app/Services/ReferralService.php": WEB / "app/Services/ReferralService.php",
    "app/Services/UserAttributionService.php": WEB / "app/Services/UserAttributionService.php",
    "app/Services/SiteSettingsService.php": WEB / "app/Services/SiteSettingsService.php",
    "app/Services/LocationDataService.php": WEB / "app/Services/LocationDataService.php",
    "app/Support/FeaturedCities.php": WEB / "app/Support/FeaturedCities.php",
    "app/Http/Controllers/Web/ReferralPageController.php": WEB
    / "app/Http/Controllers/Web/ReferralPageController.php",
    "app/Http/Controllers/Web/CitySeoPageController.php": WEB
    / "app/Http/Controllers/Web/CitySeoPageController.php",
    "app/Http/Controllers/Web/GoogleAuthController.php": WEB
    / "app/Http/Controllers/Web/GoogleAuthController.php",
    "app/Http/Middleware/CaptureGrowthAttribution.php": WEB
    / "app/Http/Middleware/CaptureGrowthAttribution.php",
    "resources/views/web/referral.blade.php": WEB / "resources/views/web/referral.blade.php",
    "resources/views/web/invite-landing.blade.php": WEB
    / "resources/views/web/invite-landing.blade.php",
    "resources/views/web/city-seo.blade.php": WEB / "resources/views/web/city-seo.blade.php",
    "resources/views/web/home.blade.php": WEB / "resources/views/web/home.blade.php",
    "resources/views/web/premium.blade.php": WEB / "resources/views/web/premium.blade.php",
    "resources/views/partials/premium-app-cta.blade.php": WEB
    / "resources/views/partials/premium-app-cta.blade.php",
    "resources/views/partials/homepage-body.blade.php": WEB
    / "resources/views/partials/homepage-body.blade.php",
    "resources/views/partials/seo-head.blade.php": WEB
    / "resources/views/partials/seo-head.blade.php",
    "app/Support/SeoHelper.php": WEB / "app/Support/SeoHelper.php",
    "app/Http/Controllers/Web/SitemapController.php": WEB
    / "app/Http/Controllers/Web/SitemapController.php",
    "app/Http/Controllers/Web/HomeController.php": WEB
    / "app/Http/Controllers/Web/HomeController.php",
    "css/app.css": WEB / "public/css/app.css",
    "css/app.min.css": WEB / "public/css/app.min.css",
    "css/premium-page.css": WEB / "public/css/premium-page.css",
    "css/premium-page.min.css": WEB / "public/css/premium-page.min.css",
    "robots.txt": WEB / "robots.txt",
    "resources/views/partials/footer.blade.php": WEB / "resources/views/partials/footer.blade.php",
    "resources/views/partials/deferred-analytics.blade.php": WEB
    / "resources/views/partials/deferred-analytics.blade.php",
    "resources/views/partials/google-tag-manager-body.blade.php": WEB
    / "resources/views/partials/google-tag-manager-body.blade.php",
    "resources/views/partials/profile-identity.blade.php": WEB
    / "resources/views/partials/profile-identity.blade.php",
    "css/growth.css": WEB / "public/css/growth.css",
    "css/growth.min.css": WEB / "public/css/growth.min.css",
    "database/data/world-locations.php": WEB / "database/data/world-locations.php",
    "storage/app/seo/openrouter-published-blog-faq.json": WEB
    / "storage/app/seo/openrouter-published-blog-faq.json",
    "lang/tr/app.php": WEB / "lang/tr/app.php",
    "lang/en/app.php": WEB / "lang/en/app.php",
    "routes/web.php": WEB / "routes/web.php",
    "app/Services/GrowthOnboardingService.php": WEB / "app/Services/GrowthOnboardingService.php",
    "app/Services/GrowthLifecycleService.php": WEB / "app/Services/GrowthLifecycleService.php",
    "app/Services/UserMailService.php": WEB / "app/Services/UserMailService.php",
    "resources/views/partials/growth-onboarding.blade.php": WEB
    / "resources/views/partials/growth-onboarding.blade.php",
    "resources/views/partials/store-badges.blade.php": WEB
    / "resources/views/partials/store-badges.blade.php",
    "config/email_templates.php": WEB / "config/email_templates.php",
}

payload = {
    rel: base64.b64encode(path.read_bytes()).decode()
    for rel, path in files.items()
    if path.is_file()
}

php = r"""<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit('forbidden');
}

$webRoot = __DIR__;
$files = json_decode(<<<'JSON'
FILES_JSON
JSON, true);

echo "Gonul Koprüsü — canlı senkron patch\n";

foreach ($files as $rel => $b64) {
    $path = $webRoot.'/'.ltrim($rel, '/');
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo "write $rel ".filesize($path)."\n";
}

foreach ([
    'app/Http/Controllers/Web/SettingsPageController.php',
    'resources/views/partials/profile-settings-menu.blade.php',
    'resources/views/partials/settings-page-header.blade.php',
    'resources/views/partials/profile-completion.blade.php',
    'resources/views/web/settings/profile.blade.php',
    'resources/views/web/settings/hobbies.blade.php',
    'resources/views/web/settings/language.blade.php',
    'resources/views/web/settings/password.blade.php',
] as $obsolete) {
    $path = $webRoot.'/'.$obsolete;
    if (is_file($path)) {
        unlink($path);
        echo "removed $obsolete\n";
    }
}

foreach (['view:clear', 'route:clear', 'cache:clear', 'config:clear'] as $command) {
    try {
        @shell_exec('cd '.escapeshellarg($webRoot).' && php artisan '.$command.' 2>/dev/null');
    } catch (Throwable $e) {
    }
}

try {
    @file_get_contents(rtrim((isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https').'://'.($_SERVER['HTTP_HOST'] ?? 'gonulkoprusu.com'), '/').'/setup/profile-fields?key=gk-cpanel-setup-2026');
    echo "schema setup/profile-fields triggered\n";
} catch (Throwable $e) {
}

echo "OK\n";
"""

OUT.write_text(php.replace("FILES_JSON", json.dumps(payload)), encoding="utf-8")
print(f"wrote {OUT} ({OUT.stat().st_size} bytes, {len(payload)} files)")

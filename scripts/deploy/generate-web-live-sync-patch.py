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
    "config/brand.php": WEB / "config/brand.php",
    "images/logo-180.png": WEB / "public/images/logo-180.png",
    "images/logo-220.png": WEB / "public/images/logo-220.png",
    "images/logo-320.png": WEB / "public/images/logo-320.png",
    "images/logo-180-light.png": WEB / "public/images/logo-180-light.png",
    "images/logo-220-light.png": WEB / "public/images/logo-220-light.png",
    "images/logo-320-light.png": WEB / "public/images/logo-320-light.png",
    "images/logo-mark.png": WEB / "public/images/logo-mark.png",
    "images/logo-admin.png": WEB / "public/images/logo-admin.png",
    "images/favicon.png": WEB / "public/images/favicon.png",
    "images/favicon-32.png": WEB / "public/images/favicon-32.png",
    "images/favicon-64.png": WEB / "public/images/favicon-64.png",
    "images/favicon.svg": WEB / "public/images/favicon.svg",
    "images/apple-touch-icon.png": WEB / "public/images/apple-touch-icon.png",
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
    "resources/views/partials/location-fields.blade.php": WEB
    / "resources/views/partials/location-fields.blade.php",
    "resources/views/partials/phone-field.blade.php": WEB
    / "resources/views/partials/phone-field.blade.php",
    "resources/views/partials/google-icon.blade.php": WEB
    / "resources/views/partials/google-icon.blade.php",
    "resources/views/partials/google-auth-button.blade.php": WEB
    / "resources/views/partials/google-auth-button.blade.php",
    "resources/views/partials/google-signup-gate.blade.php": WEB
    / "resources/views/partials/google-signup-gate.blade.php",
    "css/landing.css": WEB / "css/landing.css",
    "css/homepage-ember.css": WEB / "css/homepage-ember.css",
    "resources/views/partials/landing-inline-css.blade.php": WEB
    / "resources/views/partials/landing-inline-css.blade.php",
    "resources/views/web/login.blade.php": WEB / "resources/views/web/login.blade.php",
    "resources/views/web/campaign-landing.blade.php": WEB
    / "resources/views/web/campaign-landing.blade.php",
    "app/Http/Controllers/Web/GoogleAuthController.php": WEB
    / "app/Http/Controllers/Web/GoogleAuthController.php",
    "resources/views/partials/profile-online-label.blade.php": WEB
    / "resources/views/partials/profile-online-label.blade.php",
    "css/feed-stories.css": WEB / "public/css/feed-stories.css",
    "css/profile-toolbar-mobile.css": WEB / "public/css/profile-toolbar-mobile.css",
    "css/feed-toolbar.css": WEB / "public/css/feed-toolbar.css",
    "css/profile-premium-sections.css": WEB / "public/css/profile-premium-sections.css",
    "css/profile-premium-sections.min.css": WEB / "public/css/profile-premium-sections.min.css",
    "css/profile-page.min.css": WEB / "public/css/profile-page.min.css",
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
    "resources/views/partials/premium-features.blade.php": WEB
    / "resources/views/partials/premium-features.blade.php",
    "resources/views/web/register.blade.php": WEB / "resources/views/web/register.blade.php",
    "resources/views/partials/trust-badges.blade.php": WEB
    / "resources/views/partials/trust-badges.blade.php",
    "resources/views/partials/relationship-status-picker.blade.php": WEB
    / "resources/views/partials/relationship-status-picker.blade.php",
    "resources/views/web/user-profile.blade.php": WEB / "resources/views/web/user-profile.blade.php",
    "resources/views/partials/profile-identity.blade.php": WEB
    / "resources/views/partials/profile-identity.blade.php",
    "resources/views/partials/profile-member-badges.blade.php": WEB
    / "resources/views/partials/profile-member-badges.blade.php",
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
    "app/Services/StoryGroupService.php": WEB / "app/Services/StoryGroupService.php",
    "app/Services/GenderFilterService.php": WEB / "app/Services/GenderFilterService.php",
    "app/Services/MessageConversationService.php": WEB
    / "app/Services/MessageConversationService.php",
    "app/Http/Controllers/Web/StoryPageController.php": WEB
    / "app/Http/Controllers/Web/StoryPageController.php",
    "resources/views/partials/feed-recommended-users.blade.php": WEB
    / "resources/views/partials/feed-recommended-users.blade.php",
    "resources/views/partials/users-browse-grid-items.blade.php": WEB
    / "resources/views/partials/users-browse-grid-items.blade.php",
    "resources/views/partials/profile-verified-tick.blade.php": WEB
    / "resources/views/partials/profile-verified-tick.blade.php",
    "resources/views/partials/trust-badge.blade.php": WEB
    / "resources/views/partials/trust-badge.blade.php",
    "resources/views/web/users.blade.php": WEB / "resources/views/web/users.blade.php",
    "resources/views/web/feed.blade.php": WEB / "resources/views/web/feed.blade.php",
    "resources/views/web/profile.blade.php": WEB / "resources/views/web/profile.blade.php",
    "resources/views/partials/post-detail-dialog.blade.php": WEB
    / "resources/views/partials/post-detail-dialog.blade.php",
    "resources/views/partials/post-caption-edit-dialog.blade.php": WEB
    / "resources/views/partials/post-caption-edit-dialog.blade.php",
    "resources/views/partials/profile-posts-grid.blade.php": WEB
    / "resources/views/partials/profile-posts-grid.blade.php",
    "app/Http/Controllers/Web/PostPageController.php": WEB
    / "app/Http/Controllers/Web/PostPageController.php",
    "app/Models/Post.php": WEB / "app/Models/Post.php",
    "js/feed.js": WEB / "public/js/feed.js",
    "js/feed.min.js": WEB / "public/js/feed.min.js",
    "js/feed-page.min.js": WEB / "public/js/feed-page.min.js",
    "js/profile-posts.js": WEB / "public/js/profile-posts.js",
    "js/profile-posts.min.js": WEB / "public/js/profile-posts.min.js",
    "js/profile-page.min.js": WEB / "public/js/profile-page.min.js",
    "routes/web.php": WEB / "routes/web.php",
    "resources/views/partials/profile-boost.blade.php": WEB
    / "resources/views/partials/profile-boost.blade.php",
    "app/Http/Controllers/Web/ProfilePageController.php": WEB
    / "app/Http/Controllers/Web/ProfilePageController.php",
    "app/Http/Controllers/Web/UserProfilePageController.php": WEB
    / "app/Http/Controllers/Web/UserProfilePageController.php",
    "app/Http/Controllers/Web/PremiumPageController.php": WEB
    / "app/Http/Controllers/Web/PremiumPageController.php",
    "app/Http/Controllers/Web/LocationUsersPageController.php": WEB
    / "app/Http/Controllers/Web/LocationUsersPageController.php",
    "app/Http/Controllers/Web/SetupController.php": WEB
    / "app/Http/Controllers/Web/SetupController.php",
    "app/Http/Controllers/Web/FcmWebController.php": WEB
    / "app/Http/Controllers/Web/FcmWebController.php",
    "app/Http/Controllers/Web/DeviceTokenController.php": WEB
    / "app/Http/Controllers/Web/DeviceTokenController.php",
    "app/Services/FcmWebConfigService.php": WEB / "app/Services/FcmWebConfigService.php",
    "app/Services/FcmPushService.php": WEB / "app/Services/FcmPushService.php",
    "config/firebase.php": WEB / "config/firebase.php",
    "js/fcm-web.js": WEB / "public/js/fcm-web.js",
    "app/Models/User.php": WEB / "app/Models/User.php",
    "app/Models/Story.php": WEB / "app/Models/Story.php",
    "app/Models/ProfileView.php": WEB / "app/Models/ProfileView.php",
    "app/Models/Referral.php": WEB / "app/Models/Referral.php",
    "app/Models/PremiumSubscription.php": WEB / "app/Models/PremiumSubscription.php",
    "app/Models/SiteSetting.php": WEB / "app/Models/SiteSetting.php",
    "app/Services/StoryService.php": WEB / "app/Services/StoryService.php",
    "app/Services/MediaUploadService.php": WEB / "app/Services/MediaUploadService.php",
    "app/Services/MediaOptimizer.php": WEB / "app/Services/MediaOptimizer.php",
    "app/Services/ReferralService.php": WEB / "app/Services/ReferralService.php",
    "app/Services/UserAttributionService.php": WEB / "app/Services/UserAttributionService.php",
    "app/Services/SiteSettingsService.php": WEB / "app/Services/SiteSettingsService.php",
    "app/Services/PremiumPackagesService.php": WEB / "app/Services/PremiumPackagesService.php",
    "app/Services/LocationDataService.php": WEB / "app/Services/LocationDataService.php",
    "app/Support/FeaturedCities.php": WEB / "app/Support/FeaturedCities.php",
    "app/Support/InstagramUrl.php": WEB / "app/Support/InstagramUrl.php",
    "app/Support/CitySeoCopy.php": WEB / "app/Support/CitySeoCopy.php",
    "app/Support/SeoSchema.php": WEB / "app/Support/SeoSchema.php",
    "app/Support/SeoDistricts.php": WEB / "app/Support/SeoDistricts.php",
    "app/Http/Controllers/Web/ReferralPageController.php": WEB
    / "app/Http/Controllers/Web/ReferralPageController.php",
    "app/Http/Controllers/Web/CitySeoPageController.php": WEB
    / "app/Http/Controllers/Web/CitySeoPageController.php",
    "app/Http/Controllers/Web/SeoPillarPageController.php": WEB
    / "app/Http/Controllers/Web/SeoPillarPageController.php",
    "app/Http/Controllers/Web/SuccessStoriesController.php": WEB
    / "app/Http/Controllers/Web/SuccessStoriesController.php",
    "app/Http/Controllers/Web/SupportPageController.php": WEB
    / "app/Http/Controllers/Web/SupportPageController.php",
    "app/Http/Controllers/Web/FeedPageController.php": WEB
    / "app/Http/Controllers/Web/FeedPageController.php",
    "app/Http/Controllers/Web/AuthPageController.php": WEB
    / "app/Http/Controllers/Web/AuthPageController.php",
    "resources/views/web/seo-pillar.blade.php": WEB
    / "resources/views/web/seo-pillar.blade.php",
    "resources/views/web/success-stories.blade.php": WEB
    / "resources/views/web/success-stories.blade.php",
    "resources/views/web/support.blade.php": WEB / "resources/views/web/support.blade.php",
    "resources/views/web/about.blade.php": WEB / "resources/views/web/about.blade.php",
    "resources/views/web/home.blade.php": WEB / "resources/views/web/home.blade.php",
    "resources/views/web/referral.blade.php": WEB / "resources/views/web/referral.blade.php",
    "resources/views/web/feed.blade.php": WEB / "resources/views/web/feed.blade.php",
    "resources/views/layouts/app.blade.php": WEB / "resources/views/layouts/app.blade.php",
    "resources/views/partials/profile-settings-panels.blade.php": WEB
    / "resources/views/partials/profile-settings-panels.blade.php",
    "css/app.min.css": WEB / "public/css/app.min.css",
    "app/Http/Controllers/Web/CampaignLandingController.php": WEB
    / "app/Http/Controllers/Web/CampaignLandingController.php",
    "app/Http/Controllers/Web/GoogleAuthController.php": WEB
    / "app/Http/Controllers/Web/GoogleAuthController.php",
    "app/Http/Controllers/Web/FeedPageController.php": WEB
    / "app/Http/Controllers/Web/FeedPageController.php",
    "app/Http/Controllers/Web/LegalPageController.php": WEB
    / "app/Http/Controllers/Web/LegalPageController.php",
    "app/Http/Middleware/CaptureGrowthAttribution.php": WEB
    / "app/Http/Middleware/CaptureGrowthAttribution.php",
    "resources/views/web/referral.blade.php": WEB / "resources/views/web/referral.blade.php",
    "resources/views/web/invite-landing.blade.php": WEB
    / "resources/views/web/invite-landing.blade.php",
    "resources/views/web/city-seo.blade.php": WEB / "resources/views/web/city-seo.blade.php",
    "resources/views/web/campaign-landing.blade.php": WEB
    / "resources/views/web/campaign-landing.blade.php",
    "resources/views/web/login.blade.php": WEB / "resources/views/web/login.blade.php",
    "resources/views/web/feed.blade.php": WEB / "resources/views/web/feed.blade.php",
    "resources/views/web/home.blade.php": WEB / "resources/views/web/home.blade.php",
    "resources/views/web/premium.blade.php": WEB / "resources/views/web/premium.blade.php",
    "resources/views/partials/premium-app-cta.blade.php": WEB
    / "resources/views/partials/premium-app-cta.blade.php",
    "resources/views/partials/premium-features.blade.php": WEB
    / "resources/views/partials/premium-features.blade.php",
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
    "app/Support/QuickMessages.php": WEB / "app/Support/QuickMessages.php",
    "app/Support/GreetingTemplates.php": WEB / "app/Support/GreetingTemplates.php",
    "resources/views/web/messages/show.blade.php": WEB
    / "resources/views/web/messages/show.blade.php",
    "resources/views/web/messages/index.blade.php": WEB
    / "resources/views/web/messages/index.blade.php",
    "resources/views/web/messages/partials/dm-inbox.blade.php": WEB
    / "resources/views/web/messages/partials/dm-inbox.blade.php",
    "resources/views/web/messages/partials/inbox-body.blade.php": WEB
    / "resources/views/web/messages/partials/inbox-body.blade.php",
    "resources/views/web/messages/partials/inbox-actions-script.blade.php": WEB
    / "resources/views/web/messages/partials/inbox-actions-script.blade.php",
    "app/Http/Controllers/Web/MessagePageController.php": WEB
    / "app/Http/Controllers/Web/MessagePageController.php",
    "routes/web.php": WEB / "routes/web.php",
    "app/Services/GrowthOnboardingService.php": WEB / "app/Services/GrowthOnboardingService.php",
    "app/Services/GrowthLifecycleService.php": WEB / "app/Services/GrowthLifecycleService.php",
    "app/Services/UserMailService.php": WEB / "app/Services/UserMailService.php",
    "resources/views/partials/growth-onboarding.blade.php": WEB
    / "resources/views/partials/growth-onboarding.blade.php",
    "resources/views/partials/growth-invite-banner.blade.php": WEB
    / "resources/views/partials/growth-invite-banner.blade.php",
    "resources/views/partials/store-badges.blade.php": WEB
    / "resources/views/partials/store-badges.blade.php",
    "config/email_templates.php": WEB / "config/email_templates.php",
    "js/chat.js": WEB / "public/js/chat.js",
    "js/chat.min.js": WEB / "public/js/chat.min.js",
    "js/badges.js": WEB / "public/js/badges.js",
    "js/badges.min.js": WEB / "public/js/badges.min.js",
    "resources/views/partials/empty-state.blade.php": WEB / "resources/views/partials/empty-state.blade.php",
    "resources/views/partials/toast-host.blade.php": WEB / "resources/views/partials/toast-host.blade.php",
    "resources/views/partials/header-premium-btn.blade.php": WEB / "resources/views/partials/header-premium-btn.blade.php",
    "resources/views/web/notifications/index.blade.php": WEB / "resources/views/web/notifications/index.blade.php",
    "resources/views/web/notifications/partials/list-items.blade.php": WEB / "resources/views/web/notifications/partials/list-items.blade.php",
    "app/Http/Controllers/Web/NotificationPageController.php": WEB / "app/Http/Controllers/Web/NotificationPageController.php",
    "app/Http/Controllers/Web/LiveSyncController.php": WEB / "app/Http/Controllers/Web/LiveSyncController.php",
    "app/Services/NotificationService.php": WEB / "app/Services/NotificationService.php",
    "app/Services/ConversationService.php": WEB / "app/Services/ConversationService.php",
    "app/Services/MessageService.php": WEB / "app/Services/MessageService.php",
    "app/Services/ChatTypingService.php": WEB / "app/Services/ChatTypingService.php",
    "app/Services/AiModerationService.php": WEB / "app/Services/AiModerationService.php",
    "app/Services/RealtimeBroadcastService.php": WEB / "app/Services/RealtimeBroadcastService.php",
    "app/Support/ChatMessageHelper.php": WEB / "app/Support/ChatMessageHelper.php",
    "app/Support/SidebarBadgeCounts.php": WEB / "app/Support/SidebarBadgeCounts.php",
    "app/Models/Message.php": WEB / "app/Models/Message.php",
    "app/Models/Block.php": WEB / "app/Models/Block.php",
    "app/Models/UserNotification.php": WEB / "app/Models/UserNotification.php",
    "app/Models/AdminBroadcast.php": WEB / "app/Models/AdminBroadcast.php",
    "app/Models/UserBroadcastRead.php": WEB / "app/Models/UserBroadcastRead.php",
    "app/Models/Report.php": WEB / "app/Models/Report.php",
    "app/Models/Like.php": WEB / "app/Models/Like.php",
    "app/Providers/AppServiceProvider.php": WEB / "app/Providers/AppServiceProvider.php",
    "bootstrap/providers.php": WEB / "bootstrap/providers.php",
    "bootstrap/app.php": WEB / "bootstrap/app.php",
    "js/live-sync.js": WEB / "public/js/live-sync.js",
    "js/live-sync.min.js": WEB / "public/js/live-sync.min.js",
    "css/profile-settings.min.css": WEB / "public/css/profile-settings.min.css",
    "js/profile-settings.min.js": WEB / "public/js/profile-settings.min.js",
    "marketing/instagram/cta-discipline.txt": ROOT / "marketing/instagram/cta-discipline.txt",
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

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
header('X-LiteSpeed-Purge: *');

echo "Gonul Koprüsü — canlı senkron patch\n";

if (($_GET['export'] ?? '') === '1') {
    header('Content-Type: text/plain; charset=utf-8');
    foreach ([
        'app/Services/NotificationService.php',
        'app/Services/ConversationService.php',
        'app/Services/MessageService.php',
        'app/Services/ChatTypingService.php',
        'app/Services/AiModerationService.php',
        'app/Support/ChatMessageHelper.php',
        'app/Models/Message.php',
        'app/Models/Block.php',
        'app/Models/UserNotification.php',
        'app/Models/AdminBroadcast.php',
        'app/Models/UserBroadcastRead.php',
        'app/Models/Report.php',
        'app/Models/Like.php',
        'app/Support/SidebarBadgeCounts.php',
        'app/Http/Controllers/Web/NotificationPageController.php',
        'app/Http/Controllers/Web/LiveSyncController.php',
        'resources/views/web/notifications.blade.php',
        'resources/views/web/notifications/index.blade.php',
        'resources/views/web/notifications/partials/list-items.blade.php',
        'resources/views/partials/notification-item.blade.php',
        'app/Providers/AppServiceProvider.php',
        'bootstrap/providers.php',
        'bootstrap/app.php',
    ] as $rel) {
        $path = $webRoot.'/'.$rel;
        echo "=====FILE:$rel=====\n";
        if (is_file($path)) {
            echo file_get_contents($path);
        } else {
            echo "MISSING\n";
        }
        echo "\n=====END:$rel=====\n";
    }
    exit;
}

if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "opcache_reset before write\n";
}

foreach ($files as $rel => $b64) {
    $path = $webRoot.'/'.ltrim($rel, '/');
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($path, true);
    }
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
    'images/gonul-koprusu-logo.png',
    'images/logo-mark-light.png',
    'routes/web.php.live',
    'patch-web-diag2.php',
    'patch-web-feed-fix.php',
    'patch-web-opcache.php',
] as $obsolete) {
    $path = $webRoot.'/'.$obsolete;
    if (is_file($path)) {
        unlink($path);
        echo "removed $obsolete\n";
    }
}

$feedFixDir = $webRoot.'/.gk-feed-fix-parts';
if (is_dir($feedFixDir)) {
    foreach (glob($feedFixDir.'/*') ?: [] as $part) {
        if (is_file($part)) {
            @unlink($part);
            echo "removed .gk-feed-fix-parts/".basename($part)."\n";
        }
    }
    @rmdir($feedFixDir);
    echo "removed .gk-feed-fix-parts/\n";
}

foreach (['view:clear', 'route:clear', 'cache:clear', 'config:clear'] as $command) {
    try {
        @shell_exec('cd '.escapeshellarg($webRoot).' && php artisan '.$command.' 2>/dev/null');
    } catch (Throwable $e) {
    }
}

foreach (glob($webRoot.'/storage/framework/views/*.php') ?: [] as $view) {
    @unlink($view);
}

if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "opcache_reset after write\n";
}

try {
    @file_get_contents(rtrim((isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https').'://'.($_SERVER['HTTP_HOST'] ?? 'gonulkoprusu.com'), '/').'/setup/profile-fields?key=gk-cpanel-setup-2026');
    echo "schema setup/profile-fields triggered\n";
} catch (Throwable $e) {
}

// —— Admin brand sync (logo C / brand-v17) ——
$adminRoots = [];
foreach ([
    dirname($webRoot).'/admin.gonulkoprusu.com',
    '/home/gonulkop/admin.gonulkoprusu.com',
    dirname($webRoot).'/admin',
] as $candidate) {
    if (is_dir($candidate)) {
        $adminRoots[] = $candidate;
    }
}
$adminFiles = json_decode(<<<'JSON'
__ADMIN_FILES_JSON__
JSON, true);
$version = 'brand-v17';
foreach (array_unique($adminRoots) as $adminRoot) {
    echo "admin_root=$adminRoot\n";
    if (!is_array($adminFiles)) {
        echo "adminFiles missing\n";
        continue;
    }
    foreach ($adminFiles as $rel => $b64) {
        $path = $adminRoot.'/'.ltrim($rel, '/');
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, base64_decode($b64));
        echo "admin write $rel ".filesize($path)."\n";
    }
    // Login blade may live under alternate view names on server.
    if (isset($adminFiles['resources/views/auth/login.blade.php'])) {
        $loginBlob = base64_decode($adminFiles['resources/views/auth/login.blade.php']);
        foreach ([
            'resources/views/auth/login.blade.php',
            'resources/views/admin/login.blade.php',
            'resources/views/admin/auth/login.blade.php',
            'resources/views/login.blade.php',
            'resources/views/adminlogin.blade.php',
        ] as $loginRel) {
            $path = $adminRoot.'/'.$loginRel;
            @mkdir(dirname($path), 0755, true);
            file_put_contents($path, $loginBlob);
            echo "admin login-copy $loginRel\n";
        }
    }
    $walk = function ($dir) use (&$walk, $adminRoot, $version) {
        if (!is_dir($dir)) return;
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir.'/'.$item;
            if (is_dir($path)) { $walk($path); continue; }
            if (!preg_match('/\.(blade\.php|php|html)$/i', $item)) continue;
            $content = @file_get_contents($path);
            if ($content === false) continue;
            if (!str_contains($content, 'logo') && !str_contains($content, 'brand-v') && !str_contains($content, 'favicon')) {
                continue;
            }
            $new = $content;
            $new = str_replace('brand-v16', $version, $new);
            $new = str_replace('brand-v15', $version, $new);
            $new = str_replace('brand-v14', $version, $new);
            $new = preg_replace('/logo-admin\.png\?v=brand-v\d+/', 'logo-admin.png?v='.$version, $new) ?? $new;
            $new = preg_replace('/favicon\.png\?v=brand-v\d+/', 'favicon.png?v='.$version, $new) ?? $new;
            $new = preg_replace('/logo-mark\.png\?v=brand-v\d+/', 'logo-admin.png?v='.$version, $new) ?? $new;
            if ($new !== $content) {
                file_put_contents($path, $new);
                echo 'admin view '.str_replace($adminRoot.'/', '', $path)."\n";
            }
        }
    };
    $walk($adminRoot.'/resources/views');
    $walk($adminRoot.'/resources');
    foreach (['images/logo-mark.png'] as $adminObsolete) {
        $path = $adminRoot.'/'.$adminObsolete;
        if (is_file($path)) {
            @unlink($path);
            echo "admin removed $adminObsolete\n";
        }
    }
    foreach (glob($adminRoot.'/storage/framework/views/*.php') ?: [] as $view) {
        @unlink($view);
    }
    @shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
    @shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan cache:clear 2>/dev/null');
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }
    echo "admin cache cleared\n";
}

echo "OK\n";
"""

admin_files = {
    "images/logo-admin.png": WEB / "public/images/logo-admin.png",
    "images/favicon.png": WEB / "public/images/favicon.png",
    "images/logo-mark.png": WEB / "public/images/logo-mark.png",
    "css/admin.css": ROOT / "scripts/deploy/assets/admin.css",
    "resources/views/auth/login.blade.php": ROOT
    / "admin-panel/resources/views/auth/login.blade.php",
    "resources/views/layouts/admin.blade.php": ROOT
    / "admin-panel/resources/views/layouts/admin.blade.php",
}
admin_payload = {
    rel: base64.b64encode(path.read_bytes()).decode()
    for rel, path in admin_files.items()
    if path.is_file()
}
OUT.write_text(
    php.replace("__ADMIN_FILES_JSON__", json.dumps(admin_payload)).replace(
        "FILES_JSON", json.dumps(payload)
    ),
    encoding="utf-8",
)
print(f"wrote {OUT} ({OUT.stat().st_size} bytes, {len(payload)} files)")

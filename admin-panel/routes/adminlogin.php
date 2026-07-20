<?php

// Yönetim paneli: /adminlogin (gonulkoprusu.com/adminlogin)

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminAiController;
use App\Http\Controllers\Admin\AdminContentController;
use App\Http\Controllers\Admin\AdminEmailController;
use App\Http\Controllers\Admin\AdminReferralController;
use App\Http\Controllers\Admin\AdminSupportController;
use App\Http\Controllers\Admin\AdminGithubController;
use App\Http\Controllers\Admin\AdminPackagesController;
use App\Http\Controllers\Admin\AdminAppLinksController;
use App\Http\Controllers\Admin\AdminMarketingController;
use App\Http\Controllers\Admin\AdminOpsController;
use App\Http\Controllers\Admin\AdminPanelController;
use App\Http\Controllers\Admin\AdminSeoController;
use App\Http\Controllers\Admin\AdminProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AdminAuthController::class, 'index'])->name('admin.home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'loginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:6,1,admin-login');
});

Route::post('/logout', [AdminAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('admin.logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminPanelController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/dashboard/stats', [AdminPanelController::class, 'dashboardStats'])->name('admin.dashboard.stats');
    Route::get('/users', [AdminPanelController::class, 'users'])->name('admin.users');
    Route::get('/profile-approvals', [AdminPanelController::class, 'profileApprovals'])->name('admin.profile-approvals');
    Route::post('/profile-approvals/bulk-approve', [AdminPanelController::class, 'bulkApproveProfiles'])->name('admin.profile-approvals.bulk');
    Route::post('/profile-approvals/{user}/approve', [AdminPanelController::class, 'approveProfile'])->name('admin.profile-approvals.approve');
    Route::put('/users/{user}', [AdminPanelController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminPanelController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::post('/users/{user}/unban', [AdminPanelController::class, 'unbanUser'])->name('admin.users.unban');
    Route::post('/users/premium', [AdminPanelController::class, 'grantPremium'])->name('admin.users.premium');
    Route::post('/users/premium/cancel', [AdminPanelController::class, 'cancelPremium'])->name('admin.users.premium.cancel');
    Route::post('/users/bulk', [AdminPanelController::class, 'bulkUserAction'])->name('admin.users.bulk');
    Route::post('/users/{user}/notes', [AdminOpsController::class, 'storeUserNote'])->name('admin.users.notes.store');
    Route::delete('/notes/{note}', [AdminOpsController::class, 'destroyUserNote'])->name('admin.users.notes.destroy');
    Route::get('/messages', [AdminPanelController::class, 'messages'])->name('admin.messages');
    Route::get('/moderation', [AdminOpsController::class, 'moderationQueue'])->name('admin.moderation');
    Route::get('/gallery', [AdminOpsController::class, 'gallery'])->name('admin.gallery');
    Route::post('/gallery/{user}/remove', [AdminOpsController::class, 'removeGalleryPhoto'])->name('admin.gallery.remove');
    Route::get('/audit', [AdminOpsController::class, 'auditLogs'])->name('admin.audit');
    Route::get('/auto-rules', [AdminOpsController::class, 'autoRules'])->name('admin.auto-rules');
    Route::post('/auto-rules', [AdminOpsController::class, 'updateAutoRules'])->name('admin.auto-rules.update');
    Route::get('/system-health', [AdminOpsController::class, 'systemHealth'])->name('admin.system-health');
    Route::get('/staff', [AdminOpsController::class, 'staffRoles'])->name('admin.staff');
    Route::post('/staff', [AdminOpsController::class, 'promoteStaff'])->name('admin.staff.promote');
    Route::post('/staff/{user}', [AdminOpsController::class, 'updateStaffRole'])->name('admin.staff.update');
    Route::get('/backup/users.csv', [AdminOpsController::class, 'exportUsersCsv'])->name('admin.backup.users');
    Route::get('/backup/settings.json', [AdminOpsController::class, 'exportSettingsJson'])->name('admin.backup.settings');
    Route::get('/content', [AdminContentController::class, 'index'])->name('admin.content');
    Route::delete('/content/posts/{post}', [AdminContentController::class, 'destroyPost'])->name('admin.content.posts.destroy');
    Route::delete('/content/stories/{story}', [AdminContentController::class, 'destroyStory'])->name('admin.content.stories.destroy');
    Route::get('/ai', [AdminAiController::class, 'index'])->name('admin.ai');
    Route::post('/ai/scan', [AdminAiController::class, 'scan'])->name('admin.ai.scan');
    Route::post('/ai/test', [AdminAiController::class, 'testConnection'])->name('admin.ai.test');
    Route::post('/ai/publish-blog-faq', [AdminAiController::class, 'publishBlogFaq'])->name('admin.ai.publish-blog-faq');
    Route::post('/ai/settings', [AdminAiController::class, 'updateSettings'])->name('admin.ai.settings');
    Route::post('/ai/daily-report', [AdminAiController::class, 'dailyReport'])->name('admin.ai.daily-report');
    Route::post('/ai/flags/bulk', [AdminAiController::class, 'bulkFlags'])->name('admin.ai.flags.bulk');
    Route::post('/ai/flags/{flag}/quick', [AdminAiController::class, 'quickAction'])->name('admin.ai.flags.quick');
    Route::put('/ai/flags/{flag}', [AdminAiController::class, 'updateFlag'])->name('admin.ai.flags.update');
    Route::post('/ai/users/{user}/scan', [AdminAiController::class, 'scanProfile'])->name('admin.ai.users.scan');
    Route::get('/rapor', fn () => redirect()->route('admin.dashboard'))->name('admin.rapor');
    Route::get('/rapor/{rapor}', fn () => redirect()->route('admin.dashboard'))->name('admin.rapor.show');
    Route::get('/github', [AdminGithubController::class, 'index'])->name('admin.github');
    Route::post('/github/check', [AdminGithubController::class, 'check'])->name('admin.github.check');
    Route::post('/github/clear-cache', [AdminGithubController::class, 'clearCache'])->name('admin.github.clear-cache');
    Route::post('/github/trigger', [AdminGithubController::class, 'trigger'])->name('admin.github.trigger');
    Route::post('/github/smoke', [AdminGithubController::class, 'smokeTest'])->name('admin.github.smoke');
    Route::post('/github/alert/dismiss', [AdminGithubController::class, 'dismissAlert'])->name('admin.github.alert.dismiss');
    Route::get('/reports', [AdminPanelController::class, 'reports'])->name('admin.reports');
    Route::put('/reports/{report}', [AdminPanelController::class, 'updateReport'])->name('admin.reports.update');
    Route::get('/premium', [AdminPanelController::class, 'premium'])->name('admin.premium');
    Route::post('/premium/{subscription}/cancel', [AdminPanelController::class, 'cancelPremiumSubscription'])->name('admin.premium.cancel');
    Route::get('/packages', [AdminPackagesController::class, 'index'])->name('admin.packages');
    Route::post('/packages', [AdminPackagesController::class, 'update'])->name('admin.packages.update');
    Route::get('/app-links', [AdminAppLinksController::class, 'index'])->name('admin.app-links');
    Route::post('/app-links', [AdminAppLinksController::class, 'update'])->name('admin.app-links.update');
    Route::get('/marketing', [AdminMarketingController::class, 'index'])->name('admin.marketing');
    Route::post('/marketing', [AdminMarketingController::class, 'update'])->name('admin.marketing.update');
    Route::get('/broadcasts', [AdminPanelController::class, 'broadcasts'])->name('admin.broadcasts');
    Route::post('/broadcasts', [AdminPanelController::class, 'sendBroadcast'])->name('admin.broadcasts.send');
    Route::get('/emails', [AdminEmailController::class, 'index'])->name('admin.emails');
    Route::post('/emails', [AdminEmailController::class, 'send'])->name('admin.emails.send');
    Route::post('/emails/preview', [AdminEmailController::class, 'preview'])->name('admin.emails.preview');
    Route::post('/emails/clear', [AdminEmailController::class, 'clearLogs'])->name('admin.emails.clear');
    Route::post('/emails/test', [AdminEmailController::class, 'test'])->name('admin.emails.test');
    Route::get('/referrals', [AdminReferralController::class, 'index'])->name('admin.referrals');
    Route::get('/support', [AdminSupportController::class, 'index'])->name('admin.support');
    Route::post('/support/settings', [AdminSupportController::class, 'updateSettings'])->name('admin.support.settings');
    Route::post('/support/{ticket}/reply', [AdminSupportController::class, 'reply'])->name('admin.support.reply');
    Route::get('/seo', [AdminSeoController::class, 'index'])->name('admin.seo');
    Route::post('/seo', [AdminSeoController::class, 'update'])->name('admin.seo.update');
    Route::post('/seo/clear-sitemap', [AdminSeoController::class, 'clearSitemapCache'])->name('admin.seo.clear-sitemap');
    Route::get('/seo/openrouter-refresh', [AdminSeoController::class, 'openRouterHelp'])->name('admin.seo.openrouter-help');
    Route::post('/seo/openrouter-refresh', [AdminSeoController::class, 'refreshOpenRouter'])->name('admin.seo.openrouter-refresh');
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('admin.profile');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
    Route::post('/profile/photo', [AdminProfileController::class, 'updatePhoto'])->name('admin.profile.photo');
    Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('admin.profile.password');
});

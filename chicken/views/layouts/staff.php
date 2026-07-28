<?php
/** @var string $title */
/** @var string $content */
/** @var array|null $user */
$role = $user['role'] ?? Auth::role();
$isAdminArea = $role === 'admin' && str_starts_with(current_path(), '/yonetici');
CategorySync::ensure();
$staffCategories = [];
if (!$isAdminArea) {
    try {
        $staffCategories = Database::pdo()
            ->query('SELECT name, slug FROM categories WHERE is_active = 1 ORDER BY sort_order, id')
            ->fetchAll();
    } catch (Throwable) {
        $staffCategories = [];
    }
}
$catalogBySlug = [];
foreach (CategorySync::catalog() as $row) {
    $catalogBySlug[$row['slug']] = $row;
}
$homeStaff = $role === 'admin'
    ? url('/yonetici')
    : (in_array($role, ['waiter'], true)
        ? url('/garson')
        : (in_array($role, ['cashier'], true) ? url('/kasa') : url('/mutfak')));
$sideLabel = match ($role) {
    'admin' => 'Yönetici',
    'cashier' => 'Kasa',
    default => 'Garson',
};
$sideIcon = match ($role) {
    'admin' => 'menu',
    'cashier' => 'cashier',
    default => 'waiter',
};
$sideColor = match ($role) {
    'admin' => '#e2b457',
    'cashier' => '#4c8dff',
    default => '#ff6a1a',
};
$pendingOnlineCount = (int) ($pendingOnlineCount ?? 0);
if ($pendingOnlineCount <= 0 && in_array($role, ['cashier', 'admin'], true)) {
    try {
        $pendingOnlineCount = count(OrderService::listOnlinePending(200));
    } catch (Throwable) {
        $pendingOnlineCount = 0;
    }
}
?><!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($title ?? 'Crisp & Co. Personel') ?></title>
  <?php
    $assetRoot = dirname(__DIR__, 2) . '/assets';
    $cssVer = @filemtime($assetRoot . '/css/app.css') ?: time();
    $jsVer = @filemtime($assetRoot . '/js/app.js') ?: time();
  ?>
  <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>?v=<?= e((string) $cssVer) ?>">
</head>
<body data-base="<?= e(base_path()) ?>" class="staff-body<?= $isAdminArea ? ' is-admin-area' : '' ?>">
  <div class="layout-staff<?= $isAdminArea ? ' admin-area' : '' ?>" data-staff-layout<?= $isAdminArea ? ' data-admin-area' : '' ?><?= ($isAdminArea && current_path() === '/yonetici') ? ' data-admin-home-nav' : '' ?>>
    <div class="side-backdrop" data-nav-close hidden></div>

    <aside class="side" id="staff-side" aria-hidden="true">
      <div class="side-top">
        <div class="side-waiter">
          <?php partial('partials/menu_icon', ['icon' => $sideIcon, 'color' => $sideColor]); ?>
          <div>
            <p class="side-label"><?= e($sideLabel) ?></p>
            <strong class="side-waiter-name"><?= e($user['name'] ?? 'Personel') ?></strong>
          </div>
        </div>
        <button class="icon-btn side-close" type="button" data-nav-close aria-label="Menüyü kapat">✕</button>
      </div>

      <?php if ($isAdminArea): ?>
        <p class="side-section-title">Restoran kontrol</p>
        <?php partial('partials/admin_side_nav'); ?>
      <?php else: ?>
        <nav class="side-cats" data-side-cats>
          <button class="side-cat active" type="button" data-cat-tab="all">
            <?php partial('partials/menu_icon', ['icon' => 'all', 'color' => '#e2b457']); ?>
            <span>Tümü</span>
          </button>
          <?php foreach ($staffCategories as $cat): ?>
            <?php
              $meta = $catalogBySlug[$cat['slug']] ?? CategorySync::meta($cat['slug']);
              $label = $catalogBySlug[$cat['slug']]['name'] ?? $cat['name'];
            ?>
            <button class="side-cat" type="button" data-cat-tab="<?= e($cat['slug']) ?>">
              <?php partial('partials/menu_icon', ['icon' => $meta['icon'], 'color' => $meta['color']]); ?>
              <span><?= e($label) ?></span>
            </button>
          <?php endforeach; ?>
        </nav>
      <?php endif; ?>

      <div class="userbox">
        <?php if ($role === 'admin' && !$isAdminArea): ?>
          <a class="side-link" href="<?= e(url('/yonetici')) ?>">
            <?php partial('partials/menu_icon', ['icon' => 'menu', 'color' => '#e2b457']); ?>
            <span>Yönetici</span>
          </a>
        <?php endif; ?>
        <?php if (in_array($role, ['cashier', 'admin'], true) && !str_starts_with(current_path(), '/kasa')): ?>
          <a class="side-link" href="<?= e(url('/kasa')) ?>">
            <?php partial('partials/menu_icon', ['icon' => 'cashier', 'color' => '#4c8dff']); ?>
            <span>Kasa</span>
          </a>
        <?php endif; ?>
        <?php if (in_array($role, ['waiter', 'admin'], true) && !str_starts_with(current_path(), '/garson') && current_path() !== '/siparisler'): ?>
          <a class="side-link" href="<?= e(url('/garson')) ?>">
            <?php partial('partials/menu_icon', ['icon' => 'waiter', 'color' => '#ff6a1a']); ?>
            <span>Garson</span>
          </a>
        <?php endif; ?>
        <form method="post" action="<?= e(url('/personel/cikis')) ?>" style="margin-top:10px">
          <button class="side-link" type="submit">
            <?php partial('partials/menu_icon', ['icon' => 'logout', 'color' => '#ff7a7a']); ?>
            <span>Çıkış</span>
          </button>
        </form>
      </div>
    </aside>

    <div class="staff-content">
      <header class="staff-topbar">
        <a class="brand-inline header-logo" href="<?= e($homeStaff) ?>" style="display:inline-flex;align-items:center;gap:8px">
          <img class="staff-logo" src="<?= e(logo_url()) ?>" alt="Crisp &amp; Co." width="72" height="34">
          <span class="brand-inline-text">Crisp</span>
        </a>
        <nav class="header-nav">
          <?php if ($role === 'admin'): ?>
            <a class="<?= str_starts_with(current_path(), '/yonetici') ? 'active' : '' ?>" href="<?= e(url('/yonetici')) ?>">Yönetici</a>
          <?php endif; ?>
          <?php if (in_array($role, ['waiter', 'admin'], true)): ?>
            <a class="<?= is_active_path('/garson') ? 'active' : '' ?>" href="<?= e(url('/garson')) ?>">Garson</a>
            <a class="<?= is_active_path('/siparisler') || str_starts_with(current_path(), '/garson/masa') ? 'active' : '' ?>" href="<?= e(url('/siparisler')) ?>">
              Siparişler <span class="nav-badge" data-cart-badge hidden>0</span>
            </a>
          <?php endif; ?>
          <?php if (in_array($role, ['cashier', 'admin'], true)): ?>
            <a class="<?= is_active_path('/kasa') || str_starts_with(current_path(), '/kasa/') ? 'active' : '' ?>" href="<?= e(url('/kasa')) ?>">Kasa</a>
            <a class="<?= is_active_path('/online-siparisler') ? 'active' : '' ?>" href="<?= e(url('/online-siparisler')) ?>">
              Online
              <span class="nav-badge<?= $pendingOnlineCount <= 0 ? ' is-empty' : '' ?>" data-online-badge<?= $pendingOnlineCount <= 0 ? ' hidden' : '' ?>><?= $pendingOnlineCount ?></span>
            </a>
            <a class="<?= is_active_path('/qr') ? 'active' : '' ?>" href="<?= e(url('/qr')) ?>">QR Menü</a>
          <?php endif; ?>
          <a class="<?= is_active_path('/mutfak') ? 'active' : '' ?>" href="<?= e(url('/mutfak')) ?>">Mutfak</a>
          <a class="<?= is_active_path('/bar') ? 'active' : '' ?>" href="<?= e(url('/bar')) ?>">Bar</a>
        </nav>
        <button
          class="btn btn-dark btn-menu"
          type="button"
          data-nav-toggle
          aria-expanded="false"
          aria-controls="staff-side"
        >
          <span class="menu-icon" aria-hidden="true">☰</span>
          <span data-nav-label>Menü</span>
        </button>
      </header>
      <main class="main">
        <?php if ($msg = flash('success')): ?><div class="alert alert-ok"><?= e($msg) ?></div><?php endif; ?>
        <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
        <?= $content ?>
      </main>
    </div>
  </div>
  <script>window.CHICKEN_BASE = <?= json_encode(base_path(), JSON_UNESCAPED_SLASHES) ?>;</script>
  <script src="<?= e(url('/assets/js/app.js')) ?>?v=<?= e((string) $jsVer) ?>" defer></script>
</body>
</html>

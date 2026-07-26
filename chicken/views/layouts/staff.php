<?php
/** @var string $title */
/** @var string $content */
/** @var array|null $user */
$role = $user['role'] ?? Auth::role();
CategorySync::ensure();
$staffCategories = [];
try {
    $staffCategories = Database::pdo()
        ->query('SELECT name, slug FROM categories WHERE is_active = 1 ORDER BY sort_order, id')
        ->fetchAll();
} catch (Throwable) {
    $staffCategories = [];
}
// Prefer curated catalog order/icons even if DB lags
$catalogBySlug = [];
foreach (CategorySync::catalog() as $row) {
    $catalogBySlug[$row['slug']] = $row;
}
$homeStaff = in_array($role, ['waiter', 'admin'], true) ? url('/garson') : (in_array($role, ['cashier'], true) ? url('/kasa') : url('/mutfak'));
?><!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($title ?? 'Chicken Personel') ?></title>
  <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>">
</head>
<body data-base="<?= e(base_path()) ?>" class="staff-body">
  <div class="layout-staff" data-staff-layout>
    <div class="side-backdrop" data-nav-close hidden></div>

    <aside class="side" id="staff-side" aria-hidden="true">
      <div class="side-top">
        <div class="side-waiter">
          <?php partial('partials/menu_icon', ['icon' => 'waiter', 'color' => '#ff6a1a']); ?>
          <div>
            <p class="side-label">Garson</p>
            <strong class="side-waiter-name"><?= e($user['name'] ?? 'Personel') ?></strong>
          </div>
        </div>
        <button class="icon-btn side-close" type="button" data-nav-close aria-label="Menüyü kapat">✕</button>
      </div>

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

      <div class="userbox">
        <?php if (in_array($role, ['cashier', 'admin'], true)): ?>
          <a class="side-link" href="<?= e(url('/kasa')) ?>">Kasa</a>
        <?php endif; ?>
        <?php if ($role === 'admin'): ?>
          <a class="side-link" href="<?= e(url('/yonetici')) ?>">Yönetici</a>
        <?php endif; ?>
        <form method="post" action="<?= e(url('/personel/cikis')) ?>" style="margin-top:10px">
          <button class="btn btn-ghost btn-sm" type="submit">Çıkış</button>
        </form>
      </div>
    </aside>

    <div class="staff-content">
      <header class="staff-topbar">
        <a class="brand-inline header-logo" href="<?= e($homeStaff) ?>">Chicken<span>.</span></a>
        <nav class="header-nav">
          <?php if (in_array($role, ['waiter', 'admin'], true)): ?>
            <a class="<?= is_active_path('/garson') ? 'active' : '' ?>" href="<?= e(url('/garson')) ?>">Garson</a>
          <?php endif; ?>
          <a class="<?= is_active_path('/mutfak') ? 'active' : '' ?>" href="<?= e(url('/mutfak')) ?>">Mutfak</a>
          <a class="<?= is_active_path('/bar') ? 'active' : '' ?>" href="<?= e(url('/bar')) ?>">Bar</a>
          <a class="<?= is_active_path('/qr') ? 'active' : '' ?>" href="<?= e(url('/qr')) ?>">QR Kodlar</a>
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
  <script src="<?= e(url('/assets/js/app.js')) ?>" defer></script>
</body>
</html>

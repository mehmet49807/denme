<?php
/** @var string $title */
/** @var string $content */
/** @var array|null $user */
$role = $user['role'] ?? Auth::role();
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
    <aside class="side" id="staff-side">
      <div class="side-top">
        <a class="brand" href="<?= e(url('/')) ?>">Chicken<span>.</span></a>
        <button class="icon-btn side-close" type="button" data-nav-close aria-label="Menüyü kapat">✕</button>
      </div>
      <nav>
        <?php if (in_array($role, ['waiter', 'admin'], true)): ?>
          <a class="<?= is_active_path('/garson') ? 'active' : '' ?>" href="<?= e(url('/garson')) ?>">Garson</a>
        <?php endif; ?>
        <?php if (in_array($role, ['cashier', 'admin'], true)): ?>
          <a class="<?= is_active_path('/kasa') ? 'active' : '' ?>" href="<?= e(url('/kasa')) ?>">Kasa</a>
        <?php endif; ?>
        <?php if ($role === 'admin'): ?>
          <a class="<?= is_active_path('/yonetici') ? 'active' : '' ?>" href="<?= e(url('/yonetici')) ?>">Yönetici</a>
        <?php endif; ?>
        <a class="<?= is_active_path('/mutfak') ? 'active' : '' ?>" href="<?= e(url('/mutfak')) ?>">Mutfak</a>
        <a class="<?= is_active_path('/bar') ? 'active' : '' ?>" href="<?= e(url('/bar')) ?>">Bar</a>
        <a href="<?= e(url('/qr')) ?>">QR Kodlar</a>
        <a href="<?= e(url('/menu')) ?>">Menü</a>
      </nav>
      <div class="userbox">
        <div><?= e($user['name'] ?? '') ?></div>
        <div class="small"><?= e($user['role'] ?? '') ?></div>
        <form method="post" action="<?= e(url('/personel/cikis')) ?>" style="margin-top:10px">
          <button class="btn btn-ghost btn-sm" type="submit">Çıkış</button>
        </form>
      </div>
    </aside>
    <div class="staff-content">
      <header class="staff-topbar">
        <button class="icon-btn" type="button" data-nav-open aria-label="Menüyü aç" aria-controls="staff-side">☰</button>
        <div class="staff-topbar-title">
          <span class="brand-inline">Chicken<span>.</span></span>
          <span class="small muted"><?= e($title ?? 'Personel') ?></span>
        </div>
        <span class="chip"><?= e($user['name'] ?? '') ?></span>
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

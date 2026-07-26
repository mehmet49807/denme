<?php
/** @var string $title */
/** @var string $content */
/** @var array|null $user */
$role = $user['role'] ?? Auth::role();
?><!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Chicken Personel') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
  <div class="layout-staff">
    <aside class="side">
      <a class="brand" href="/">Chicken<span>.</span></a>
      <nav>
        <?php if (in_array($role, ['waiter', 'admin'], true)): ?>
          <a class="<?= is_active_path('/garson') ? 'active' : '' ?>" href="/garson">Garson</a>
        <?php endif; ?>
        <?php if (in_array($role, ['cashier', 'admin'], true)): ?>
          <a class="<?= is_active_path('/kasa') ? 'active' : '' ?>" href="/kasa">Kasa</a>
        <?php endif; ?>
        <?php if ($role === 'admin'): ?>
          <a class="<?= is_active_path('/yonetici') ? 'active' : '' ?>" href="/yonetici">Yönetici</a>
        <?php endif; ?>
        <a class="<?= is_active_path('/mutfak') ? 'active' : '' ?>" href="/mutfak">Mutfak</a>
        <a class="<?= is_active_path('/bar') ? 'active' : '' ?>" href="/bar">Bar</a>
        <a href="/qr">QR Kodlar</a>
        <a href="/menu">Menü</a>
      </nav>
      <div class="userbox">
        <div><?= e($user['name'] ?? '') ?></div>
        <div class="small"><?= e($user['role'] ?? '') ?></div>
        <form method="post" action="/personel/cikis" style="margin-top:10px">
          <button class="btn btn-ghost btn-sm" type="submit">Çıkış</button>
        </form>
      </div>
    </aside>
    <main class="main">
      <?php if ($msg = flash('success')): ?><div class="alert alert-ok"><?= e($msg) ?></div><?php endif; ?>
      <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
      <?= $content ?>
    </main>
  </div>
  <script src="/assets/js/app.js" defer></script>
</body>
</html>

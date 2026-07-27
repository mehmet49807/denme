<?php
/** @var string $title */
/** @var string $content */
$customer = class_exists('CustomerAuth') ? CustomerAuth::user() : null;
?><!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <title><?= e($title ?? 'Chicken') ?></title>
  <meta name="description" content="Chicken restoran — QR menü, online sipariş ve sipariş takip.">
  <?php
    $assetRoot = dirname(__DIR__, 2) . '/assets';
    $cssVer = @filemtime($assetRoot . '/css/app.css') ?: time();
    $jsVer = @filemtime($assetRoot . '/js/app.js') ?: time();
  ?>
  <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>?v=<?= e((string) $cssVer) ?>">
</head>
<body<?= !empty($bodyAttrs) ? ' ' . $bodyAttrs : '' ?> data-base="<?= e(base_path()) ?>">
  <header class="site-header">
    <div class="nav">
      <a class="brand" href="<?= e(url('/')) ?>">Chicken<span>.</span></a>
      <nav class="nav-links">
        <a href="<?= e(url('/menu')) ?>">Menü</a>
        <a href="<?= e(url('/siparis')) ?>">Sipariş</a>
        <a href="<?= e(url('/takip')) ?>">Takip</a>
        <?php if ($customer): ?>
          <span class="nav-user muted small"><?= e((string) $customer['name']) ?></span>
          <form method="post" action="<?= e(url('/cikis')) ?>" style="display:inline;margin:0">
            <?= csrf_field() ?>
            <button class="btn btn-ghost btn-sm" type="submit">Çıkış</button>
          </form>
        <?php else: ?>
          <a href="<?= e(url('/giris')) ?>">Giriş</a>
          <a href="<?= e(url('/uye-ol')) ?>">Üye ol</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
  <?= $content ?>
  <footer class="footer">
    <div>Chicken · Izgara lezzet · QR menü · Online sipariş</div>
  </footer>
  <script>window.CHICKEN_BASE = <?= json_encode(base_path(), JSON_UNESCAPED_SLASHES) ?>;</script>
  <script src="<?= e(url('/assets/js/app.js')) ?>?v=<?= e((string) $jsVer) ?>" defer></script>
</body>
</html>

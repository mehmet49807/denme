<?php
/** @var string $title */
/** @var string $content */
?><!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
        <a href="<?= e(url('/personel/giris')) ?>">Personel</a>
      </nav>
    </div>
  </header>
  <?= $content ?>
  <footer class="footer">
    <div>Chicken · Izgara lezzet · QR menü · Online sipariş</div>
  </footer>
  <script>window.CHICKEN_BASE = <?= json_encode(base_path(), JSON_UNESCAPED_SLASHES) ?>;</script>
  <script src="<?= e(url('/assets/js/app.js')) ?>" defer></script>
</body>
</html>

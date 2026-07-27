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
  <link rel="icon" href="<?= e(url('/assets/img/logo.svg')) ?>" type="image/svg+xml">
</head>
<body<?= !empty($bodyAttrs) ? ' ' . $bodyAttrs : '' ?> data-base="<?= e(base_path()) ?>">
  <header class="site-header">
    <div class="nav">
      <a class="brand" href="<?= e(url('/')) ?>">
        <img class="brand-mark" src="<?= e(url('/assets/img/logo.svg')) ?>" alt="" width="36" height="36">
        <span class="brand-text">Chicken<span>.</span></span>
      </a>
      <div class="nav-auth">
        <?php if ($customer): ?>
          <span class="nav-user muted small"><?= e((string) $customer['name']) ?></span>
          <form method="post" action="<?= e(url('/cikis')) ?>" style="display:inline;margin:0">
            <?= csrf_field() ?>
            <button class="btn btn-ghost btn-sm" type="submit">Çıkış</button>
          </form>
        <?php else: ?>
          <a class="btn btn-nav-login btn-sm" href="<?= e(url('/giris')) ?>">Giriş</a>
          <a class="btn btn-accent btn-sm" href="<?= e(url('/uye-ol')) ?>">Üye ol</a>
        <?php endif; ?>
      </div>
      <nav class="nav-links" aria-label="Ana menü">
        <a href="<?= e(url('/menu')) ?>">Menü</a>
        <a href="<?= e(url('/siparis')) ?>">Sipariş</a>
        <a href="<?= e(url('/takip')) ?>">Takip</a>
        <a href="<?= e(url('/hakkimizda')) ?>">Hakkımızda</a>
      </nav>
    </div>
  </header>
  <?= $content ?>
  <footer class="footer site-footer">
    <div class="footer-glow" aria-hidden="true"></div>
    <div class="footer-inner footer-premium">
      <div class="footer-brand">
        <a class="brand footer-brand-link" href="<?= e(url('/')) ?>">
          <img class="brand-mark" src="<?= e(url('/assets/img/logo.svg')) ?>" alt="" width="40" height="40">
          <span class="brand-text">Chicken<span>.</span></span>
        </a>
        <p class="footer-tagline">Izgara lezzet · hızlı servis · online sipariş</p>
        <div class="footer-cta">
          <a class="btn btn-accent btn-sm" href="<?= e(url('/siparis')) ?>">Sipariş ver</a>
          <a class="btn btn-ghost btn-sm" href="<?= e(url('/menu')) ?>">Menü</a>
        </div>
      </div>
      <div class="footer-cols">
        <div class="footer-col">
          <h3>Keşfet</h3>
          <a href="<?= e(url('/menu')) ?>">Menü</a>
          <a href="<?= e(url('/siparis')) ?>">Online sipariş</a>
          <a href="<?= e(url('/takip')) ?>">Sipariş takip</a>
          <a href="<?= e(url('/menu/brosur')) ?>">QR menü broşürü</a>
        </div>
        <div class="footer-col">
          <h3>Kurumsal</h3>
          <a href="<?= e(url('/hakkimizda')) ?>">Hakkımızda</a>
          <a href="<?= e(url('/misyon')) ?>">Misyon</a>
          <a href="<?= e(url('/musteri-memnuniyeti')) ?>">Müşteri memnuniyeti</a>
        </div>
        <div class="footer-col">
          <h3>Sözleşmeler</h3>
          <a href="<?= e(url('/sozlesmeler/kvkk')) ?>">KVKK</a>
          <a href="<?= e(url('/sozlesmeler/gizlilik')) ?>">Gizlilik</a>
          <a href="<?= e(url('/sozlesmeler/mesafeli-satis')) ?>">Mesafeli satış</a>
          <a href="<?= e(url('/sozlesmeler/uyelik')) ?>">Üyelik sözleşmesi</a>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© <?= date('Y') ?> Chicken</span>
        <span class="footer-dot" aria-hidden="true"></span>
        <span>Yeni üyelere <strong>YENI10</strong> · %10 indirim</span>
      </div>
    </div>
  </footer>
  <script>window.CHICKEN_BASE = <?= json_encode(base_path(), JSON_UNESCAPED_SLASHES) ?>;</script>
  <script src="<?= e(url('/assets/js/app.js')) ?>?v=<?= e((string) $jsVer) ?>" defer></script>
</body>
</html>

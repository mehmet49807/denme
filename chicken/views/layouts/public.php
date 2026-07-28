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
  <title><?= e($title ?? 'Crisp & Co.') ?></title>
  <meta name="description" content="Crisp & Co. — lezzetin doğal adresi. QR menü, online sipariş ve sipariş takip.">
  <?php
    $assetRoot = dirname(__DIR__, 2) . '/assets';
    $cssVer = @filemtime($assetRoot . '/css/app.css') ?: time();
    $publicCssVer = @filemtime($assetRoot . '/css/public-site.css') ?: time();
    $jsVer = @filemtime($assetRoot . '/js/app.js') ?: time();
    $logoSrc = logo_url();
  ?>
  <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>?v=<?= e((string) $cssVer) ?>">
  <link rel="stylesheet" href="<?= e(url('/assets/css/public-site.css')) ?>?v=<?= e((string) $publicCssVer) ?>">
  <link rel="icon" href="<?= e($logoSrc) ?>" type="image/png">
</head>
<body<?= !empty($bodyAttrs) ? ' ' . $bodyAttrs : '' ?> data-base="<?= e(base_path()) ?>" data-public-layout data-theme="vista">
  <div class="vs-app">
    <header class="vs-top">
      <a class="vs-brand" href="<?= e(url('/')) ?>" aria-label="Crisp &amp; Co.">
        <img src="<?= e($logoSrc) ?>" alt="" width="52" height="52">
        <span>Crisp &amp; Co.</span>
      </a>
      <nav class="vs-nav" aria-label="Ana menü">
        <a href="<?= e(url('/menu')) ?>">Menü</a>
        <a href="<?= e(url('/siparis')) ?>">Sipariş</a>
        <a href="<?= e(url('/takip')) ?>">Takip</a>
        <a href="<?= e(url('/bayilik')) ?>">Franchise</a>
        <a href="<?= e(url('/hakkimizda')) ?>">Hakkımızda</a>
      </nav>
      <div class="vs-tools">
        <a class="vs-cart" href="<?= e(url('/siparis#sepet')) ?>" data-cart-link aria-label="Sepet">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 6h2l1.2 10.2a2 2 0 002 1.8h7.5a2 2 0 002-1.7L20 8H7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="10" cy="20" r="1.4" fill="currentColor"/>
            <circle cx="17" cy="20" r="1.4" fill="currentColor"/>
          </svg>
          <span class="nav-badge cart-badge is-empty" data-cart-badge hidden>0</span>
        </a>
        <?php if ($customer): ?>
          <span class="vs-user"><?= e((string) $customer['name']) ?></span>
          <form method="post" action="<?= e(url('/cikis')) ?>" class="vs-logout">
            <?= csrf_field() ?>
            <button type="submit">Çıkış</button>
          </form>
        <?php else: ?>
          <a class="vs-login" href="<?= e(url('/giris')) ?>">Giriş</a>
          <a class="vs-join" href="<?= e(url('/uye-ol')) ?>">Üye ol</a>
        <?php endif; ?>
      </div>
    </header>

    <main class="vs-main">
      <?= $content ?>
    </main>

    <footer class="vs-footer">
      <div class="vs-footer-inner">
        <div class="vs-footer-brand">
          <img src="<?= e($logoSrc) ?>" alt="Crisp &amp; Co." width="48" height="48">
          <div>
            <strong>Crisp &amp; Co.</strong>
            <p>Lezzetin doğal adresi</p>
          </div>
        </div>
        <nav class="vs-footer-links" aria-label="Alt menü">
          <a href="<?= e(url('/menu')) ?>">Menü</a>
          <a href="<?= e(url('/siparis')) ?>">Sipariş</a>
          <a href="<?= e(url('/takip')) ?>">Takip</a>
          <a href="<?= e(url('/bayilik')) ?>">Franchise</a>
          <a href="<?= e(url('/hakkimizda')) ?>">Hakkımızda</a>
          <a href="<?= e(url('/misyon')) ?>">Misyon</a>
          <a href="<?= e(url('/sozlesmeler/kvkk')) ?>">KVKK</a>
          <a href="<?= e(url('/sozlesmeler/gizlilik')) ?>">Gizlilik</a>
        </nav>
        <p class="vs-footer-copy">© <?= date('Y') ?> Crisp &amp; Co. · <b>YENI10</b> %10</p>
      </div>
    </footer>
  </div>

  <script>window.CHICKEN_BASE = <?= json_encode(base_path(), JSON_UNESCAPED_SLASHES) ?>;</script>
  <script src="<?= e(url('/assets/js/app.js')) ?>?v=<?= e((string) $jsVer) ?>" defer></script>
</body>
</html>

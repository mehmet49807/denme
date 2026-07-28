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
<body<?= !empty($bodyAttrs) ? ' ' . $bodyAttrs : '' ?> data-base="<?= e(base_path()) ?>" data-public-layout data-theme="blueplate">
  <header class="site-header bp-header">
    <div class="bp-nav">
      <a class="bp-brand" href="<?= e(url('/')) ?>">
        <img src="<?= e($logoSrc) ?>" alt="" width="40" height="40">
        <span>CRISP &amp; CO.</span>
      </a>
      <nav class="bp-links" aria-label="Ana menü">
        <a href="<?= e(url('/menu')) ?>">Menü</a>
        <a href="<?= e(url('/siparis')) ?>">Sipariş</a>
        <a href="<?= e(url('/takip')) ?>">Takip</a>
        <a href="<?= e(url('/hakkimizda')) ?>">Hakkımızda</a>
      </nav>
      <div class="bp-actions">
        <a class="bp-cart" href="<?= e(url('/siparis#sepet')) ?>" data-cart-link aria-label="Sepet">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 6h2l1.2 10.2a2 2 0 002 1.8h7.5a2 2 0 002-1.7L20 8H7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="10" cy="20" r="1.4" fill="currentColor"/>
            <circle cx="17" cy="20" r="1.4" fill="currentColor"/>
          </svg>
          <span class="nav-badge cart-badge is-empty" data-cart-badge hidden>0</span>
        </a>
        <?php if ($customer): ?>
          <span class="bp-user"><?= e((string) $customer['name']) ?></span>
          <form method="post" action="<?= e(url('/cikis')) ?>" class="bp-logout">
            <?= csrf_field() ?>
            <button type="submit">Çıkış</button>
          </form>
        <?php else: ?>
          <a class="bp-login" href="<?= e(url('/giris')) ?>">Giriş</a>
          <a class="bp-join" href="<?= e(url('/uye-ol')) ?>">Üye ol</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main class="bp-main">
    <?= $content ?>
  </main>

  <a class="bp-fab" href="<?= e(url('/siparis#sepet')) ?>" data-cart-link aria-label="Sepete git">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <path d="M4 6h2l1.2 10.2a2 2 0 002 1.8h7.5a2 2 0 002-1.7L20 8H7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      <circle cx="10" cy="20" r="1.4" fill="currentColor"/>
      <circle cx="17" cy="20" r="1.4" fill="currentColor"/>
    </svg>
    <span class="nav-badge cart-badge is-empty" data-cart-badge hidden>0</span>
  </a>

  <footer class="bp-footer">
    <div class="bp-footer-inner">
      <div class="bp-footer-brand">
        <img src="<?= e($logoSrc) ?>" alt="Crisp &amp; Co." width="52" height="52">
        <div>
          <strong>CRISP &amp; CO.</strong>
          <p>Lezzetin doğal adresi</p>
        </div>
      </div>
      <nav class="bp-footer-links" aria-label="Alt menü">
        <a href="<?= e(url('/menu')) ?>">Menü</a>
        <a href="<?= e(url('/siparis')) ?>">Sipariş</a>
        <a href="<?= e(url('/takip')) ?>">Takip</a>
        <a href="<?= e(url('/hakkimizda')) ?>">Hakkımızda</a>
        <a href="<?= e(url('/misyon')) ?>">Misyon</a>
        <a href="<?= e(url('/sozlesmeler/kvkk')) ?>">KVKK</a>
        <a href="<?= e(url('/sozlesmeler/gizlilik')) ?>">Gizlilik</a>
      </nav>
      <p class="bp-footer-copy">© <?= date('Y') ?> Crisp &amp; Co. · Yeni üyelere <b>YENI10</b></p>
    </div>
  </footer>
  <script>window.CHICKEN_BASE = <?= json_encode(base_path(), JSON_UNESCAPED_SLASHES) ?>;</script>
  <script src="<?= e(url('/assets/js/app.js')) ?>?v=<?= e((string) $jsVer) ?>" defer></script>
</body>
</html>

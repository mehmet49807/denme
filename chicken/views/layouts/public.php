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
<body<?= !empty($bodyAttrs) ? ' ' . $bodyAttrs : '' ?> data-base="<?= e(base_path()) ?>" data-public-layout data-theme="cedar">
  <header class="site-header site-header-cedar">
    <div class="nav nav-cedar">
      <a class="brand brand-cedar" href="<?= e(url('/')) ?>">
        <img class="brand-mark brand-mark-full" src="<?= e($logoSrc) ?>" alt="" width="48" height="48">
        <span class="brand-wordmark">Crisp <em>&amp;</em> Co.</span>
      </a>
      <nav class="nav-links" aria-label="Ana menü">
        <a class="nav-link" href="<?= e(url('/menu')) ?>">Menü</a>
        <a class="nav-link" href="<?= e(url('/siparis')) ?>">Sipariş</a>
        <a class="nav-link" href="<?= e(url('/takip')) ?>">Takip</a>
        <a class="nav-link" href="<?= e(url('/hakkimizda')) ?>">Hakkımızda</a>
      </nav>
      <div class="nav-auth">
        <a class="btn btn-cart btn-sm" href="<?= e(url('/siparis#sepet')) ?>" data-cart-link aria-label="Sepet">
          <span class="cart-ico" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M4 6h2l1.2 10.2a2 2 0 002 1.8h7.5a2 2 0 002-1.7L20 8H7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="10" cy="20" r="1.4" fill="currentColor"/>
              <circle cx="17" cy="20" r="1.4" fill="currentColor"/>
            </svg>
          </span>
          <span class="cart-label">Sepet</span>
          <span class="nav-badge cart-badge is-empty" data-cart-badge hidden>0</span>
        </a>
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
    </div>
  </header>

  <main class="public-main">
    <?= $content ?>
  </main>

  <a class="cart-fab cart-fab-cedar" href="<?= e(url('/siparis#sepet')) ?>" data-cart-link aria-label="Sepete git">
    <span class="cart-ico" aria-hidden="true">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
        <path d="M4 6h2l1.2 10.2a2 2 0 002 1.8h7.5a2 2 0 002-1.7L20 8H7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="10" cy="20" r="1.4" fill="currentColor"/>
        <circle cx="17" cy="20" r="1.4" fill="currentColor"/>
      </svg>
    </span>
    <span class="nav-badge cart-badge is-empty" data-cart-badge hidden>0</span>
  </a>

  <footer class="site-footer site-footer-cedar">
    <div class="footer-cedar-inner">
      <div class="footer-cedar-brand">
        <a class="brand brand-cedar" href="<?= e(url('/')) ?>">
          <img class="brand-mark brand-mark-full" src="<?= e($logoSrc) ?>" alt="" width="56" height="56">
          <span class="brand-wordmark">Crisp <em>&amp;</em> Co.</span>
        </a>
        <p>Lezzetin doğal adresi</p>
      </div>
      <nav class="footer-cedar-links" aria-label="Alt menü">
        <a href="<?= e(url('/menu')) ?>">Menü</a>
        <a href="<?= e(url('/siparis')) ?>">Sipariş</a>
        <a href="<?= e(url('/takip')) ?>">Takip</a>
        <a href="<?= e(url('/hakkimizda')) ?>">Hakkımızda</a>
        <a href="<?= e(url('/misyon')) ?>">Misyon</a>
        <a href="<?= e(url('/sozlesmeler/kvkk')) ?>">KVKK</a>
        <a href="<?= e(url('/sozlesmeler/gizlilik')) ?>">Gizlilik</a>
      </nav>
      <p class="footer-cedar-copy">© <?= date('Y') ?> Crisp &amp; Co. · Yeni üyelere <strong>YENI10</strong></p>
    </div>
  </footer>
  <script>window.CHICKEN_BASE = <?= json_encode(base_path(), JSON_UNESCAPED_SLASHES) ?>;</script>
  <script src="<?= e(url('/assets/js/app.js')) ?>?v=<?= e((string) $jsVer) ?>" defer></script>
</body>
</html>

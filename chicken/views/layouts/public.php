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
    $jsVer = @filemtime($assetRoot . '/js/app.js') ?: time();
    $logoSrc = logo_url();
  ?>
  <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>?v=<?= e((string) $cssVer) ?>">
  <link rel="icon" href="<?= e($logoSrc) ?>" type="image/png">
</head>
<body<?= !empty($bodyAttrs) ? ' ' . $bodyAttrs : '' ?> data-base="<?= e(base_path()) ?>" data-public-layout>
  <header class="site-header">
    <div class="nav">
      <a class="brand brand-logo-only" href="<?= e(url('/')) ?>" aria-label="Crisp &amp; Co.">
        <img class="brand-mark brand-mark-full" src="<?= e($logoSrc) ?>" alt="Crisp &amp; Co." width="58" height="58">
      </a>
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
      <nav class="nav-links" aria-label="Ana menü">
        <a class="nav-link" href="<?= e(url('/menu')) ?>">
          <span class="nav-ico nav-ico-menu" aria-hidden="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M4 6h16M4 12h16M4 18h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </span>
          Menü
        </a>
        <a class="nav-link" href="<?= e(url('/siparis')) ?>">
          <span class="nav-ico nav-ico-order" aria-hidden="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M4 6h2l1.2 10.2a2 2 0 002 1.8h7.5a2 2 0 002-1.7L20 8H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="10" cy="20" r="1.4" fill="currentColor"/>
              <circle cx="17" cy="20" r="1.4" fill="currentColor"/>
            </svg>
          </span>
          Sipariş
        </a>
        <a class="nav-link" href="<?= e(url('/takip')) ?>">
          <span class="nav-ico nav-ico-track" aria-hidden="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/>
              <path d="M12 8v4l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          Takip
        </a>
        <a class="nav-link" href="<?= e(url('/hakkimizda')) ?>">
          <span class="nav-ico nav-ico-about" aria-hidden="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="8" r="3.2" stroke="currentColor" stroke-width="2"/>
              <path d="M5.5 19c1.6-3.2 4-4.8 6.5-4.8S16.9 15.8 18.5 19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </span>
          Hakkımızda
        </a>
      </nav>
    </div>
  </header>
  <?= $content ?>

  <a class="cart-fab" href="<?= e(url('/siparis#sepet')) ?>" data-cart-link aria-label="Sepete git">
    <span class="cart-ico" aria-hidden="true">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
        <path d="M4 6h2l1.2 10.2a2 2 0 002 1.8h7.5a2 2 0 002-1.7L20 8H7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="10" cy="20" r="1.4" fill="currentColor"/>
        <circle cx="17" cy="20" r="1.4" fill="currentColor"/>
      </svg>
    </span>
    <span class="nav-badge cart-badge is-empty" data-cart-badge hidden>0</span>
  </a>

  <footer class="footer site-footer">
    <div class="footer-glow" aria-hidden="true"></div>
    <div class="footer-inner footer-premium">
      <div class="footer-brand">
        <a class="brand footer-brand-link brand-logo-only" href="<?= e(url('/')) ?>" aria-label="Crisp &amp; Co.">
          <img class="brand-mark brand-mark-full" src="<?= e($logoSrc) ?>" alt="Crisp &amp; Co." width="76" height="76">
        </a>
        <p class="footer-tagline">Lezzetin doğal adresi · ızgara tavuk · online sipariş</p>
        <div class="footer-cta">
          <a class="btn btn-accent btn-sm" href="<?= e(url('/siparis')) ?>">Sipariş ver</a>
          <a class="btn btn-ghost btn-sm" href="<?= e(url('/menu')) ?>">Menü</a>
        </div>
      </div>
      <div class="footer-cols">
        <div class="footer-col">
          <h3>
            <span class="footer-col-icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M7 7V5.5A1.5 1.5 0 018.5 4h7A1.5 1.5 0 0117 5.5V7M6 7l1 12h10l1-12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            Keşfet
          </h3>
          <a href="<?= e(url('/menu')) ?>">Menü</a>
          <a href="<?= e(url('/siparis')) ?>">Online sipariş</a>
          <a href="<?= e(url('/takip')) ?>">Sipariş takip</a>
          <a href="<?= e(url('/menu/brosur')) ?>">QR menü broşürü</a>
        </div>
        <div class="footer-col">
          <h3>
            <span class="footer-col-icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3z" stroke="currentColor" stroke-width="1.8"/><path d="M12 12l8-4.5M12 12v9M12 12L4 7.5" stroke="currentColor" stroke-width="1.8"/></svg>
            </span>
            Kurumsal
          </h3>
          <a href="<?= e(url('/hakkimizda')) ?>">Hakkımızda</a>
          <a href="<?= e(url('/misyon')) ?>">Misyon</a>
          <a href="<?= e(url('/musteri-memnuniyeti')) ?>">Müşteri memnuniyeti</a>
        </div>
        <div class="footer-col">
          <h3>
            <span class="footer-col-icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z" stroke="currentColor" stroke-width="1.8"/><path d="M14 3v5h5M8 13h8M8 17h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </span>
            Sözleşmeler
          </h3>
          <a href="<?= e(url('/sozlesmeler/kvkk')) ?>">KVKK</a>
          <a href="<?= e(url('/sozlesmeler/gizlilik')) ?>">Gizlilik</a>
          <a href="<?= e(url('/sozlesmeler/mesafeli-satis')) ?>">Mesafeli satış</a>
          <a href="<?= e(url('/sozlesmeler/uyelik')) ?>">Üyelik sözleşmesi</a>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© <?= date('Y') ?> Crisp &amp; Co.</span>
        <span class="footer-dot" aria-hidden="true"></span>
        <span>Yeni üyelere <strong>YENI10</strong> · %10 indirim</span>
      </div>
    </div>
  </footer>
  <script>window.CHICKEN_BASE = <?= json_encode(base_path(), JSON_UNESCAPED_SLASHES) ?>;</script>
  <script src="<?= e(url('/assets/js/app.js')) ?>?v=<?= e((string) $jsVer) ?>" defer></script>
</body>
</html>

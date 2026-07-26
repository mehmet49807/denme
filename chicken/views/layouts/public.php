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
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body<?= !empty($bodyAttrs) ? ' ' . $bodyAttrs : '' ?>>
  <header class="site-header">
    <div class="nav">
      <a class="brand" href="/">Chicken<span>.</span></a>
      <nav class="nav-links">
        <a href="/menu">Menü</a>
        <a href="/siparis">Sipariş</a>
        <a href="/takip">Takip</a>
        <a href="/personel/giris">Personel</a>
      </nav>
    </div>
  </header>
  <?= $content ?>
  <footer class="footer">
    <div>Chicken · Izgara lezzet · QR menü · Online sipariş</div>
  </footer>
  <script src="/assets/js/app.js" defer></script>
</body>
</html>

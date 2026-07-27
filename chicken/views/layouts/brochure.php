<?php
/** @var string $title */
/** @var string $content */
/** @var string $themeId */
$themeId = preg_replace('/[^a-z0-9_-]/i', '', (string) ($themeId ?? 'classic')) ?: 'classic';
?><!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <title><?= e($title ?? 'Chicken Menü') ?></title>
  <?php
    $assetRoot = dirname(__DIR__, 2) . '/assets';
    $cssVer = @filemtime($assetRoot . '/css/app.css') ?: time();
  ?>
  <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>?v=<?= e((string) $cssVer) ?>">
  <link rel="icon" href="<?= e(url('/assets/img/logo.svg')) ?>" type="image/svg+xml">
</head>
<body class="brochure-body theme-<?= e($themeId) ?>" data-base="<?= e(base_path()) ?>" data-theme="<?= e($themeId) ?>">
  <?= $content ?>
  <script>window.CHICKEN_BASE = <?= json_encode(base_path(), JSON_UNESCAPED_SLASHES) ?>;</script>
</body>
</html>

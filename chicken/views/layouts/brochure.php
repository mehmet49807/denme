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
  <title><?= e($title ?? 'Crisp & Co. Menü') ?></title>
  <?php
    $assetRoot = dirname(__DIR__, 2) . '/assets';
    $cssVer = @filemtime($assetRoot . '/css/app.css') ?: time();
  ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&family=Outfit:wght@500;600;700;800&family=Fraunces:opsz,wght@84,600;84,700&family=Patrick+Hand&family=Sora:wght@500;600;700&display=swap">
  <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>?v=<?= e((string) $cssVer) ?>">
  <link rel="icon" href="<?= e(logo_url()) ?>" type="image/png">
</head>
<body
  class="brochure-body theme-<?= e($themeId) ?>"
  data-base="<?= e(base_path()) ?>"
  data-theme="<?= e($themeId) ?>"
  data-layout="<?= e(class_exists('BrochureService') ? BrochureService::themeLayout($themeId) : 'list') ?>"
>
  <?= $content ?>
  <script>window.CHICKEN_BASE = <?= json_encode(base_path(), JSON_UNESCAPED_SLASHES) ?>;</script>
</body>
</html>

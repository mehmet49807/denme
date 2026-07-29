<?php
/**
 * Menü broşürü QR — ortasında logo.
 * Görselde CSS ile logo bindirilir; indirilen PNG ayrıca logolu üretilir.
 *
 * @var string $qrImageUrl  Düz/yüksek ECC QR
 * @var string|null $logoUrl
 * @var int|null $size
 * @var string|null $alt
 */
$qrImageUrl = (string) ($qrImageUrl ?? '');
$logoUrl = (string) ($logoUrl ?? (function_exists('logo_url') ? logo_url() : ''));
$size = (int) ($size ?? 240);
$alt = (string) ($alt ?? 'Menü Broşürü QR');
$size = max(100, min(480, $size));
?>
<div class="qr-brand" style="--qr-size: <?= $size ?>px">
  <img
    class="qr-brand-code"
    src="<?= e($qrImageUrl) ?>"
    alt="<?= e($alt) ?>"
    width="<?= $size ?>"
    height="<?= $size ?>"
  >
  <?php if ($logoUrl !== ''): ?>
    <span class="qr-brand-logo" aria-hidden="true">
      <img src="<?= e($logoUrl) ?>" alt="" width="72" height="72">
    </span>
  <?php endif; ?>
</div>

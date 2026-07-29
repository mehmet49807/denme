<?php
/** @var bool $canEdit */
/** @var string $brochureUrl */
/** @var string $qrImageUrl */
/** @var string|null $logoUrl */
/** @var string|null $selectedThemeName */
$canEdit = !empty($canEdit);
$selectedThemeName = $selectedThemeName ?? null;
$logoUrl = $logoUrl ?? logo_url();
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">QR menü</p>
    <h1>QR Menü</h1>
  </div>
  <div class="cta-row">
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/menu/brosur')) ?>" target="_blank" rel="noopener">Broşürü aç</a>
    <?php if ($canEdit): ?>
      <a class="btn btn-accent btn-sm" href="<?= e(url('/yonetici/brosurler')) ?>">Broşür temaları</a>
    <?php endif; ?>
  </div>
</div>

<?php if ($canEdit): ?>
  <p class="muted" style="margin-top:-8px">
    Müşteri bu QR’ı okutunca aktif broşür temasıyla menüyü görür. QR’ın ortasında Crisp &amp; Co. logosu vardır.
    <?php if ($selectedThemeName): ?>
      · Seçili tema: <strong><?= e($selectedThemeName) ?></strong>
    <?php endif; ?>
  </p>
<?php else: ?>
  <p class="muted" style="margin-top:-8px">
    Yalnızca görüntüleme. Yazdırıp masaya koyabilirsiniz. Ortadaki logo markayı güçlendirir.
  </p>
<?php endif; ?>

<div class="qr-single panel" style="margin-top:20px;max-width:420px">
  <strong style="display:block;margin-bottom:12px;font-family:var(--font-display);font-size:1.2rem">Menü Broşürü QR</strong>
  <?php partial('partials/qr_brand', [
      'qrImageUrl' => $qrImageUrl,
      'logoUrl' => $logoUrl,
      'size' => 240,
      'alt' => 'Menü Broşürü QR',
  ]); ?>
  <div class="small muted" style="margin-top:12px;word-break:break-all"><?= e($brochureUrl) ?></div>
  <div class="cta-row" style="margin-top:16px">
    <a class="btn btn-accent btn-sm" href="<?= e(BrochureService::qrBrandedDownloadUrl(480)) ?>" target="_blank" rel="noopener" download="crisp-co-menu-qr.png">QR indir</a>
    <button class="btn btn-ghost btn-sm" type="button" onclick="window.print()">Yazdır</button>
  </div>
</div>

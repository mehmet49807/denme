<?php
/** @var string $brochureUrl */
/** @var string $qrImageUrl */
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">QR menü</p>
    <h1>QR Menü</h1>
  </div>
  <a class="btn btn-ghost btn-sm" href="<?= e(url('/menu/brosur')) ?>" target="_blank" rel="noopener">Broşürü aç</a>
</div>

<p class="muted" style="margin-top:-8px">
  Eski masa QR kodları kaldırıldı. Tek bir QR menü var: müşteri okutunca menü broşürüne gider.
</p>

<div class="qr-single panel" style="margin-top:20px;max-width:420px">
  <strong style="display:block;margin-bottom:12px;font-family:var(--font-display);font-size:1.2rem">Menü Broşürü</strong>
  <img
    class="qr-single-img"
    alt="QR Menü Broşürü"
    width="240"
    height="240"
    src="<?= e($qrImageUrl) ?>"
  >
  <div class="small muted" style="margin-top:12px;word-break:break-all"><?= e($brochureUrl) ?></div>
  <div class="cta-row" style="margin-top:16px">
    <a class="btn btn-accent btn-sm" href="<?= e($qrImageUrl) ?>" download="chicken-qr-menu.png" target="_blank" rel="noopener">QR indir</a>
    <button class="btn btn-ghost btn-sm" type="button" onclick="window.print()">Yazdır</button>
  </div>
</div>

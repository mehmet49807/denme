<?php
/** @var array $invoice */
/** @var array $lines */
$lines = $lines ?? [];
if ($lines === [] && !empty($invoice['lines_json'])) {
    $decoded = json_decode((string) $invoice['lines_json'], true);
    $lines = is_array($decoded) ? $decoded : [];
}
?>
<div class="panel-head no-print">
  <div>
    <p class="eyebrow">Satış fişi</p>
    <h1><?= e((string) $invoice['invoice_no']) ?></h1>
  </div>
  <div class="cta-row">
    <button class="btn btn-primary btn-sm" type="button" onclick="window.print()">Yazdır</button>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/kasa/faturalar')) ?>">Faturalar</a>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/kasa')) ?>">Kasaya dön</a>
  </div>
</div>

<div class="tr-fis-wrap">
  <?php partial('partials/tr_receipt', [
      'invoice' => $invoice,
      'lines' => $lines,
      'printedAt' => date('d.m.Y H:i'),
  ]); ?>
</div>

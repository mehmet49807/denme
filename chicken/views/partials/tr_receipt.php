<?php
/**
 * Türkiye restoran satış fişi (bilgi amaçlı / KDV dahil).
 *
 * @var array $invoice  company_*, buyer_*, invoice_no, invoice_date, order_code?, table_label?, payment_method?, net_total, vat_total, gross_total, vat_rate?
 * @var list<array> $lines
 * @var string|null $printedAt
 */
$lines = $lines ?? [];
$printedAt = $printedAt ?? date('d.m.Y H:i');
$invoiceDate = (string) ($invoice['invoice_date'] ?? date('Y-m-d'));
try {
    $dateLabel = (new DateTimeImmutable($invoiceDate))->format('d.m.Y');
} catch (Throwable) {
    $dateLabel = $invoiceDate;
}
$timeLabel = date('H:i');
if (preg_match('/(\d{1,2}:\d{2})/', (string) $printedAt, $m)) {
    $timeLabel = $m[1];
}

$vatByRate = [];
foreach ($lines as $line) {
    $rate = (float) ($line['vat_rate'] ?? $invoice['vat_rate'] ?? 10);
    $vatByRate[(string) $rate] = ($vatByRate[(string) $rate] ?? 0.0) + (float) ($line['vat'] ?? 0);
}
ksort($vatByRate, SORT_NUMERIC);

$pay = payment_method_label($invoice['payment_method'] ?? null);
?>
<article class="tr-fis" aria-label="Satış fişi">
  <header class="tr-fis-head">
    <div class="tr-fis-brand"><?= e((string) ($invoice['company_title'] ?? 'Crisp & Co.')) ?></div>
    <?php if (!empty($invoice['company_address'])): ?>
      <div class="tr-fis-line"><?= e((string) $invoice['company_address']) ?></div>
    <?php endif; ?>
    <?php if (!empty($invoice['company_city'])): ?>
      <div class="tr-fis-line"><?= e((string) $invoice['company_city']) ?></div>
    <?php endif; ?>
    <?php if (!empty($invoice['company_phone'])): ?>
      <div class="tr-fis-line">Tel: <?= e((string) $invoice['company_phone']) ?></div>
    <?php endif; ?>
    <?php if (!empty($invoice['company_tax_office']) || !empty($invoice['company_vkn'])): ?>
      <div class="tr-fis-line">
        <?php if (!empty($invoice['company_tax_office'])): ?>
          VD: <?= e((string) $invoice['company_tax_office']) ?>
        <?php endif; ?>
        <?php if (!empty($invoice['company_vkn'])): ?>
          · VKN/TCKN: <?= e((string) $invoice['company_vkn']) ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </header>

  <div class="tr-fis-rule" aria-hidden="true"></div>
  <div class="tr-fis-title">SATIŞ FİŞİ</div>
  <div class="tr-fis-rule" aria-hidden="true"></div>

  <div class="tr-fis-meta">
    <div><span>Fiş No</span><strong><?= e((string) ($invoice['invoice_no'] ?? '')) ?></strong></div>
    <div><span>Tarih</span><strong><?= e($dateLabel) ?></strong></div>
    <div><span>Saat</span><strong><?= e($timeLabel) ?></strong></div>
    <?php if (!empty($invoice['order_code'])): ?>
      <div><span>Sipariş</span><strong><?= e((string) $invoice['order_code']) ?></strong></div>
    <?php endif; ?>
    <?php if (!empty($invoice['table_label'])): ?>
      <div><span>Masa</span><strong><?= e((string) $invoice['table_label']) ?></strong></div>
    <?php endif; ?>
  </div>

  <div class="tr-fis-rule" aria-hidden="true"></div>

  <table class="tr-fis-items">
    <thead>
      <tr>
        <th class="tr-fis-col-name">Ürün / Hizmet</th>
        <th class="tr-fis-col-qty">Adet</th>
        <th class="tr-fis-col-kdv">KDV</th>
        <th class="tr-fis-col-amt">Tutar</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($lines as $line): ?>
        <?php $lineRate = (float) ($line['vat_rate'] ?? $invoice['vat_rate'] ?? 10); ?>
        <tr>
          <td>
            <?= e((string) ($line['name'] ?? '')) ?>
            <div class="tr-fis-unit">Birim: <?= e(money((float) ($line['unit_price'] ?? 0))) ?> (KDV dahil)</div>
          </td>
          <td class="tr-fis-col-qty"><?= (int) ($line['qty'] ?? 0) ?></td>
          <td class="tr-fis-col-kdv"><?= e(format_vat_rate($lineRate)) ?></td>
          <td class="tr-fis-col-amt"><?= e(money((float) ($line['gross'] ?? 0))) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="tr-fis-rule" aria-hidden="true"></div>

  <div class="tr-fis-totals">
    <div><span>TOPKDV HARİÇ (Matrah)</span><strong><?= e(money((float) ($invoice['net_total'] ?? 0))) ?></strong></div>
    <?php foreach ($vatByRate as $rateKey => $vatAmount): ?>
      <div><span>KDV <?= e(format_vat_rate((float) $rateKey)) ?></span><strong><?= e(money((float) $vatAmount)) ?></strong></div>
    <?php endforeach; ?>
    <?php if ($vatByRate === []): ?>
      <div><span>KDV <?= e(format_vat_rate($invoice['vat_rate'] ?? 10)) ?></span><strong><?= e(money((float) ($invoice['vat_total'] ?? 0))) ?></strong></div>
    <?php endif; ?>
    <div class="tr-fis-grand"><span>TOPLAM</span><strong><?= e(money((float) ($invoice['gross_total'] ?? 0))) ?></strong></div>
    <div><span>ÖDEME</span><strong><?= e($pay) ?></strong></div>
  </div>

  <div class="tr-fis-rule" aria-hidden="true"></div>

  <section class="tr-fis-buyer">
    <div class="tr-fis-buyer-title">ALICI</div>
    <div><strong><?= e((string) ($invoice['buyer_name'] ?? 'Nihai Tüketici')) ?></strong></div>
    <?php if (!empty($invoice['buyer_tax_id'])): ?>
      <div>VKN/TCKN: <?= e((string) $invoice['buyer_tax_id']) ?></div>
    <?php endif; ?>
    <?php if (!empty($invoice['buyer_tax_office'])): ?>
      <div>Vergi Dairesi: <?= e((string) $invoice['buyer_tax_office']) ?></div>
    <?php endif; ?>
    <?php if (!empty($invoice['buyer_address'])): ?>
      <div><?= e((string) $invoice['buyer_address']) ?></div>
    <?php endif; ?>
  </section>

  <div class="tr-fis-rule" aria-hidden="true"></div>

  <footer class="tr-fis-foot">
    <p class="tr-fis-thanks">Afiyet olsun!</p>
    <p class="tr-fis-brand-foot"><?= e((string) ($invoice['company_title'] ?? 'Crisp & Co.')) ?></p>
    <p class="tr-fis-legal">
      Fiyatlar KDV dahildir. Bu belge restoran satış kaydı / bilgi fişidir;
      GİB e-Fatura veya e-Arşiv belgesi değildir.
    </p>
    <p class="tr-fis-print">Yazdırma: <?= e($printedAt) ?></p>
  </footer>
</article>

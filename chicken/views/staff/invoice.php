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
    <p class="eyebrow">Satış faturası</p>
    <h1><?= e((string) $invoice['invoice_no']) ?></h1>
  </div>
  <div class="cta-row">
    <button class="btn btn-primary btn-sm" type="button" onclick="window.print()">Yazdır</button>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/kasa')) ?>">Kasaya dön</a>
  </div>
</div>

<article class="panel invoice-doc">
  <header class="invoice-head">
    <div>
      <strong class="invoice-brand"><?= e((string) $invoice['company_title']) ?></strong>
      <?php if (!empty($invoice['company_vkn'])): ?>
        <div class="small">VKN/TCKN: <?= e((string) $invoice['company_vkn']) ?></div>
      <?php endif; ?>
      <?php if (!empty($invoice['company_tax_office'])): ?>
        <div class="small">Vergi Dairesi: <?= e((string) $invoice['company_tax_office']) ?></div>
      <?php endif; ?>
      <?php if (!empty($invoice['company_address'])): ?>
        <div class="small"><?= e((string) $invoice['company_address']) ?></div>
      <?php endif; ?>
      <?php if (!empty($invoice['company_city'])): ?>
        <div class="small"><?= e((string) $invoice['company_city']) ?></div>
      <?php endif; ?>
      <?php if (!empty($invoice['company_phone'])): ?>
        <div class="small">Tel: <?= e((string) $invoice['company_phone']) ?></div>
      <?php endif; ?>
    </div>
    <div class="invoice-meta">
      <h2>SATIŞ FATURASI</h2>
      <div><strong>Fatura No:</strong> <?= e((string) $invoice['invoice_no']) ?></div>
      <div><strong>Tarih:</strong> <?= e((string) $invoice['invoice_date']) ?></div>
      <div><strong>Sipariş:</strong> <?= e((string) ($invoice['order_code'] ?? '')) ?></div>
      <?php if (!empty($invoice['table_label'])): ?>
        <div><strong>Masa:</strong> <?= e((string) $invoice['table_label']) ?></div>
      <?php endif; ?>
      <div class="small muted" style="margin-top:8px">KDV dahil · Bilgi amaçlı satış belgesi</div>
    </div>
  </header>

  <section class="invoice-buyer">
    <h3>Alıcı</h3>
    <div><strong><?= e((string) $invoice['buyer_name']) ?></strong></div>
    <?php if (!empty($invoice['buyer_tax_id'])): ?>
      <div class="small">VKN/TCKN: <?= e((string) $invoice['buyer_tax_id']) ?></div>
    <?php endif; ?>
    <?php if (!empty($invoice['buyer_tax_office'])): ?>
      <div class="small">Vergi Dairesi: <?= e((string) $invoice['buyer_tax_office']) ?></div>
    <?php endif; ?>
    <?php if (!empty($invoice['buyer_address'])): ?>
      <div class="small"><?= e((string) $invoice['buyer_address']) ?></div>
    <?php endif; ?>
  </section>

  <div class="table-wrap" style="margin-top:16px">
    <table>
      <thead>
        <tr>
          <th>Ürün / Hizmet</th>
          <th>Adet</th>
          <th>Birim (KDV dahil)</th>
          <th>Tutar</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($lines as $line): ?>
          <tr>
            <td><?= e((string) ($line['name'] ?? '')) ?></td>
            <td><?= (int) ($line['qty'] ?? 0) ?></td>
            <td><?= e(money((float) ($line['unit_price'] ?? 0))) ?></td>
            <td><?= e(money((float) ($line['gross'] ?? 0))) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <section class="invoice-totals">
    <div class="meta-row"><span>Matrah (KDV hariç)</span><strong><?= e(money((float) $invoice['net_total'])) ?></strong></div>
    <div class="meta-row"><span>KDV (%<?= e(rtrim(rtrim(number_format((float) $invoice['vat_rate'], 2, ',', ''), '0'), ',')) ?>)</span><strong><?= e(money((float) $invoice['vat_total'])) ?></strong></div>
    <div class="meta-row invoice-grand"><span>Genel toplam</span><strong><?= e(money((float) $invoice['gross_total'])) ?></strong></div>
    <div class="meta-row"><span>Ödeme</span><strong><?= e(payment_method_label($invoice['payment_method'] ?? null)) ?></strong></div>
  </section>

  <p class="small muted" style="margin-top:18px">
    Bu belge restoran satış kaydı içindir. Fiyatlar KDV dahildir. GİB e-Fatura/e-Arşiv entegrasyonu değildir;
    resmi e-belge gerekiyorsa muhasebe/entegratör üzerinden ayrıca düzenlenmelidir.
  </p>
</article>

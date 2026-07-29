<?php
/** @var array $order */
$company = FiscalService::companyProfile();
$printedAt = date('d.m.Y H:i');
$allItems = array_values(array_filter(
    $order['items'] ?? [],
    static fn(array $i): bool => ($i['status'] ?? '') !== 'cancelled'
));
?>
<div class="panel-head no-print">
  <div>
    <p class="eyebrow">Sipariş fişleri</p>
    <h1><?= e($order['order_code']) ?></h1>
  </div>
  <div class="cta-row">
    <button class="btn btn-primary btn-sm" type="button" onclick="window.print()">Yazdır</button>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/garson')) ?>">Panele dön</a>
  </div>
</div>

<div class="panel no-print" style="margin-bottom:16px">
  <?php partial('partials/order_note', ['order' => $order]); ?>
</div>

<div class="slips">
  <section class="slip tr-station-slip">
    <div class="tr-station-head">
      <strong><?= e((string) $company['title']) ?></strong>
      <h2>MUTFAK FİŞİ</h2>
    </div>
    <div class="tr-station-meta">
      <div><span>Sipariş</span><strong><?= e($order['order_code']) ?></strong></div>
      <div><span>Masa</span><strong><?= e($order['table_label'] ?? 'Paket / Online') ?></strong></div>
      <div><span>Kaynak</span><strong><?= e(source_label($order['source'])) ?></strong></div>
      <div><span>Saat</span><strong><?= e($printedAt) ?></strong></div>
    </div>
    <?php if (!empty($order['customer_note'])): ?>
      <p class="tr-station-note"><strong>Sipariş notu:</strong> <?= e($order['customer_note']) ?></p>
    <?php endif; ?>
    <div class="tr-fis-rule" aria-hidden="true"></div>
    <?php if ($order['kitchen_items']): ?>
      <ul class="tr-station-list">
        <?php foreach ($order['kitchen_items'] as $item): ?>
          <li>
            <strong><?= (int) $item['quantity'] ?>× <?= e($item['item_name']) ?></strong>
            <?php if (!empty($item['note'])): ?><div class="small"><?= e($item['note']) ?></div><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="muted">Mutfak ürünü yok.</p>
    <?php endif; ?>
  </section>

  <section class="slip tr-station-slip">
    <div class="tr-station-head">
      <strong><?= e((string) $company['title']) ?></strong>
      <h2>BAR FİŞİ</h2>
    </div>
    <div class="tr-station-meta">
      <div><span>Sipariş</span><strong><?= e($order['order_code']) ?></strong></div>
      <div><span>Masa</span><strong><?= e($order['table_label'] ?? 'Paket / Online') ?></strong></div>
      <div><span>Kaynak</span><strong><?= e(source_label($order['source'])) ?></strong></div>
      <div><span>Saat</span><strong><?= e($printedAt) ?></strong></div>
    </div>
    <?php if (!empty($order['customer_note'])): ?>
      <p class="tr-station-note"><strong>Sipariş notu:</strong> <?= e($order['customer_note']) ?></p>
    <?php endif; ?>
    <div class="tr-fis-rule" aria-hidden="true"></div>
    <?php if ($order['bar_items']): ?>
      <ul class="tr-station-list">
        <?php foreach ($order['bar_items'] as $item): ?>
          <li>
            <strong><?= (int) $item['quantity'] ?>× <?= e($item['item_name']) ?></strong>
            <?php if (!empty($item['note'])): ?><div class="small"><?= e($item['note']) ?></div><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="muted">Bar ürünü yok.</p>
    <?php endif; ?>
  </section>

  <?php
    // Kasa fişi: ödeme öncesi/sonrası Türkiye satış fişi görünümü
    $previewLines = [];
    $net = 0.0;
    $vat = 0.0;
    $gross = 0.0;
    foreach ($allItems as $item) {
        $lineGross = round((float) $item['unit_price'] * (int) $item['quantity'], 2);
        $lineRate = FiscalService::normalizeVatRate($item['vat_rate'] ?? FiscalService::vatRate());
        $split = FiscalService::splitVat($lineGross, $lineRate);
        $previewLines[] = [
            'name' => (string) $item['item_name'],
            'qty' => (int) $item['quantity'],
            'unit_price' => (float) $item['unit_price'],
            'vat_rate' => $lineRate,
            'gross' => $lineGross,
            'net' => $split['net'],
            'vat' => $split['vat'],
        ];
        $net += $split['net'];
        $vat += $split['vat'];
        $gross += $lineGross;
    }
    // Sipariş toplamı (indirim varsa) ile hizala
    $orderGross = round((float) $order['total'], 2);
    if ($gross > 0 && abs($orderGross - $gross) > 0.009) {
        $scale = $orderGross / $gross;
        foreach ($previewLines as &$pl) {
            $pl['gross'] = round($pl['gross'] * $scale, 2);
            $pl['unit_price'] = $pl['qty'] > 0 ? round($pl['gross'] / $pl['qty'], 2) : $pl['unit_price'];
            $scaled = FiscalService::splitVat($pl['gross'], $pl['vat_rate']);
            $pl['net'] = $scaled['net'];
            $pl['vat'] = $scaled['vat'];
        }
        unset($pl);
        $net = array_sum(array_column($previewLines, 'net'));
        $vat = array_sum(array_column($previewLines, 'vat'));
        $gross = $orderGross;
    }
    $kasaInvoice = [
        'company_title' => $company['title'],
        'company_vkn' => $company['vkn'],
        'company_tax_office' => $company['tax_office'],
        'company_address' => $company['address'],
        'company_city' => $company['city'],
        'company_phone' => $company['phone'],
        'invoice_no' => 'ÖNİZLEME-' . (string) $order['order_code'],
        'invoice_date' => date('Y-m-d'),
        'order_code' => (string) $order['order_code'],
        'table_label' => (string) ($order['table_label'] ?? ''),
        'payment_method' => $order['payment_method'] ?? null,
        'buyer_name' => (string) ($order['customer_name'] ?? 'Nihai Tüketici'),
        'buyer_tax_id' => null,
        'buyer_tax_office' => null,
        'buyer_address' => null,
        'net_total' => round($net, 2),
        'vat_total' => round($vat, 2),
        'gross_total' => round($gross, 2),
        'vat_rate' => FiscalService::vatRate(),
    ];
  ?>
  <section class="slip tr-fis-slip-card">
    <p class="tr-fis-preview-label no-print">Kasa / müşteri satış fişi (Türkiye formatı)</p>
    <?php partial('partials/tr_receipt', [
        'invoice' => $kasaInvoice,
        'lines' => $previewLines,
        'printedAt' => $printedAt,
    ]); ?>
    <p class="small muted no-print" style="margin-top:10px">
      Resmi kayıtlı satış fişi için ödemeden sonra <strong>Satış fişi kes</strong> kullanın.
      Durum: <?= e(status_label((string) $order['status'])) ?>
      · Garson: <?= e((string) ($order['waiter_name'] ?? '—')) ?>
    </p>
  </section>
</div>

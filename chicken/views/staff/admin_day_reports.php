<?php
/** @var list<array> $reports */
/** @var array|null $summary */
/** @var array $company */
/** @var string $date */
$reports = $reports ?? [];
$summary = $summary ?? null;
$company = $company ?? [];
$date = $date ?? '';
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Satış</p>
    <h1>Günü raporlar</h1>
  </div>
  <div class="cta-row">
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/kasa/gun-sonu')) ?>">Manuel gün sonu</a>
    <?php if ($summary): ?>
      <button class="btn btn-primary btn-sm no-print" type="button" onclick="window.print()">Yazdır</button>
    <?php endif; ?>
  </div>
</div>

<p class="muted" style="margin-top:0;max-width:640px">
  Kasa her gece 00:00’dan sonra bir önceki günün gün sonunu otomatik alır.
  Raporlar burada listelenir; ayrıntı için güne tıklayın.
</p>

<section class="panel no-print" style="margin-bottom:16px">
  <h2 class="live-title" style="margin-top:0">Kapanış listesi</h2>
  <?php if (!$reports): ?>
    <p class="muted">Henüz gün sonu raporu yok. İlk otomatik kapanış gece yarısından sonra oluşur.</p>
  <?php else: ?>
    <div class="table-wrap" style="margin-top:12px">
      <table>
        <thead>
          <tr>
            <th>Tarih</th>
            <th>Tür</th>
            <th>Sipariş</th>
            <th>Nakit</th>
            <th>Kart</th>
            <th>KDV</th>
            <th>Toplam</th>
            <th>Kapatan</th>
            <th>Kapanış</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reports as $row): ?>
            <?php
              $rowDate = (string) ($row['business_date'] ?? '');
              $isAuto = !empty($row['is_auto']);
            ?>
            <tr<?= $date !== '' && $date === $rowDate ? ' style="background:rgba(196,122,44,.08)"' : '' ?>>
              <td>
                <a href="<?= e(url('/yonetici/gun-raporlari?date=' . rawurlencode($rowDate))) ?>">
                  <?= e($rowDate) ?>
                </a>
              </td>
              <td>
                <?php if ($isAuto): ?>
                  <span class="chip kitchen">Otomatik</span>
                <?php else: ?>
                  <span class="chip">Manuel</span>
                <?php endif; ?>
              </td>
              <td><?= (int) ($row['paid_order_count'] ?? 0) ?></td>
              <td><?= e(money((float) ($row['cash_total'] ?? 0))) ?></td>
              <td><?= e(money((float) ($row['card_total'] ?? 0))) ?></td>
              <td><?= e(money((float) ($row['vat_total'] ?? 0))) ?></td>
              <td><strong><?= e(money((float) ($row['gross_total'] ?? 0))) ?></strong></td>
              <td><?= e((string) ($row['closed_by_name'] ?? ($isAuto ? 'Sistem' : '—'))) ?></td>
              <td class="small muted"><?= e((string) ($row['created_at'] ?? '')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php if (is_array($summary)): ?>
  <section class="panel day-close-report">
    <div class="meta-row" style="margin-bottom:12px">
      <h2 class="live-title" style="margin:0">Gün raporu · <?= e($date) ?></h2>
      <?php if (!empty($summary['is_closed'])): ?>
        <?php if (!empty($summary['close']['is_auto'])): ?>
          <span class="chip kitchen">Otomatik kapanış</span>
        <?php else: ?>
          <span class="chip kitchen">Manuel kapanış</span>
        <?php endif; ?>
      <?php else: ?>
        <span class="chip">Henüz kapanmamış</span>
      <?php endif; ?>
    </div>

    <div class="stats">
      <div class="stat"><span class="muted">Ödenen sipariş</span><strong><?= (int) ($summary['paid_orders'] ?? 0) ?></strong></div>
      <div class="stat"><span class="muted">Kesilen fatura</span><strong><?= (int) ($summary['invoice_count'] ?? 0) ?></strong></div>
      <div class="stat"><span class="muted">Nakit</span><strong><?= e(money((float) ($summary['cash_total'] ?? 0))) ?></strong></div>
      <div class="stat"><span class="muted">Kart</span><strong><?= e(money((float) ($summary['card_total'] ?? 0))) ?></strong></div>
      <div class="stat"><span class="muted">Matrah</span><strong><?= e(money((float) ($summary['net_total'] ?? 0))) ?></strong></div>
      <div class="stat"><span class="muted">KDV</span><strong><?= e(money((float) ($summary['vat_total'] ?? 0))) ?></strong></div>
      <div class="stat"><span class="muted">Genel toplam</span><strong><?= e(money((float) ($summary['gross_total'] ?? 0))) ?></strong></div>
    </div>

    <p class="small muted">
      Firma: <?= e((string) ($company['title'] ?? '')) ?>
      <?php if (!empty($company['vkn'])): ?> · VKN <?= e((string) $company['vkn']) ?><?php endif; ?>
      · KDV %<?= e((string) ($summary['vat_rate'] ?? $company['vat_rate'] ?? '10')) ?>
    </p>

    <?php if (!empty($summary['close'])): ?>
      <div class="alert alert-ok">
        <?php if (!empty($summary['close']['is_auto'])): ?>
          Otomatik gece 00:00 gün sonu
        <?php else: ?>
          Manuel gün sonu
          <?php if (!empty($summary['close']['closed_by_name'])): ?>
            · <?= e((string) $summary['close']['closed_by_name']) ?>
          <?php endif; ?>
        <?php endif; ?>
        <?php if (!empty($summary['close']['created_at'])): ?>
          · <?= e((string) $summary['close']['created_at']) ?>
        <?php endif; ?>
        <?php if (!empty($summary['close']['note'])): ?>
          · <?= e((string) $summary['close']['note']) ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($summary['orders'])): ?>
      <div class="table-wrap" style="margin-top:18px">
        <table>
          <thead>
            <tr>
              <th>Kod</th>
              <th>Masa</th>
              <th>Ödeme</th>
              <th>Tutar</th>
              <th class="no-print"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($summary['orders'] as $order): ?>
              <tr>
                <td><?= e((string) $order['order_code']) ?></td>
                <td><?= e((string) ($order['table_label'] ?? '—')) ?></td>
                <td><?= e(payment_method_label($order['payment_method'] ?? null)) ?></td>
                <td><?= e(money((float) $order['total'])) ?></td>
                <td class="no-print">
                  <a class="btn btn-ghost btn-sm" href="<?= e(url('/kasa/fatura/siparis/' . (int) $order['id'])) ?>">Fatura</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="muted">Bu günde ödenen sipariş yok.</p>
    <?php endif; ?>
  </section>
<?php endif; ?>

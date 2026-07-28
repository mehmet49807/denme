<?php
/** @var array $summary */
/** @var array $recent */
/** @var array $company */
/** @var string $date */
/** @var bool $canBrowseDates */
/** @var bool $canManageFiscalSettings */
$summary = $summary ?? [];
$recent = $recent ?? [];
$company = $company ?? [];
$date = $date ?? date('Y-m-d');
$canBrowseDates = (bool) ($canBrowseDates ?? false);
$canManageFiscalSettings = (bool) ($canManageFiscalSettings ?? false);
$closed = !empty($summary['is_closed']);
?>
<div class="panel-head">
  <div>
    <p class="eyebrow"><?= $canBrowseDates ? 'Yönetici' : 'Kasa' ?> · Mali işlemler</p>
    <h1>Gün sonu<?= $canBrowseDates ? '' : ' (bugün)' ?></h1>
  </div>
  <div class="cta-row">
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/kasa/faturalar')) ?>">Faturalar</a>
    <?php if ($canManageFiscalSettings): ?>
      <a class="btn btn-ghost btn-sm" href="<?= e(url('/kasa/fatura-ayarlar')) ?>">Firma / KDV</a>
    <?php endif; ?>
    <button class="btn btn-primary btn-sm no-print" type="button" onclick="window.print()">Yazdır</button>
  </div>
</div>

<?php if ($canBrowseDates): ?>
  <section class="panel no-print" style="margin-bottom:16px;max-width:520px">
    <form method="get" action="<?= e(url('/kasa/gun-sonu')) ?>" class="stack">
      <label>İşlem tarihi
        <input type="date" name="date" value="<?= e($date) ?>" required>
      </label>
      <button class="btn btn-dark btn-sm" type="submit">Özeti göster</button>
    </form>
  </section>
<?php else: ?>
  <p class="muted no-print" style="margin-top:0">Kasa yalnızca bugünün gün sonunu görür ve kapatabilir.</p>
<?php endif; ?>

<section class="panel day-close-report">
  <div class="meta-row" style="margin-bottom:12px">
    <h2 class="live-title" style="margin:0">Gün sonu özeti · <?= e($date) ?></h2>
    <?php if ($closed): ?>
      <span class="chip kitchen">Kapalı</span>
    <?php else: ?>
      <span class="chip">Açık</span>
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
    <div class="stat"><span class="muted">Açık masa / sipariş</span><strong><?= (int) ($summary['open_tables'] ?? 0) ?> / <?= (int) ($summary['open_orders'] ?? 0) ?></strong></div>
  </div>

  <p class="small muted">
    Firma: <?= e((string) ($company['title'] ?? '')) ?>
    <?php if (!empty($company['vkn'])): ?> · VKN <?= e((string) $company['vkn']) ?><?php endif; ?>
    · KDV %<?= e((string) ($summary['vat_rate'] ?? $company['vat_rate'] ?? '10')) ?>
  </p>

  <?php if (!$closed): ?>
    <?php if (($summary['open_tables'] ?? 0) > 0 || ($summary['open_orders'] ?? 0) > 0): ?>
      <div class="alert alert-error">Gün sonu için önce tüm açık masaları ve siparişleri kapatın.</div>
    <?php else: ?>
      <form method="post" action="<?= e(url('/kasa/gun-sonu')) ?>" class="stack no-print" style="max-width:480px;margin-top:16px">
        <?= csrf_field() ?>
        <input type="hidden" name="date" value="<?= e($date) ?>">
        <label>Kapanış notu (opsiyonel)
          <textarea name="note" rows="2" placeholder="Kasa sayımı, eksik/fazla vb."></textarea>
        </label>
        <button class="btn btn-primary" type="submit" onclick="return confirm('Gün sonu kapatılsın mı? Bu işlem geri alınamaz.')">
          Gün sonunu kapat
        </button>
      </form>
    <?php endif; ?>
  <?php else: ?>
    <div class="alert alert-ok">
      Gün sonu kapatıldı
      <?php if (!empty($summary['close']['closed_by_name'])): ?>
        · <?= e((string) $summary['close']['closed_by_name']) ?>
      <?php endif; ?>
      <?php if (!empty($summary['close']['created_at'])): ?>
        · <?= e((string) $summary['close']['created_at']) ?>
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
  <?php endif; ?>
</section>

<?php if ($canBrowseDates && $recent): ?>
  <section class="panel no-print" style="margin-top:16px">
    <h2 class="live-title">Tüm kapanışlar</h2>
    <div class="table-wrap" style="margin-top:12px">
      <table>
        <thead>
          <tr>
            <th>Tarih</th>
            <th>Tür</th>
            <th>Toplam</th>
            <th>Nakit / Kart</th>
            <th>Kapatan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $row): ?>
            <?php $isAuto = !empty($row['is_auto']); ?>
            <tr>
              <td><a href="<?= e(url('/kasa/gun-sonu?date=' . urlencode((string) $row['business_date']))) ?>"><?= e((string) $row['business_date']) ?></a></td>
              <td><?= $isAuto ? 'Otomatik' : 'Manuel' ?></td>
              <td><?= e(money((float) $row['gross_total'])) ?></td>
              <td><?= e(money((float) $row['cash_total'])) ?> / <?= e(money((float) $row['card_total'])) ?></td>
              <td><?= e((string) ($row['closed_by_name'] ?? ($isAuto ? 'Sistem' : '—'))) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; ?>

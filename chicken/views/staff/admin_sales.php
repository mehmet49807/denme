<?php
/** @var array $stats */
/** @var array $dayStats */
/** @var string $monthLabel */
/** @var list<array> $topToday */
/** @var list<array> $topMonth */
/** @var list<array> $stockAlerts */
$topToday = $topToday ?? [];
$topMonth = $topMonth ?? [];
$stockAlerts = $stockAlerts ?? [];
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Satış</p>
    <h1>Satış istatistikleri</h1>
  </div>
  <div class="muted"><?= e($monthLabel) ?></div>
</div>

<div class="stats">
  <div class="stat"><span class="muted">Aylık sipariş</span><strong><?= (int) ($stats['order_count'] ?? 0) ?></strong></div>
  <div class="stat"><span class="muted">Aylık tahsilat</span><strong><?= e(money((float) ($stats['paid_total'] ?? 0))) ?></strong></div>
  <div class="stat"><span class="muted">Online</span><strong><?= e(money((float) ($stats['online_total'] ?? 0))) ?></strong></div>
  <div class="stat"><span class="muted">Garson</span><strong><?= e(money((float) ($stats['waiter_total'] ?? 0))) ?></strong></div>
  <div class="stat"><span class="muted">Kasa</span><strong><?= e(money((float) ($stats['cashier_total'] ?? 0))) ?></strong></div>
  <div class="stat"><span class="muted">Nakit / Kart</span><strong><?= e(money((float) ($stats['cash_total'] ?? 0))) ?> / <?= e(money((float) ($stats['card_total'] ?? 0))) ?></strong></div>
</div>

<section class="panel">
  <h2 style="font-family:var(--font-display);margin:0 0 12px">Bugün</h2>
  <div class="stats" style="margin:0">
    <div class="stat"><span class="muted">Sipariş</span><strong><?= (int) ($dayStats['order_count'] ?? 0) ?></strong></div>
    <div class="stat"><span class="muted">Tahsilat</span><strong><?= e(money((float) ($dayStats['paid_total'] ?? 0))) ?></strong></div>
    <div class="stat"><span class="muted">Açık tutar</span><strong><?= e(money((float) ($dayStats['open_total'] ?? 0))) ?></strong></div>
  </div>
</section>

<section class="panel" style="margin-top:18px">
  <h2 style="font-family:var(--font-display);margin:0 0 12px">Bugün en çok satanlar</h2>
  <?php if (!$topToday): ?>
    <p class="muted" style="margin:0">Bugün ödenmiş satış yok.</p>
  <?php else: ?>
    <ol class="top-sellers">
      <?php foreach ($topToday as $row): ?>
        <li>
          <strong><?= e((string) $row['item_name']) ?></strong>
          <span class="muted"><?= (int) $row['qty_sold'] ?> adet · <?= e(money((float) $row['sales_total'])) ?></span>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>
</section>

<section class="panel" style="margin-top:18px">
  <h2 style="font-family:var(--font-display);margin:0 0 12px">Ayın en çok satanları</h2>
  <?php if (!$topMonth): ?>
    <p class="muted" style="margin:0">Bu ay ödenmiş satış yok.</p>
  <?php else: ?>
    <ol class="top-sellers">
      <?php foreach ($topMonth as $row): ?>
        <li>
          <strong><?= e((string) $row['item_name']) ?></strong>
          <span class="muted"><?= (int) $row['qty_sold'] ?> adet · <?= e(money((float) $row['sales_total'])) ?></span>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>
</section>

<?php if ($stockAlerts): ?>
<section class="panel" style="margin-top:18px">
  <h2 style="font-family:var(--font-display);margin:0 0 12px">Stok uyarıları</h2>
  <ul>
    <?php foreach ($stockAlerts as $row): ?>
      <li>
        <a href="<?= e(url('/yonetici/urunler/' . (int) $row['id'])) ?>"><?= e((string) $row['name']) ?></a>
        — <?= e((string) $row['stock_qty']) ?> / eşik <?= e((string) $row['stock_alert_qty']) ?>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>

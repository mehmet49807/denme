<?php
/** @var array $stats */
/** @var array $dayStats */
/** @var string $monthLabel */
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

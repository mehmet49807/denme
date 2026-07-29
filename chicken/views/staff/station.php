<?php
/** @var string $station */
/** @var array $rows */
$stationKey = $station === 'bar' ? 'bar' : 'kitchen';
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">İstasyon</p>
    <h1><?= $stationKey === 'bar' ? 'Bar' : 'Mutfak' ?> ekranı</h1>
  </div>
  <div class="cta-row">
    <span class="live-chip" data-live-updated>Canlı</span>
    <button class="btn btn-ghost btn-sm" type="button" data-live-refresh>Yenile</button>
  </div>
</div>

<div
  class="station-board"
  data-station-board
  data-station="<?= e($stationKey) ?>"
  data-live-version="<?= e(OrderService::snapshotVersion($rows)) ?>"
>
  <?php if (!$rows): ?>
    <div class="panel muted" data-station-empty>Bekleyen ürün yok.</div>
  <?php endif; ?>
  <?php foreach ($rows as $row): ?>
    <article class="ticket">
      <h3><?= (int) $row['quantity'] ?>× <?= e($row['item_name']) ?></h3>
      <p class="muted small">
        <?= e($row['order_code']) ?>
        · <?= e($row['table_label'] ?? 'Online') ?>
        · <?= e(source_label($row['source'])) ?>
      </p>
      <?php if (!empty($row['customer_note'])): ?>
        <p><strong>Sipariş notu:</strong> <?= e($row['customer_note']) ?></p>
      <?php endif; ?>
      <?php if (!empty($row['note'])): ?>
        <p><?= e($row['note']) ?></p>
      <?php endif; ?>
      <div class="cta-row" style="margin-top:12px">
        <?php if ($row['status'] === 'queued'): ?>
          <button class="btn btn-primary btn-sm" type="button" data-item-id="<?= (int) $row['id'] ?>" data-item-status="preparing">Hazırla</button>
        <?php endif; ?>
        <?php if (in_array($row['status'], ['queued', 'preparing'], true)): ?>
          <button class="btn btn-ghost btn-sm" type="button" data-item-id="<?= (int) $row['id'] ?>" data-item-status="ready">Hazır</button>
        <?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
</div>

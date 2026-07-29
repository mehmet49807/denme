<?php
/** @var string $station */
/** @var list<array> $orders */
$stationKey = $station === 'bar' ? 'bar' : 'kitchen';
$stationTitle = $stationKey === 'bar' ? 'Bar' : 'Mutfak';
$orders = is_array($orders ?? null) ? $orders : [];
$statusChip = static function (string $status): string {
    return match ($status) {
        'preparing' => 'Hazırlanıyor',
        'ready' => 'Hazır',
        default => 'Sırada',
    };
};
$fmtTime = static function (?string $dt): string {
    if (!$dt) {
        return '—';
    }
    $ts = strtotime($dt);
    return $ts ? date('H:i', $ts) : $dt;
};
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">İstasyon · Fiş takibi</p>
    <h1><?= e($stationTitle) ?> ekranı</h1>
  </div>
  <div class="cta-row">
    <span class="live-chip" data-live-updated>Canlı</span>
    <button class="btn btn-ghost btn-sm" type="button" data-live-refresh>Yenile</button>
    <a class="btn btn-ghost btn-sm" href="<?= e(url(($stationKey === 'bar' ? '/bar' : '/mutfak') . '/fisler')) ?>">Fiş geçmişi</a>
    <?php if (empty($kiosk)): ?>
      <a class="btn btn-dark btn-sm" href="<?= e(url(($stationKey === 'bar' ? '/bar' : '/mutfak') . '?kiosk=1')) ?>">Kiosk</a>
    <?php else: ?>
      <a class="btn btn-dark btn-sm" href="<?= e(url($stationKey === 'bar' ? '/bar' : '/mutfak')) ?>">Normal</a>
    <?php endif; ?>
  </div>
</div>

<p class="muted" style="margin-top:-4px">
  <?= (int) ($waitAlertMinutes ?? 15) ?> dk üzeri açık fişler uyarı rengi alır.
  Hazır fişi <strong>Fişi kapat</strong> ile panodan kaldırın.
</p>

<div
  class="station-orders<?= !empty($kiosk) ? ' is-kiosk' : '' ?>"
  data-station-board
  data-station-mode="orders"
  data-station="<?= e($stationKey) ?>"
  data-live-version="<?= e(OrderService::snapshotVersion($orders)) ?>"
  data-wait-alert="<?= (int) ($waitAlertMinutes ?? 15) ?>"
  data-qz-enabled="<?= !empty($qz['enabled']) ? '1' : '0' ?>"
  data-qz-printer="<?= e((string) (($stationKey === 'bar' ? ($qz['printer_bar'] ?? '') : ($qz['printer_kitchen'] ?? '')))) ?>"
  <?= !empty($kiosk) ? 'data-kiosk="1"' : '' ?>
>
  <?php if (!$orders): ?>
    <div class="panel muted" data-station-empty>Bekleyen sipariş yok.</div>
  <?php endif; ?>
  <?php foreach ($orders as $order): ?>
    <?php
      $slip = (string) ($order['slip_status'] ?? 'waiting');
      $slipClass = match ($slip) {
          'acked' => 'is-acked',
          'sent' => 'is-sent',
          default => 'is-waiting',
      };
      if (!empty($order['is_late'])) {
          $slipClass .= ' is-late';
      }
    ?>
    <article class="station-order ticket <?= e($slipClass) ?>" data-order-id="<?= (int) $order['id'] ?>">
      <div class="station-order-head">
        <div>
          <h3><?= e((string) $order['order_code']) ?></h3>
          <p class="muted small" style="margin:4px 0 0">
            <?= e((string) ($order['table_label'] ?? 'Online / Paket')) ?>
            · <?= e(source_label((string) ($order['source'] ?? ''))) ?>
            <?php if (!empty($order['waiter_name'])): ?>
              · <?= e((string) $order['waiter_name']) ?>
            <?php endif; ?>
            · <?= e($fmtTime($order['created_at'] ?? null)) ?>
            · <strong><?= (int) ($order['wait_minutes'] ?? 0) ?> dk</strong>
          </p>
        </div>
        <div class="station-slip-meta">
          <span class="slip-chip slip-<?= e($slip) ?>"><?= e((string) ($order['slip_status_label'] ?? '')) ?></span>
          <div class="small muted">
            Gönderim: <strong><?= e($fmtTime($order['slip_sent_at'] ?? null)) ?></strong>
            · Alındı: <strong><?= e($fmtTime($order['slip_acked_at'] ?? null)) ?></strong>
          </div>
        </div>
      </div>

      <?php if (!empty($order['customer_note'])): ?>
        <p class="station-note"><strong>Sipariş notu:</strong> <?= e((string) $order['customer_note']) ?></p>
      <?php endif; ?>
      <?php if (!empty($order['customer_name']) && ($order['source'] ?? '') === 'online'): ?>
        <p class="small muted">Müşteri: <?= e((string) $order['customer_name']) ?></p>
      <?php endif; ?>

      <ul class="station-item-list">
        <?php foreach ($order['items'] ?? [] as $item): ?>
          <li class="station-item status-<?= e((string) $item['status']) ?>">
            <div class="station-item-main">
              <strong><?= (int) $item['quantity'] ?>× <?= e((string) $item['item_name']) ?></strong>
              <span class="chip"><?= e($statusChip((string) $item['status'])) ?></span>
            </div>
            <label class="station-item-note-edit">
              Not
              <div class="item-note-row">
                <input
                  type="text"
                  maxlength="255"
                  value="<?= e((string) ($item['note'] ?? '')) ?>"
                  placeholder="Ürün notu yazın..."
                  data-item-note-input="<?= (int) $item['id'] ?>"
                >
                <button class="btn btn-dark btn-sm" type="button" data-item-note-save="<?= (int) $item['id'] ?>">Kaydet</button>
              </div>
            </label>
            <div class="cta-row" style="margin-top:8px">
              <?php if (($item['status'] ?? '') === 'queued'): ?>
                <button class="btn btn-primary btn-sm" type="button" data-item-id="<?= (int) $item['id'] ?>" data-item-status="preparing">Hazırla</button>
              <?php endif; ?>
              <?php if (in_array($item['status'] ?? '', ['queued', 'preparing'], true)): ?>
                <button class="btn btn-ghost btn-sm" type="button" data-item-id="<?= (int) $item['id'] ?>" data-item-status="ready">Hazır</button>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="cta-row station-order-actions">
        <a class="btn btn-ghost btn-sm" href="<?= e((string) ($order['fis_url'] ?? url('/garson/fis/' . (int) $order['id'] . '?station=' . $stationKey))) ?>">Fişi gör</a>
        <?php if ($slip !== 'acked'): ?>
          <button
            class="btn btn-accent btn-sm"
            type="button"
            data-slip-ack
            data-order-id="<?= (int) $order['id'] ?>"
            data-station="<?= e($stationKey) ?>"
          >Fişi aldım</button>
        <?php endif; ?>
        <button
          class="btn btn-dark btn-sm"
          type="button"
          data-slip-close
          data-order-id="<?= (int) $order['id'] ?>"
          data-station="<?= e($stationKey) ?>"
        >Fişi kapat</button>
        <span class="muted small"><?= (int) ($order['open_count'] ?? 0) ?> açık · <?= (int) ($order['ready_count'] ?? 0) ?> hazır</span>
      </div>
    </article>
  <?php endforeach; ?>
</div>

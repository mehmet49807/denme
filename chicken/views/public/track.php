<?php $bodyAttrs = $order ? 'data-track-code="' . e($order['order_code']) . '"' : ''; ?>
<div class="page-shell">
  <p class="eyebrow">Sipariş takip</p>
  <h1 class="page-title">Siparişini izle</h1>
  <p class="muted">Sipariş kodunu girerek güncel durumu görün.</p>

  <form class="panel stack" method="get" action="<?= e(url('/takip')) ?>" style="margin:22px 0; max-width:520px">
    <label>Sipariş kodu
      <input name="code" value="<?= e($code) ?>" placeholder="CHK-......" required>
    </label>
    <button class="btn btn-primary" type="submit">Sorgula</button>
  </form>

  <?php if ($code !== '' && !$order): ?>
    <div class="alert alert-error">Bu kodla sipariş bulunamadı.</div>
  <?php endif; ?>

  <?php if ($order): ?>
    <div class="panel" style="max-width:720px">
      <div class="meta-row">
        <div>
          <div class="eyebrow">Sipariş ID</div>
          <h2 style="margin:0;font-family:var(--font-display)"><?= e($order['order_code']) ?></h2>
        </div>
        <span class="chip <?= e($order['source']) ?>"><?= e(source_label($order['source'])) ?></span>
      </div>
      <p style="margin:16px 0">Durum: <strong data-live-status><?= e(status_label($order['status'])) ?></strong></p>
      <p class="muted small">Toplam: <?= e(money((float) $order['total'])) ?> · <?= e($order['created_at']) ?></p>
      <ul>
        <?php foreach ($order['items'] as $item): ?>
          <li><?= (int) $item['quantity'] ?>× <?= e($item['item_name']) ?>
            <span class="chip <?= e($item['station']) ?>"><?= e(station_label($item['station'])) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
      <h3 style="margin-top:22px">Hareketler</h3>
      <ul>
        <?php foreach ($order['events'] as $event): ?>
          <li class="muted small"><?= e($event['created_at']) ?> — <?= e($event['message']) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
</div>

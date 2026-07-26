<div class="panel-head">
  <div>
    <p class="eyebrow">Kasa alanı</p>
    <h1>Günün siparişleri</h1>
  </div>
</div>

<div class="stats">
  <?php
    $open = count(array_filter($orders, fn($o) => !in_array($o['status'], ['paid', 'cancelled'], true)));
    $online = count(array_filter($orders, fn($o) => $o['source'] === 'online'));
    $waiter = count(array_filter($orders, fn($o) => $o['source'] === 'waiter'));
    $paidSum = array_sum(array_map(fn($o) => $o['status'] === 'paid' ? (float) $o['total'] : 0, $orders));
  ?>
  <div class="stat"><span class="muted">Açık sipariş</span><strong><?= $open ?></strong></div>
  <div class="stat"><span class="muted">Online</span><strong><?= $online ?></strong></div>
  <div class="stat"><span class="muted">Garson</span><strong><?= $waiter ?></strong></div>
  <div class="stat"><span class="muted">Tahsilat</span><strong><?= e(money($paidSum)) ?></strong></div>
</div>

<div class="order-card-list">
  <?php if (!$orders): ?>
    <div class="panel muted">Bugün henüz sipariş yok.</div>
  <?php endif; ?>
  <?php foreach ($orders as $order): ?>
    <article class="order-card">
      <div class="order-card-head">
        <div>
          <a class="order-code" href="<?= e(url('/garson/fis/' . (int) $order['id'])) ?>"><?= e($order['order_code']) ?></a>
          <div class="small muted"><?= e($order['created_at'] ?? '') ?></div>
        </div>
        <span class="chip <?= e($order['source']) ?>"><?= e(source_label($order['source'])) ?></span>
      </div>
      <div class="order-card-meta">
        <div>
          <span class="muted small">Masa / Müşteri</span>
          <strong><?= e($order['table_label'] ?? ($order['customer_name'] ?: '—')) ?></strong>
          <?php if (!empty($order['customer_phone'])): ?>
            <div class="small muted"><?= e($order['customer_phone']) ?></div>
          <?php endif; ?>
        </div>
        <div>
          <span class="muted small">Garson</span>
          <strong><?= e($order['waiter_name'] ?? '—') ?></strong>
        </div>
        <div>
          <span class="muted small">Durum</span>
          <strong><?= e(status_label($order['status'])) ?></strong>
        </div>
        <div>
          <span class="muted small">Tutar</span>
          <strong class="price"><?= e(money((float) $order['total'])) ?></strong>
        </div>
      </div>
      <?php partial('partials/order_note', ['order' => $order]); ?>
      <div class="cta-row order-card-actions">
        <?php if ($order['status'] === 'pending'): ?>
          <button class="btn btn-primary btn-sm" type="button" data-order-id="<?= (int) $order['id'] ?>" data-status-btn="accepted">Al</button>
        <?php endif; ?>
        <?php if (in_array($order['status'], ['accepted', 'preparing'], true)): ?>
          <button class="btn btn-ghost btn-sm" type="button" data-order-id="<?= (int) $order['id'] ?>" data-status-btn="ready">Hazır</button>
        <?php endif; ?>
        <?php if (!in_array($order['status'], ['paid', 'cancelled'], true)): ?>
          <button class="btn btn-dark btn-sm" type="button" data-order-id="<?= (int) $order['id'] ?>" data-status-btn="paid">Ödendi</button>
        <?php endif; ?>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/garson/fis/' . (int) $order['id'])) ?>">Fiş</a>
      </div>
    </article>
  <?php endforeach; ?>
</div>

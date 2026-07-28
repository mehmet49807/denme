<?php /** @var array $orders */ ?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Siparişler</p>
    <h1>Tüm siparişler</h1>
  </div>
</div>

<div class="order-card-list">
  <?php if (!$orders): ?>
    <div class="panel muted">Henüz sipariş yok.</div>
  <?php endif; ?>
  <?php foreach ($orders as $order): ?>
    <article class="order-card">
      <div class="order-card-head">
        <div>
          <a class="order-code" href="<?= e(url('/garson/fis/' . (int) $order['id'])) ?>"><?= e($order['order_code']) ?></a>
          <div class="small muted">
            <?= e($order['created_at'] ?? '') ?>
            · <?= e($order['table_label'] ?? ($order['customer_name'] ?: '—')) ?>
            · <?= e($order['waiter_name'] ?? '—') ?>
          </div>
        </div>
        <div class="cta-row">
          <span class="chip <?= e($order['source']) ?>"><?= e(source_label($order['source'])) ?></span>
          <strong class="price"><?= e(money((float) $order['total'])) ?></strong>
        </div>
      </div>
      <div class="small muted" style="margin-bottom:8px">
        Durum: <?= e(status_label($order['status'])) ?>
        <?php if (!empty($order['payment_method'])): ?>
          · Ödeme: <?= e(payment_method_label($order['payment_method'])) ?>
        <?php endif; ?>
      </div>
      <?php partial('partials/order_note', ['order' => $order]); ?>
      <div class="cta-row">
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/garson/fis/' . (int) $order['id'])) ?>">Fiş</a>
        <?php if (($order['status'] ?? '') === 'paid'): ?>
          <a class="btn btn-primary btn-sm" href="<?= e(url('/kasa/fatura/siparis/' . (int) $order['id'])) ?>">Fatura</a>
        <?php endif; ?>
        <?php if (!empty($order['table_id']) && !in_array($order['status'], ['paid', 'cancelled'], true)): ?>
          <a class="btn btn-dark btn-sm" href="<?= e(url('/kasa/masa/' . (int) $order['table_id'])) ?>">Kasada aç</a>
        <?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
</div>

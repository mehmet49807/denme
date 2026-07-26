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

<div class="slips">
  <section class="slip">
    <h2>Mutfak Fişi</h2>
    <p class="muted small">
      <?= e(source_label($order['source'])) ?>
      · <?= e($order['table_label'] ?? 'Paket/Online') ?>
      · <?= e($order['order_code']) ?>
    </p>
    <?php if ($order['kitchen_items']): ?>
      <ul>
        <?php foreach ($order['kitchen_items'] as $item): ?>
          <li>
            <strong><?= (int) $item['quantity'] ?>× <?= e($item['item_name']) ?></strong>
            <?php if (!empty($item['note'])): ?><div class="small muted"><?= e($item['note']) ?></div><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="muted">Mutfak ürünü yok.</p>
    <?php endif; ?>
  </section>

  <section class="slip">
    <h2>Bar Fişi</h2>
    <p class="muted small">
      <?= e(source_label($order['source'])) ?>
      · <?= e($order['table_label'] ?? 'Paket/Online') ?>
      · <?= e($order['order_code']) ?>
    </p>
    <?php if ($order['bar_items']): ?>
      <ul>
        <?php foreach ($order['bar_items'] as $item): ?>
          <li>
            <strong><?= (int) $item['quantity'] ?>× <?= e($item['item_name']) ?></strong>
            <?php if (!empty($item['note'])): ?><div class="small muted"><?= e($item['note']) ?></div><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="muted">Bar ürünü yok.</p>
    <?php endif; ?>
  </section>

  <section class="slip">
    <h2>Kasa / Takip Fişi</h2>
    <p><strong>ID:</strong> <?= e($order['order_code']) ?></p>
    <p><strong>Kaynak:</strong> <?= e(source_label($order['source'])) ?></p>
    <p><strong>Garson:</strong> <?= e($order['waiter_name'] ?? '-') ?></p>
    <p><strong>Müşteri:</strong> <?= e($order['customer_name'] ?? '-') ?> <?= e($order['customer_phone'] ?? '') ?></p>
    <p><strong>Toplam:</strong> <?= e(money((float) $order['total'])) ?></p>
    <p><strong>Durum:</strong> <?= e(status_label($order['status'])) ?></p>
  </section>
</div>

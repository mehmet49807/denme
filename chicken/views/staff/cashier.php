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

<div class="table-wrap panel" style="padding:0">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Kaynak</th>
        <th>Masa / Müşteri</th>
        <th>Garson</th>
        <th>Durum</th>
        <th>Tutar</th>
        <th>İşlem</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $order): ?>
        <tr>
          <td>
            <a href="<?= e(url('/garson/fis/' . (int) $order['id'])) ?>"><?= e($order['order_code']) ?></a>
          </td>
          <td><span class="chip <?= e($order['source']) ?>"><?= e(source_label($order['source'])) ?></span></td>
          <td>
            <?= e($order['table_label'] ?? ($order['customer_name'] ?: '—')) ?>
            <?php if (!empty($order['customer_phone'])): ?>
              <div class="small muted"><?= e($order['customer_phone']) ?></div>
            <?php endif; ?>
          </td>
          <td><?= e($order['waiter_name'] ?? '—') ?></td>
          <td><?= e(status_label($order['status'])) ?></td>
          <td><?= e(money((float) $order['total'])) ?></td>
          <td>
            <div class="cta-row">
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
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php
/** @var array $table */
/** @var array $orders */
/** @var array $items */
/** @var string $mode */
/** @var bool $canPay */
/** @var bool $canCancel */
/** @var bool $canClose */
/** @var callable $canAddToOrder */
/** @var callable $canEditItemNote */
$back = $mode === 'cashier' ? url('/kasa') : url('/siparisler');
$openTotal = array_sum(array_map(static fn(array $o): float => (float) $o['total'], $orders));
?>
<div class="panel-head">
  <div>
    <p class="eyebrow"><?= $mode === 'cashier' ? 'Kasa · Masa' : 'Garson · Masa' ?></p>
    <h1><?= e($table['label']) ?> <span class="muted" style="font-size:.55em">(<?= e($table['code']) ?>)</span></h1>
  </div>
  <div class="cta-row">
    <a class="btn btn-ghost btn-sm" href="<?= e($back) ?>">Geri</a>
    <?php if ($canClose && $orders): ?>
      <button class="btn btn-primary btn-sm" type="button" data-close-table="<?= (int) $table['id'] ?>" data-method="cash">Nakit kapat</button>
      <button class="btn btn-dark btn-sm" type="button" data-close-table="<?= (int) $table['id'] ?>" data-method="card">Kart kapat</button>
    <?php endif; ?>
  </div>
</div>

<div class="stats">
  <div class="stat"><span class="muted">Açık sipariş</span><strong><?= count($orders) ?></strong></div>
  <div class="stat"><span class="muted">Masa toplam</span><strong><?= e(money($openTotal)) ?></strong></div>
  <div class="stat"><span class="muted">Durum</span><strong><?= $orders ? 'Açık' : 'Boş' ?></strong></div>
</div>

<?php if (!$orders): ?>
  <div class="panel muted" style="margin-bottom:20px">Bu masada açık sipariş yok. Aşağıdan yeni sipariş açabilirsiniz.</div>
<?php endif; ?>

<?php foreach ($orders as $order): ?>
  <?php
    $allowAdd = (bool) $canAddToOrder($order);
    $allowNote = (bool) $canEditItemNote($order);
  ?>
  <article class="panel order-manage" data-order-block="<?= (int) $order['id'] ?>">
    <div class="order-card-head">
      <div>
        <a class="order-code" href="<?= e(url('/garson/fis/' . (int) $order['id'])) ?>"><?= e($order['order_code']) ?></a>
        <div class="small muted">
          <?= e(source_label($order['source'])) ?>
          · <?= e($order['waiter_name'] ?? '—') ?>
          · <?= e(status_label($order['status'])) ?>
        </div>
      </div>
      <strong class="price"><?= e(money((float) $order['total'])) ?></strong>
    </div>

    <div class="item-manage-list">
      <?php foreach ($order['items'] as $line): ?>
        <?php $cancelled = $line['status'] === 'cancelled'; ?>
        <div class="item-manage <?= $cancelled ? 'is-cancelled' : '' ?>" data-item-row="<?= (int) $line['id'] ?>">
          <div class="item-manage-main">
            <div>
              <strong><?= e($line['item_name']) ?></strong>
              <div class="muted small">
                <?= e(station_label($line['station'])) ?>
                · x<?= (int) $line['quantity'] ?>
                · <?= e(money((float) $line['unit_price'] * (int) $line['quantity'])) ?>
                <?php if ($cancelled): ?> · İptal<?php endif; ?>
              </div>
            </div>
            <?php if ($canCancel && !$cancelled): ?>
              <button class="btn btn-ghost btn-sm" type="button" data-cancel-item="<?= (int) $line['id'] ?>">İptal</button>
            <?php endif; ?>
          </div>
          <?php if (!$cancelled && $allowNote): ?>
            <label class="item-note-label">
              <?= e(station_label($line['station'])) ?> notu
              <div class="item-note-row">
                <input
                  type="text"
                  maxlength="255"
                  value="<?= e((string) ($line['note'] ?? '')) ?>"
                  placeholder="<?= $line['station'] === 'bar' ? 'Bar için not...' : 'Mutfak için not...' ?>"
                  data-item-note-input="<?= (int) $line['id'] ?>"
                >
                <button class="btn btn-dark btn-sm" type="button" data-item-note-save="<?= (int) $line['id'] ?>">Kaydet</button>
              </div>
            </label>
          <?php elseif (!$cancelled && !empty($line['note'])): ?>
            <div class="muted small">Not: <?= e($line['note']) ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <?php partial('partials/order_note', ['order' => $order]); ?>

    <div class="cta-row" style="margin-top:12px">
      <?php if ($allowAdd): ?>
        <button class="btn btn-primary btn-sm" type="button" data-focus-add="<?= (int) $order['id'] ?>">Bu siparişe ürün ekle</button>
      <?php elseif ($mode === 'waiter'): ?>
        <span class="muted small">Bu sipariş başka garsona ait — sadece görüntüleme</span>
      <?php endif; ?>
      <?php if ($canPay): ?>
        <button class="btn btn-dark btn-sm" type="button" data-pay-order="<?= (int) $order['id'] ?>" data-method="cash">Nakit al</button>
        <button class="btn btn-dark btn-sm" type="button" data-pay-order="<?= (int) $order['id'] ?>" data-method="card">Kart al</button>
      <?php endif; ?>
      <?php if ($canCancel): ?>
        <button class="btn btn-ghost btn-sm" type="button" data-cancel-order="<?= (int) $order['id'] ?>">Siparişi iptal</button>
      <?php endif; ?>
      <a class="btn btn-ghost btn-sm" href="<?= e(url('/garson/fis/' . (int) $order['id'])) ?>">Fiş</a>
    </div>
  </article>
<?php endforeach; ?>

<div class="panel" style="margin-top:8px" id="order-builder" data-table-order-builder>
  <div class="panel-head" style="margin-bottom:12px">
    <div>
      <p class="eyebrow">Ürün ekle</p>
      <h2 style="margin:0;font-family:var(--font-display);font-size:1.35rem">
        <?= $orders ? 'Siparişe / yeni fişe ekle' : 'Yeni masa siparişi' ?>
      </h2>
    </div>
  </div>

  <form class="stack" data-table-order-form>
    <input type="hidden" name="table_id" value="<?= (int) $table['id'] ?>">
    <label>Hedef sipariş
      <select name="order_id" data-target-order>
        <option value="0">Yeni sipariş fişi aç</option>
        <?php foreach ($orders as $order): ?>
          <?php if ($canAddToOrder($order)): ?>
            <option value="<?= (int) $order['id'] ?>"><?= e($order['order_code']) ?> · <?= e(money((float) $order['total'])) ?></option>
          <?php endif; ?>
        <?php endforeach; ?>
      </select>
    </label>

    <div class="menu-grid menu-grid-compact">
      <?php foreach ($items as $item): ?>
        <?php partial('partials/menu_item_card', ['item' => $item, 'showAdd' => true]); ?>
      <?php endforeach; ?>
    </div>

    <div class="panel cart" data-waiter-cart style="position:static;margin-top:16px">
      <div data-cart-list></div>
      <div class="meta-row">
        <span class="muted">Toplam / adet</span>
        <strong><span data-cart-total>0,00 ₺</span> · <span data-cart-count>0</span></strong>
      </div>
      <label>Sipariş notu (yeni fiş)
        <textarea name="customer_note" placeholder="Sipariş altına not..."></textarea>
      </label>
      <button class="btn btn-primary" type="submit">Mutfak + Bar’a gönder</button>
    </div>
  </form>
</div>

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
$canClose = (bool) ($canClose ?? false);
$role = Auth::role();
$back = $mode === 'cashier'
    ? url('/kasa')
    : ($role === 'admin' ? url('/yonetici/masalar') : url('/siparisler'));
$closeRedirect = $mode === 'cashier'
    ? url('/kasa')
    : ($role === 'admin' ? url('/yonetici/masalar') : url('/siparisler'));
$openTotal = array_sum(array_map(static fn(array $o): float => (float) $o['total'], $orders));
$eyebrow = match (true) {
    $mode === 'cashier' => 'Kasa · Masa',
    $role === 'admin' => 'Yönetici · Masa',
    default => 'Garson · Masa',
};
?>
<div class="panel-head">
  <div>
    <p class="eyebrow"><?= e($eyebrow) ?></p>
    <h1><?= e($table['label']) ?> <span class="muted" style="font-size:.55em">(<?= e($table['code']) ?>)</span></h1>
    <?php if (!empty($table['opened_by_name'])): ?>
      <p class="muted" style="margin:6px 0 0">Açan: <?= e((string) $table['opened_by_name']) ?> · <?= (int) ($table['seats'] ?? 0) ?> kişi</p>
    <?php endif; ?>
  </div>
  <div class="cta-row">
    <a class="btn btn-ghost btn-sm" href="<?= e($back) ?>">Geri</a>
  </div>
</div>

<div class="stats">
  <div class="stat"><span class="muted">Açık sipariş</span><strong><?= count($orders) ?></strong></div>
  <div class="stat"><span class="muted">Masa toplam</span><strong><?= e(money($openTotal)) ?></strong></div>
  <div class="stat"><span class="muted">Durum</span><strong><?= $orders ? 'Açık' : 'Boş' ?></strong></div>
</div>

<?php if ($canClose && $orders): ?>
  <section class="panel table-close-panel" style="margin-bottom:18px">
    <div class="meta-row" style="margin-bottom:10px">
      <strong>Masa kapat</strong>
      <span class="small muted">Yalnızca kasa / yönetici · açık siparişler ödenir</span>
    </div>
    <?php partial('partials/table_close_buttons', [
        'tableId' => (int) $table['id'],
        'redirect' => $closeRedirect,
        'wrap' => true,
        'label' => 'Ödeme ile kapat',
    ]); ?>
  </section>
<?php endif; ?>

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
            <?php if (!$cancelled && (int) $line['quantity'] > 1 && $allowNote): ?>
              <button
                class="btn btn-ghost btn-sm"
                type="button"
                data-split-item="<?= (int) $line['id'] ?>"
                data-split-max="<?= (int) $line['quantity'] - 1 ?>"
              >Böl</button>
            <?php endif; ?>
          </div>
          <?php if (!$cancelled && $allowNote): ?>
            <label class="item-note-label">
              <?= e(station_label($line['station'])) ?> · Not
              <div class="item-note-row">
                <input
                  type="text"
                  maxlength="255"
                  value="<?= e((string) ($line['note'] ?? '')) ?>"
                  placeholder="Bu ürün için not yazın..."
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

    <?php if ($allowAdd || $canPay || $role === 'admin' || $role === 'cashier'): ?>
      <div class="stack" style="margin-top:14px;padding-top:12px;border-top:1px dashed var(--line)">
        <label>Masaya taşı
          <div class="item-note-row">
            <select data-move-table-select>
              <option value="">Hedef masa</option>
              <?php foreach (OrderService::tablesOverview() as $t): ?>
                <?php if ((int) $t['id'] === (int) $table['id']) {
                    continue;
                } ?>
                <option value="<?= (int) $t['id'] ?>"><?= e((string) $t['label']) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-ghost btn-sm" type="button" data-move-order="<?= (int) $order['id'] ?>">Taşı</button>
          </div>
        </label>
      </div>
    <?php endif; ?>
  </article>
<?php endforeach; ?>

<?php if (($canPay || $role === 'admin' || $role === 'cashier' || $mode === 'waiter') && $orders): ?>
<section class="panel" style="margin:18px 0">
  <h2 style="margin:0 0 10px;font-family:var(--font-display)">Masa birleştir</h2>
  <p class="muted small">Bu masadaki açık siparişleri başka masaya taşıyın.</p>
  <div class="item-note-row">
    <select data-merge-to-table>
      <option value="">Hedef masa</option>
      <?php foreach (OrderService::tablesOverview() as $t): ?>
        <?php if ((int) $t['id'] === (int) $table['id']) {
            continue;
        } ?>
        <option value="<?= (int) $t['id'] ?>"><?= e((string) $t['label']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-dark btn-sm" type="button" data-merge-tables data-from-table="<?= (int) $table['id'] ?>">Birleştir</button>
  </div>
</section>
<?php endif; ?>

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

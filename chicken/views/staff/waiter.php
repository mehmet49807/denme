<div class="panel-head">
  <div>
    <p class="eyebrow">Garson paneli</p>
    <h1>Masa siparişi</h1>
  </div>
  <button class="btn btn-ghost btn-sm" type="button" data-nav-toggle>Kategoriler</button>
</div>

<div class="order-builder">
  <div class="builder-menu">
    <div class="menu-grid menu-grid-compact">
      <?php foreach ($items as $item): ?>
        <article class="menu-item" data-cat="<?= e($item['category_slug']) ?>">
          <div class="meta-row">
            <span class="chip <?= e($item['station']) ?>"><?= e(station_label($item['station'])) ?></span>
            <span class="price"><?= e(money((float) $item['price'])) ?></span>
          </div>
          <h3><?= e($item['name']) ?></h3>
          <button
            class="btn btn-dark btn-sm"
            type="button"
            data-add-item="<?= (int) $item['id'] ?>"
            data-name="<?= e($item['name']) ?>"
            data-price="<?= e((string) $item['price']) ?>"
            data-station="<?= e($item['station']) ?>"
          >Ekle</button>
        </article>
      <?php endforeach; ?>
    </div>
  </div>

  <aside class="panel cart cart-panel" data-waiter-cart>
    <h2 style="margin:0 0 12px;font-family:var(--font-display)">Sipariş fişi</h2>
    <form class="stack" data-waiter-form>
      <label>Masa
        <select name="table_id" required>
          <option value="">Masa seçin</option>
          <?php foreach ($tables as $table): ?>
            <option value="<?= (int) $table['id'] ?>"><?= e($table['label']) ?> (<?= e($table['code']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
      <div data-cart-list></div>
      <div class="meta-row">
        <span class="muted">Toplam / adet</span>
        <strong><span data-cart-total>0,00 ₺</span> · <span data-cart-count>0</span></strong>
      </div>
      <label>Sipariş notu
        <textarea name="customer_note" placeholder="Sipariş altına not yazın..."></textarea>
      </label>
      <button class="btn btn-primary" type="submit">Mutfak + Bar fişi gönder</button>
    </form>
  </aside>
</div>

<div class="panel" style="margin-top:20px">
  <h2 style="font-family:var(--font-display);margin:0 0 12px">Bugünkü siparişlerim</h2>
  <div class="order-card-list">
    <?php if (!$orders): ?>
      <p class="muted">Henüz sipariş yok.</p>
    <?php endif; ?>
    <?php foreach ($orders as $order): ?>
      <article class="order-card">
        <div class="order-card-head">
          <div>
            <div class="order-code"><?= e($order['order_code']) ?></div>
            <div class="small muted"><?= e($order['table_label'] ?? '-') ?> · <?= e(status_label($order['status'])) ?></div>
          </div>
          <strong class="price"><?= e(money((float) $order['total'])) ?></strong>
        </div>
        <?php partial('partials/order_note', ['order' => $order]); ?>
        <div class="cta-row">
          <a class="btn btn-ghost btn-sm" href="<?= e(url('/garson/fis/' . (int) $order['id'])) ?>">Fiş aç</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</div>

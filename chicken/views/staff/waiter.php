<div class="panel-head">
  <div>
    <p class="eyebrow">Garson paneli</p>
    <h1>Masa siparişi</h1>
  </div>
  <a class="btn btn-ghost btn-sm" href="<?= e(url('/garson/fis/0')) ?>" style="display:none"></a>
</div>

<div class="order-builder">
  <div>
    <div class="tabs">
      <button class="tab active" type="button" data-cat-tab="all">Tümü</button>
      <?php foreach ($categories as $cat): ?>
        <button class="tab" type="button" data-cat-tab="<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></button>
      <?php endforeach; ?>
    </div>
    <div class="menu-grid">
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

  <aside class="panel cart" data-waiter-cart>
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
      <label>Not
        <textarea name="customer_note" placeholder="Müşteri isteği"></textarea>
      </label>
      <button class="btn btn-primary" type="submit">Mutfak + Bar fişi gönder</button>
    </form>
  </aside>
</div>

<div class="panel" style="margin-top:20px">
  <h2 style="font-family:var(--font-display);margin:0 0 12px">Bugünkü siparişlerim</h2>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Masa</th>
          <th>Durum</th>
          <th>Tutar</th>
          <th>Fiş</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
          <tr>
            <td><?= e($order['order_code']) ?></td>
            <td><?= e($order['table_label'] ?? '-') ?></td>
            <td><?= e(status_label($order['status'])) ?></td>
            <td><?= e(money((float) $order['total'])) ?></td>
            <td><a class="btn btn-ghost btn-sm" href="<?= e(url('/garson/fis/' . (int) $order['id'])) ?>">Aç</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

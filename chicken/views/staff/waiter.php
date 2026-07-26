<div class="panel-head">
  <div>
    <p class="eyebrow">Garson paneli</p>
    <h1>Masa siparişi</h1>
  </div>
  <div class="cta-row">
    <button class="btn btn-ghost btn-sm" type="button" data-nav-toggle>Kategoriler</button>
    <a class="btn btn-primary btn-sm" href="<?= e(url('/siparisler')) ?>">
      Siparişler <span class="chip" data-cart-badge hidden>0</span>
    </a>
  </div>
</div>

<div class="menu-grid menu-grid-compact" data-waiter-menu>
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

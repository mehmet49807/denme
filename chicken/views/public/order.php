<div class="page-shell">
  <p class="eyebrow">Online sipariş</p>
  <h1 class="page-title">Siparişini oluştur</h1>
  <p class="muted">Ürünleri seç, bilgilerini gir, sipariş kodunla takip et.</p>

  <div class="tabs" style="margin-top:22px">
    <button class="tab active" type="button" data-cat-tab="all">Tümü</button>
    <?php foreach ($categories as $cat): ?>
      <button class="tab" type="button" data-cat-tab="<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></button>
    <?php endforeach; ?>
  </div>

  <div class="order-builder">
    <div class="menu-grid">
      <?php foreach ($items as $item): ?>
        <article class="menu-item" data-cat="<?= e($item['category_slug']) ?>">
          <div class="meta-row">
            <span class="chip <?= e($item['station']) ?>"><?= e(station_label($item['station'])) ?></span>
            <span class="price"><?= e(money((float) $item['price'])) ?></span>
          </div>
          <h3><?= e($item['name']) ?></h3>
          <p><?= e($item['description'] ?? '') ?></p>
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

    <aside class="panel cart" data-online-cart>
      <div class="meta-row">
        <h2 style="margin:0;font-family:var(--font-display)">Sepet</h2>
        <span class="chip" data-cart-count>0</span>
      </div>
      <div data-cart-list style="margin:14px 0"></div>
      <div class="meta-row" style="margin-bottom:14px">
        <span class="muted">Toplam</span>
        <strong data-cart-total>0,00 ₺</strong>
      </div>
      <form class="stack" data-online-form>
        <label>Ad Soyad
          <input name="customer_name" required placeholder="Adınız">
        </label>
        <label>Telefon
          <input name="customer_phone" required placeholder="05xx...">
        </label>
        <label>Not
          <textarea name="customer_note" placeholder="Az acılı, ekstra sos..."></textarea>
        </label>
        <button class="btn btn-primary" type="submit">Siparişi Gönder</button>
      </form>
    </aside>
  </div>
</div>

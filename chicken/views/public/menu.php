<div class="page-shell">
  <p class="eyebrow">Dijital menü</p>
  <h1 class="page-title">Menü<?= !empty($table) ? ' · ' . e($table['label']) : '' ?></h1>
  <p class="muted">QR kod ile açılan menü. Beğendiğiniz ürünleri online siparişe taşıyabilirsiniz.</p>

  <div class="tabs" style="margin-top:22px">
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
        <p><?= e($item['description'] ?? '') ?></p>
      </article>
    <?php endforeach; ?>
  </div>

  <div class="cta-row" style="margin-top:28px">
    <a class="btn btn-primary" href="/siparis">Online Sipariş Ver</a>
    <a class="btn btn-ghost" href="/takip">Sipariş Takip</a>
  </div>
</div>

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
      <?php partial('partials/menu_item_card', ['item' => $item, 'showDescription' => true]); ?>
    <?php endforeach; ?>
  </div>

  <div class="cta-row" style="margin-top:28px">
    <a class="btn btn-primary" href="<?= e(url('/siparis')) ?>">Online Sipariş Ver</a>
    <a class="btn btn-ghost" href="<?= e(url('/takip')) ?>">Sipariş Takip</a>
  </div>
</div>

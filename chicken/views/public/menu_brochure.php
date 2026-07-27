<?php
/** @var array $categories */
/** @var array $items */
/** @var array|null $table */
/** @var string $themeId */
$table = $table ?? null;
$themeId = preg_replace('/[^a-z0-9_-]/i', '', (string) ($themeId ?? 'classic')) ?: 'classic';
$byCat = [];
foreach ($items as $item) {
    $slug = (string) ($item['category_slug'] ?? 'diger');
    $byCat[$slug][] = $item;
}
?>
<div class="brochure theme-<?= e($themeId) ?>" data-brochure data-theme="<?= e($themeId) ?>">
  <header class="brochure-hero">
    <img class="brochure-logo" src="<?= e(url('/assets/img/logo.svg')) ?>" alt="" width="64" height="64">
    <p class="eyebrow">Chicken Grill</p>
    <h1>Menü</h1>
    <?php if (!empty($table['label'])): ?>
      <p class="brochure-table"><?= e((string) $table['label']) ?></p>
    <?php endif; ?>
    <p class="brochure-lede">Izgara lezzetler · sıcak bar · hızlı servis</p>
  </header>

  <?php foreach ($categories as $cat): ?>
    <?php
      $slug = (string) $cat['slug'];
      $rows = $byCat[$slug] ?? [];
      if (!$rows) {
          continue;
      }
    ?>
    <section class="brochure-section">
      <h2><?= e((string) $cat['name']) ?></h2>
      <div class="brochure-list">
        <?php foreach ($rows as $item): ?>
          <?php
            $image = class_exists('MenuImageSync')
                ? MenuImageSync::resolve($item)
                : trim((string) ($item['image_url'] ?? ''));
            $imageSrc = $image !== '' ? asset_url($image) : '';
          ?>
          <article class="brochure-item">
            <?php if ($imageSrc !== ''): ?>
              <div class="brochure-thumb" style="background-image:url('<?= e($imageSrc) ?>')"></div>
            <?php endif; ?>
            <div class="brochure-item-body">
              <div class="meta-row">
                <h3><?= e((string) $item['name']) ?></h3>
                <span class="price"><?= e(money((float) $item['price'])) ?></span>
              </div>
              <?php if (!empty($item['description'])): ?>
                <p><?= e((string) $item['description']) ?></p>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>

  <footer class="brochure-foot">
    <p>Garsonunuza sipariş verebilir veya online sipariş açabilirsiniz.</p>
    <div class="cta-row">
      <a class="btn btn-accent" href="<?= e(url('/siparis')) ?>">Online Sipariş</a>
      <a class="btn btn-ghost" href="<?= e(url('/takip')) ?>">Sipariş Takip</a>
    </div>
  </footer>
</div>

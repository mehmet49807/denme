<?php
/** @var array $categories */
/** @var array $items */
/** @var array|null $table */
/** @var string $themeId */
$table = $table ?? null;
$themeId = preg_replace('/[^a-z0-9_-]/i', '', (string) ($themeId ?? 'classic')) ?: 'classic';
$layout = class_exists('BrochureService')
    ? BrochureService::themeLayout($themeId)
    : 'list';
$byCat = [];
foreach ($items as $item) {
    $slug = (string) ($item['category_slug'] ?? 'diger');
    $byCat[$slug][] = $item;
}

$resolveImage = static function (array $item): string {
    $image = class_exists('MenuImageSync')
        ? MenuImageSync::resolve($item)
        : trim((string) ($item['image_url'] ?? ''));
    return $image !== '' ? asset_url($image) : '';
};

$activeCats = [];
foreach ($categories as $cat) {
    $slug = (string) $cat['slug'];
    if (!empty($byCat[$slug])) {
        $activeCats[] = $cat;
    }
}

$featured = null;
$featuredCatName = '';
if ($layout === 'magazine') {
    foreach ($activeCats as $cat) {
        foreach ($byCat[(string) $cat['slug']] as $item) {
            if ($resolveImage($item) !== '') {
                $featured = $item;
                $featuredCatName = (string) $cat['name'];
                break 2;
            }
        }
    }
    if ($featured === null && $activeCats) {
        $firstCat = $activeCats[0];
        $featured = $byCat[(string) $firstCat['slug']][0] ?? null;
        $featuredCatName = (string) ($firstCat['name'] ?? '');
    }
}

$showImages = !in_array($layout, ['board'], true);
?>
<div
  class="brochure theme-<?= e($themeId) ?> layout-<?= e($layout) ?>"
  data-brochure
  data-theme="<?= e($themeId) ?>"
  data-layout="<?= e($layout) ?>"
>
  <header class="brochure-hero">
    <img class="brochure-logo" src="<?= e(logo_url()) ?>" alt="Crisp &amp; Co." width="120" height="120">
    <p class="eyebrow">Lezzetin doğal adresi</p>
    <h1>Crisp &amp; Co.</h1>
    <p class="brochure-menu-label">Menü</p>
    <?php if (!empty($table['label'])): ?>
      <p class="brochure-table"><?= e((string) $table['label']) ?></p>
    <?php endif; ?>
    <p class="brochure-lede">Izgara lezzetler · sıcak bar · hızlı servis</p>
  </header>

  <?php if ($layout === 'split' && $activeCats): ?>
    <nav class="brochure-catnav" aria-label="Kategoriler">
      <?php foreach ($activeCats as $cat): ?>
        <a class="brochure-catnav-link" href="#brosur-<?= e((string) $cat['slug']) ?>"><?= e((string) $cat['name']) ?></a>
      <?php endforeach; ?>
    </nav>
  <?php endif; ?>

  <?php if ($layout === 'magazine' && is_array($featured)): ?>
    <?php $featImg = $resolveImage($featured); ?>
    <section class="brochure-featured">
      <?php if ($featImg !== ''): ?>
        <div class="brochure-featured-media" style="background-image:url('<?= e($featImg) ?>')"></div>
      <?php endif; ?>
      <div class="brochure-featured-body">
        <?php if ($featuredCatName !== ''): ?>
          <p class="eyebrow"><?= e($featuredCatName) ?></p>
        <?php endif; ?>
        <h2><?= e((string) $featured['name']) ?></h2>
        <?php if (!empty($featured['description'])): ?>
          <p><?= e((string) $featured['description']) ?></p>
        <?php endif; ?>
        <span class="price"><?= e(money((float) $featured['price'])) ?></span>
        <span class="price-vat">KDV dahil</span>
      </div>
    </section>
  <?php endif; ?>

  <?php foreach ($activeCats as $cat): ?>
    <?php
      $slug = (string) $cat['slug'];
      $rows = $byCat[$slug] ?? [];
      if ($layout === 'magazine' && is_array($featured)) {
          $rows = array_values(array_filter(
              $rows,
              static fn(array $item): bool => (int) ($item['id'] ?? 0) !== (int) ($featured['id'] ?? 0)
          ));
          if ($rows === []) {
              continue;
          }
      }
    ?>
    <section class="brochure-section" id="brosur-<?= e($slug) ?>">
      <h2><?= e((string) $cat['name']) ?></h2>
      <div class="brochure-list">
        <?php foreach ($rows as $item): ?>
          <?php $imageSrc = $showImages ? $resolveImage($item) : ''; ?>
          <article class="brochure-item<?= $imageSrc !== '' ? ' has-media' : '' ?>">
            <?php if ($imageSrc !== ''): ?>
              <div class="brochure-thumb" style="background-image:url('<?= e($imageSrc) ?>')"></div>
            <?php endif; ?>
            <div class="brochure-item-body">
              <?php if ($layout === 'board'): ?>
                <div class="brochure-board-row">
                  <h3><?= e((string) $item['name']) ?></h3>
                  <span class="brochure-dots" aria-hidden="true"></span>
                  <span class="price"><?= e(money((float) $item['price'])) ?></span>
                </div>
                <?php if (!empty($item['description'])): ?>
                  <p><?= e((string) $item['description']) ?></p>
                <?php endif; ?>
              <?php else: ?>
                <div class="meta-row">
                  <h3><?= e((string) $item['name']) ?></h3>
                  <span class="price-wrap">
                    <span class="price"><?= e(money((float) $item['price'])) ?></span>
                    <span class="price-vat">KDV dahil</span>
                  </span>
                </div>
                <?php if (!empty($item['description'])): ?>
                  <p><?= e((string) $item['description']) ?></p>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>

  <footer class="brochure-foot">
    <p class="small muted">Fiyatlarımız KDV dahildir (%10 restoran yeme-içme hizmeti).</p>
    <p>Garsonunuza sipariş verebilir veya online sipariş açabilirsiniz.</p>
    <div class="cta-row">
      <a class="btn btn-accent" href="<?= e(url('/siparis')) ?>">Online Sipariş</a>
      <a class="btn btn-ghost" href="<?= e(url('/takip')) ?>">Sipariş Takip</a>
    </div>
  </footer>
</div>

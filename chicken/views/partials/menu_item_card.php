<?php
/** @var array $item */
$showDescription = !empty($showDescription);
$showAdd = !empty($showAdd);
$image = trim((string) ($item['image_url'] ?? ''));
$cat = (string) ($item['category_slug'] ?? '');
?>
<article class="menu-item<?= $image !== '' ? ' has-media' : '' ?>"<?= $cat !== '' ? ' data-cat="' . e($cat) . '"' : '' ?>>
  <div class="menu-item-media<?= $image === '' ? ' is-empty' : '' ?>">
    <?php if ($image !== ''): ?>
      <img
        src="<?= e(url($image)) ?>"
        alt="<?= e((string) $item['name']) ?>"
        loading="lazy"
        decoding="async"
        width="480"
        height="360"
      >
    <?php endif; ?>
  </div>
  <div class="menu-item-body">
    <div class="meta-row">
      <span class="chip <?= e((string) $item['station']) ?>"><?= e(station_label((string) $item['station'])) ?></span>
      <span class="price"><?= e(money((float) $item['price'])) ?></span>
    </div>
    <h3><?= e((string) $item['name']) ?></h3>
    <?php if ($showDescription): ?>
      <p><?= e((string) ($item['description'] ?? '')) ?></p>
    <?php endif; ?>
    <?php if ($showAdd): ?>
      <button
        class="btn btn-dark btn-sm"
        type="button"
        data-add-item="<?= (int) $item['id'] ?>"
        data-name="<?= e((string) $item['name']) ?>"
        data-price="<?= e((string) $item['price']) ?>"
        data-station="<?= e((string) $item['station']) ?>"
      >Ekle</button>
    <?php endif; ?>
  </div>
</article>

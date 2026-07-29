<?php
/** @var array $item */
$showDescription = !empty($showDescription);
$showAdd = !empty($showAdd);
$image = class_exists('MenuImageSync')
    ? MenuImageSync::resolve($item)
    : trim((string) ($item['image_url'] ?? ''));
$cat = (string) ($item['category_slug'] ?? '');
$imageSrc = $image !== '' ? asset_url($image) : '';
?>
<article class="menu-item<?= $imageSrc !== '' ? ' has-media' : '' ?>"<?= $cat !== '' ? ' data-cat="' . e($cat) . '"' : '' ?>>
  <?php if ($imageSrc !== ''): ?>
    <div
      class="menu-item-media"
      style="background-image:url('<?= e($imageSrc) ?>')"
    >
      <img
        src="<?= e($imageSrc) ?>"
        alt="<?= e((string) $item['name']) ?>"
        loading="eager"
        decoding="async"
        width="480"
        height="360"
      >
    </div>
  <?php else: ?>
    <div class="menu-item-media is-empty" aria-hidden="true"></div>
  <?php endif; ?>
  <div class="menu-item-body">
    <div class="meta-row">
      <span class="chip <?= e((string) $item['station']) ?>"><?= e(station_label((string) $item['station'])) ?></span>
      <span class="price-wrap">
        <span class="price"><?= e(money((float) $item['price'])) ?></span>
        <span class="price-vat">KDV dahil<?= isset($item['vat_rate']) ? ' · ' . e(format_vat_rate($item['vat_rate'])) : '' ?></span>
      </span>
    </div>
    <h3><?= e((string) $item['name']) ?></h3>
    <?php if ($showDescription): ?>
      <p><?= e((string) ($item['description'] ?? '')) ?></p>
    <?php endif; ?>
    <?php if ($showAdd): ?>
      <label class="menu-item-note">
        <span class="visually-hidden">Ürün notu</span>
        <input
          type="text"
          maxlength="255"
          data-item-add-note
          placeholder="Bu ürün için not yazın..."
          autocomplete="off"
        >
      </label>
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

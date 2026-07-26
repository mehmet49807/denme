<?php
/** @var array|null $item */
/** @var array $categories */
$isEdit = !empty($item);
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Menü</p>
    <h1><?= $isEdit ? 'Ürün düzenle' : 'Ürün ekle' ?></h1>
  </div>
  <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/urunler')) ?>">Ürünlere dön</a>
</div>

<section class="panel" style="max-width:520px">
  <form
    method="post"
    action="<?= e(url($isEdit ? '/yonetici/urunler/' . (int) $item['id'] : '/yonetici/urunler/ekle')) ?>"
    class="stack"
  >
    <?= csrf_field() ?>
    <label>Ürün adı
      <input name="name" required maxlength="160" value="<?= e((string) ($item['name'] ?? '')) ?>">
    </label>
    <label>Açıklama
      <textarea name="description" rows="2" maxlength="400" placeholder="İsteğe bağlı"><?= e((string) ($item['description'] ?? '')) ?></textarea>
    </label>
    <label>Görsel URL
      <input
        name="image_url"
        maxlength="255"
        placeholder="/assets/img/menu/ornek.jpg"
        value="<?= e((string) ($item['image_url'] ?? '')) ?>"
      >
    </label>
    <?php if (!empty($item['image_url'])): ?>
      <div class="admin-product-preview">
        <img src="<?= e(url((string) $item['image_url'])) ?>" alt="<?= e((string) $item['name']) ?>">
      </div>
    <?php endif; ?>
    <label>Kategori
      <select name="category_id" required>
        <option value="">Seçin</option>
        <?php foreach ($categories as $cat): ?>
          <option
            value="<?= (int) $cat['id'] ?>"
            <?= (int) ($item['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>
          ><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Fiyat (₺)
      <input type="number" name="price" required min="0" step="0.01" value="<?= e((string) ($item['price'] ?? '')) ?>">
    </label>
    <label>İstasyon
      <select name="station" required>
        <option value="kitchen" <?= ($item['station'] ?? 'kitchen') === 'kitchen' ? 'selected' : '' ?>>Mutfak</option>
        <option value="bar" <?= ($item['station'] ?? '') === 'bar' ? 'selected' : '' ?>>Bar</option>
      </select>
    </label>
    <label>Sıra
      <input type="number" name="sort_order" min="0" max="999" value="<?= (int) ($item['sort_order'] ?? 0) ?>">
    </label>
    <label class="meta-row" style="align-items:center;gap:10px">
      <input type="checkbox" name="is_available" value="1" <?= !isset($item['is_available']) || !empty($item['is_available']) ? 'checked' : '' ?>>
      <span>Satışta</span>
    </label>
    <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Güncelle' : 'Ürünü kaydet' ?></button>
  </form>
</section>

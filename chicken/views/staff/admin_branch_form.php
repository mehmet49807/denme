<?php
/** @var array|null $branch */
$branch = $branch ?? null;
$isEdit = is_array($branch);
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Franchise</p>
    <h1><?= $isEdit ? 'Şube düzenle' : 'Şube ekle' ?></h1>
  </div>
  <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/franchise/subeler')) ?>">Şubelere dön</a>
</div>

<?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>

<section class="panel" style="max-width:560px">
  <form method="post" class="stack" action="<?= e(url($isEdit ? '/yonetici/franchise/subeler/' . (int) $branch['id'] : '/yonetici/franchise/subeler/ekle')) ?>">
    <?= csrf_field() ?>
    <label>Şube adı
      <input name="name" required value="<?= e((string) ($branch['name'] ?? '')) ?>" placeholder="Antalya Lara">
    </label>
    <label>Şehir
      <input name="city" required value="<?= e((string) ($branch['city'] ?? '')) ?>" placeholder="Antalya">
    </label>
    <label>Telefon
      <input name="phone" value="<?= e((string) ($branch['phone'] ?? '')) ?>" placeholder="0242...">
    </label>
    <label>WhatsApp
      <input name="whatsapp" value="<?= e((string) ($branch['whatsapp'] ?? '')) ?>" placeholder="05xx...">
    </label>
    <label>Adres
      <textarea name="address" rows="2" placeholder="Mahalle, cadde..."><?= e((string) ($branch['address'] ?? '')) ?></textarea>
    </label>
    <label>Sıra
      <input type="number" name="sort_order" value="<?= e((string) ($branch['sort_order'] ?? '0')) ?>">
    </label>
    <label class="check-line">
      <input type="checkbox" name="is_active" value="1" <?= (!$isEdit || !empty($branch['is_active'])) ? 'checked' : '' ?>>
      <span>Aktif şube (bayilik formunda görünsün)</span>
    </label>
    <button class="btn btn-accent" type="submit"><?= $isEdit ? 'Kaydet' : 'Şube ekle' ?></button>
  </form>
</section>

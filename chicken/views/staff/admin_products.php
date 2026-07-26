<?php
/** @var array $items */
/** @var array $categories */
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Menü</p>
    <h1>Ürünler</h1>
  </div>
  <a class="btn btn-primary btn-sm" href="<?= e(url('/yonetici/urunler/ekle')) ?>">Ürün ekle</a>
</div>

<section class="panel">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Ürün</th>
          <th>Kategori</th>
          <th>İstasyon</th>
          <th>Fiyat</th>
          <th>Durum</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$items): ?>
          <tr><td colspan="6" class="muted">Henüz ürün yok.</td></tr>
        <?php endif; ?>
        <?php foreach ($items as $item): ?>
          <tr>
            <td>
              <strong><?= e($item['name']) ?></strong>
              <?php if (!empty($item['description'])): ?>
                <div class="small muted"><?= e($item['description']) ?></div>
              <?php endif; ?>
            </td>
            <td><?= e($item['category_name'] ?? '—') ?></td>
            <td><?= e(station_label((string) $item['station'])) ?></td>
            <td class="price"><?= e(money((float) $item['price'])) ?></td>
            <td><?= !empty($item['is_available']) ? 'Satışta' : 'Kapalı' ?></td>
            <td>
              <div class="cta-row">
                <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/urunler/' . (int) $item['id'])) ?>">Düzenle</a>
                <form method="post" action="<?= e(url('/yonetici/urunler/durum')) ?>" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                  <input type="hidden" name="is_available" value="<?= !empty($item['is_available']) ? '0' : '1' ?>">
                  <button class="btn btn-dark btn-sm" type="submit">
                    <?= !empty($item['is_available']) ? 'Kapat' : 'Aç' ?>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

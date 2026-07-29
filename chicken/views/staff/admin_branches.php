<?php
/** @var list<array> $branches */
$branches = $branches ?? [];
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Franchise</p>
    <h1>Şubeler</h1>
  </div>
  <div class="cta-row">
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/franchise')) ?>">Başvurular</a>
    <a class="btn btn-accent btn-sm" href="<?= e(url('/yonetici/franchise/whatsapp')) ?>">WhatsApp</a>
    <a class="btn btn-primary btn-sm" href="<?= e(url('/yonetici/franchise/subeler/ekle')) ?>">Şube ekle</a>
  </div>
</div>

<?php if ($msg = flash('success')): ?><div class="alert alert-ok"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>

<section class="panel">
  <?php if (!$branches): ?>
    <p class="muted" style="margin:0">Henüz şube yok. İlk şubeyi ekleyin.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Şube</th>
            <th>Şehir</th>
            <th>Telefon</th>
            <th>WhatsApp</th>
            <th>Sıra</th>
            <th>Durum</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($branches as $b): ?>
            <tr>
              <td><?= (int) $b['id'] ?></td>
              <td>
                <strong><?= e((string) $b['name']) ?></strong>
                <?php if (!empty($b['address'])): ?>
                  <div class="small muted"><?= e((string) $b['address']) ?></div>
                <?php endif; ?>
              </td>
              <td><?= e((string) $b['city']) ?></td>
              <td class="small"><?= e((string) ($b['phone'] ?: '—')) ?></td>
              <td class="small"><?= e((string) ($b['whatsapp'] ?: '—')) ?></td>
              <td><?= (int) $b['sort_order'] ?></td>
              <td>
                <span class="chip <?= !empty($b['is_active']) ? 'online' : '' ?>">
                  <?= !empty($b['is_active']) ? 'Aktif' : 'Pasif' ?>
                </span>
              </td>
              <td>
                <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/franchise/subeler/' . (int) $b['id'])) ?>">Düzenle</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

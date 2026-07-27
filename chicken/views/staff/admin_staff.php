<?php /** @var array $staff */ ?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Personel</p>
    <h1>Personel takip</h1>
  </div>
  <div class="cta-row">
    <a class="btn btn-primary btn-sm" href="<?= e(url('/yonetici/personel/ekle')) ?>">Personel ekle</a>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/personel/cikar')) ?>">Garson sil</a>
  </div>
</div>

<section class="panel">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Ad</th>
          <th>Kullanıcı</th>
          <th>Yetki</th>
          <th>Durum</th>
          <th>Kayıt</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($staff as $member): ?>
          <tr>
            <td><?= e($member['name']) ?></td>
            <td><?= e($member['username']) ?></td>
            <td><?= e(role_label((string) $member['role'])) ?></td>
            <td><?= !empty($member['is_active']) ? 'Aktif' : 'Pasif' ?></td>
            <td class="small muted"><?= e($member['created_at'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

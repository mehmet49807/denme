<?php /** @var array $staff */ ?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Personel</p>
    <h1>Personel çıkarma</h1>
  </div>
  <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/personel')) ?>">Takibe dön</a>
</div>

<p class="muted" style="margin:-8px 0 16px">Personel silinmez; hesabı pasife alınır ve giriş yapamaz.</p>

<section class="panel">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Ad</th>
          <th>Kullanıcı</th>
          <th>Yetki</th>
          <th>İşlem</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($staff as $member): ?>
          <tr>
            <td><?= e($member['name']) ?></td>
            <td><?= e($member['username']) ?></td>
            <td><?= e(role_label((string) $member['role'])) ?></td>
            <td>
              <?php if ((int) $member['id'] === (int) Auth::id()): ?>
                <span class="muted small">Siz</span>
              <?php elseif (empty($member['is_active'])): ?>
                <form method="post" action="<?= e(url('/yonetici/personel/aktif')) ?>" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="staff_id" value="<?= (int) $member['id'] ?>">
                  <button class="btn btn-ghost btn-sm" type="submit">Aktifleştir</button>
                </form>
              <?php else: ?>
                <form method="post" action="<?= e(url('/yonetici/personel/cikar')) ?>" style="display:inline" onsubmit="return confirm('Personel pasife alınsın mı?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="staff_id" value="<?= (int) $member['id'] ?>">
                  <button class="btn btn-dark btn-sm" type="submit">Çıkar</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

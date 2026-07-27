<?php
/** @var array $staff */
$me = (int) Auth::id();
?>
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
          <th>İşlem</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($staff as $member): ?>
          <?php
            $id = (int) $member['id'];
            $role = (string) $member['role'];
            $active = !empty($member['is_active']);
            $isMe = $id === $me;
            $isWaiter = $role === 'waiter';
          ?>
          <tr>
            <td><?= e($member['name']) ?></td>
            <td><?= e($member['username']) ?></td>
            <td><?= e(role_label($role)) ?></td>
            <td><?= $active ? 'Aktif' : 'Pasif' ?></td>
            <td class="small muted"><?= e($member['created_at'] ?? '') ?></td>
            <td>
              <?php if ($isMe): ?>
                <span class="muted small">Siz</span>
              <?php else: ?>
                <div class="cta-row" style="flex-wrap:wrap;gap:6px">
                  <?php if ($active): ?>
                    <form method="post" action="<?= e(url('/yonetici/personel/cikar')) ?>" style="display:inline;margin:0"
                      onsubmit="return confirm('Personel pasife alınsın mı?')">
                      <?= csrf_field() ?>
                      <input type="hidden" name="staff_id" value="<?= $id ?>">
                      <input type="hidden" name="redirect" value="/yonetici/personel">
                      <button class="btn btn-ghost btn-sm" type="submit">Pasif</button>
                    </form>
                  <?php else: ?>
                    <form method="post" action="<?= e(url('/yonetici/personel/aktif')) ?>" style="display:inline;margin:0">
                      <?= csrf_field() ?>
                      <input type="hidden" name="staff_id" value="<?= $id ?>">
                      <input type="hidden" name="redirect" value="/yonetici/personel">
                      <button class="btn btn-ghost btn-sm" type="submit">Aktif</button>
                    </form>
                  <?php endif; ?>
                  <?php if ($isWaiter): ?>
                    <form method="post" action="<?= e(url('/yonetici/personel/cikar')) ?>" style="display:inline;margin:0"
                      onsubmit="return confirm('<?= e($member['name']) ?> hesabı KALICI silinsin mi? Geri alınamaz.')">
                      <?= csrf_field() ?>
                      <input type="hidden" name="staff_id" value="<?= $id ?>">
                      <input type="hidden" name="role_guard" value="waiter">
                      <input type="hidden" name="hard_delete" value="1">
                      <input type="hidden" name="redirect" value="/yonetici/personel">
                      <button class="btn btn-dark btn-sm" type="submit">Sil</button>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

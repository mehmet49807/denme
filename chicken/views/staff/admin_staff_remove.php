<?php
/** @var array $staff */
$waiters = array_values(array_filter($staff, static fn(array $m): bool => ($m['role'] ?? '') === 'waiter'));
$others = array_values(array_filter($staff, static fn(array $m): bool => ($m['role'] ?? '') !== 'waiter'));
$me = (int) Auth::id();
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Personel</p>
    <h1>Garson sil</h1>
  </div>
  <div class="cta-row">
    <a class="btn btn-primary btn-sm" href="<?= e(url('/yonetici/personel/ekle')) ?>">Personel ekle</a>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/personel')) ?>">Takibe dön</a>
  </div>
</div>

<p class="muted" style="margin:-8px 0 16px">
  Garson hesabı veritabanından <strong>kalıcı olarak silinir</strong> (pasife alma değil). Giriş yapamaz; sipariş geçmişi kalır, garson bağlantısı kaldırılır.
</p>

<section class="panel">
  <h2 class="side-group-title" style="margin:0 0 12px">Garsonlar</h2>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Ad</th>
          <th>Kullanıcı</th>
          <th>Durum</th>
          <th>İşlem</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$waiters): ?>
          <tr><td colspan="4" class="muted">Kayıtlı garson yok.</td></tr>
        <?php endif; ?>
        <?php foreach ($waiters as $member): ?>
          <tr>
            <td><strong><?= e($member['name']) ?></strong></td>
            <td><?= e($member['username']) ?></td>
            <td><?= !empty($member['is_active']) ? 'Aktif' : 'Pasif' ?></td>
            <td>
              <?php if ((int) $member['id'] === $me): ?>
                <span class="muted small">Siz</span>
              <?php else: ?>
                <form
                  method="post"
                  action="<?= e(url('/yonetici/personel/cikar')) ?>"
                  style="display:inline"
                  onsubmit="return confirm('<?= e($member['name']) ?> hesabı KALICI silinsin mi? Pasife alınmaz — hesap tamamen silinir ve geri alınamaz.')"
                >
                  <?= csrf_field() ?>
                  <input type="hidden" name="staff_id" value="<?= (int) $member['id'] ?>">
                  <input type="hidden" name="role_guard" value="waiter">
                  <input type="hidden" name="hard_delete" value="1">
                  <input type="hidden" name="redirect" value="/yonetici/personel/cikar">
                  <button class="btn btn-dark btn-sm" type="submit">Hesabı sil</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php if ($others): ?>
<section class="panel" style="margin-top:18px">
  <h2 class="side-group-title" style="margin:0 0 12px">Diğer personel</h2>
  <p class="muted small" style="margin:0 0 12px">Kasa / yönetici için pasife alma (hesap silinmez).</p>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Ad</th>
          <th>Kullanıcı</th>
          <th>Yetki</th>
          <th>Durum</th>
          <th>İşlem</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($others as $member): ?>
          <tr>
            <td><?= e($member['name']) ?></td>
            <td><?= e($member['username']) ?></td>
            <td><?= e(role_label((string) $member['role'])) ?></td>
            <td><?= !empty($member['is_active']) ? 'Aktif' : 'Pasif' ?></td>
            <td>
              <?php if ((int) $member['id'] === $me): ?>
                <span class="muted small">Siz</span>
              <?php elseif (empty($member['is_active'])): ?>
                <form method="post" action="<?= e(url('/yonetici/personel/aktif')) ?>" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="staff_id" value="<?= (int) $member['id'] ?>">
                  <input type="hidden" name="redirect" value="/yonetici/personel/cikar">
                  <button class="btn btn-ghost btn-sm" type="submit">Aktifleştir</button>
                </form>
              <?php else: ?>
                <form
                  method="post"
                  action="<?= e(url('/yonetici/personel/cikar')) ?>"
                  style="display:inline"
                  onsubmit="return confirm('Personel pasife alınsın mı?')"
                >
                  <?= csrf_field() ?>
                  <input type="hidden" name="staff_id" value="<?= (int) $member['id'] ?>">
                  <input type="hidden" name="redirect" value="/yonetici/personel/cikar">
                  <button class="btn btn-dark btn-sm" type="submit">Pasife al</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php endif; ?>

<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Personel</p>
    <h1>Personel ekle</h1>
  </div>
  <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/personel')) ?>">Personel takip</a>
</div>

<section class="panel" style="max-width:480px">
  <form method="post" action="<?= e(url('/yonetici/personel')) ?>" class="stack">
    <?= csrf_field() ?>
    <input type="hidden" name="redirect" value="/yonetici/personel/ekle">
    <label>Ad
      <input name="name" required>
    </label>
    <label>Kullanıcı adı
      <input name="username" required autocomplete="username">
    </label>
    <label>Parola
      <input type="password" name="password" required minlength="6" autocomplete="new-password">
    </label>
    <label>Rol / yetki
      <select name="role" required>
        <option value="waiter">Garson</option>
        <option value="cashier">Kasa</option>
        <option value="admin">Yönetici (tüm yetkiler)</option>
      </select>
    </label>
    <p class="muted small">Yönetici: masa, kasa, garson, mutfak, bar ve personel işlemlerinin tamamı.</p>
    <button class="btn btn-primary" type="submit">Personeli kaydet</button>
  </form>
</section>

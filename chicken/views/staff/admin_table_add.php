<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Masalar</p>
    <h1>Masa ekleme</h1>
  </div>
  <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/masalar')) ?>">Tüm masalar</a>
</div>

<section class="panel" style="max-width:480px">
  <form method="post" action="<?= e(url('/yonetici/masalar/ekle')) ?>" class="stack">
    <?= csrf_field() ?>
    <label>Masa kodu
      <input name="code" required maxlength="20" placeholder="Örn: M9" pattern="[A-Za-z0-9_-]+">
    </label>
    <label>Masa adı
      <input name="label" required maxlength="80" placeholder="Örn: Masa 9">
    </label>
    <label>Kişi sayısı
      <input type="number" name="seats" min="1" max="50" value="4" required>
    </label>
    <button class="btn btn-primary" type="submit">Masayı kaydet</button>
  </form>
</section>

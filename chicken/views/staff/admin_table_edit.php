<?php /** @var array $table */ ?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Masalar</p>
    <h1>Masa düzenle</h1>
  </div>
  <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/masalar')) ?>">Tüm masalar</a>
</div>

<section class="panel" style="max-width:480px">
  <form method="post" action="<?= e(url('/yonetici/masalar/' . (int) $table['id'])) ?>" class="stack">
    <?= csrf_field() ?>
    <label>Masa kodu
      <input name="code" required maxlength="20" value="<?= e((string) $table['code']) ?>" pattern="[A-Za-z0-9_-]+">
    </label>
    <label>Masa adı
      <input name="label" required maxlength="80" value="<?= e((string) $table['label']) ?>">
    </label>
    <label>Kişi sayısı
      <input type="number" name="seats" min="1" max="50" value="<?= (int) $table['seats'] ?>" required>
    </label>
    <label class="meta-row" style="align-items:center;gap:10px">
      <input type="checkbox" name="is_active" value="1" <?= !empty($table['is_active']) ? 'checked' : '' ?>>
      <span>Aktif masa</span>
    </label>
    <p class="muted small">QR: <?= e((string) $table['qr_token']) ?></p>
    <button class="btn btn-primary" type="submit">Kaydet</button>
  </form>
</section>

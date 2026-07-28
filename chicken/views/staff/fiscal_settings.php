<?php
/** @var array $company */
$company = $company ?? [];
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Mali ayarlar</p>
    <h1>Firma ve KDV</h1>
  </div>
  <a class="btn btn-ghost btn-sm" href="<?= e(url('/kasa/gun-sonu')) ?>">Gün sonu</a>
</div>

<section class="panel" style="max-width:560px">
  <p class="muted" style="margin-top:0">Satış faturası ve gün sonu belgelerinde görünecek bilgiler. Restoran hizmeti için varsayılan KDV %10’dur (ayarlanabilir).</p>
  <form method="post" action="<?= e(url('/kasa/fatura-ayarlar')) ?>" class="stack">
    <?= csrf_field() ?>
    <label>Firma ünvanı
      <input name="title" required maxlength="160" value="<?= e((string) ($company['title'] ?? '')) ?>">
    </label>
    <label>VKN / TCKN
      <input name="vkn" maxlength="11" inputmode="numeric" pattern="\d{10,11}" value="<?= e((string) ($company['vkn'] ?? '')) ?>" placeholder="10 veya 11 hane">
    </label>
    <label>Vergi dairesi
      <input name="tax_office" maxlength="120" value="<?= e((string) ($company['tax_office'] ?? '')) ?>">
    </label>
    <label>Adres
      <textarea name="address" rows="2"><?= e((string) ($company['address'] ?? '')) ?></textarea>
    </label>
    <label>Şehir
      <input name="city" maxlength="80" value="<?= e((string) ($company['city'] ?? '')) ?>">
    </label>
    <label>Telefon
      <input name="phone" maxlength="40" value="<?= e((string) ($company['phone'] ?? '')) ?>">
    </label>
    <label>KDV oranı (%)
      <input type="number" name="vat_rate" min="0" max="20" step="0.01" value="<?= e((string) ($company['vat_rate'] ?? '10')) ?>" required>
    </label>
    <button class="btn btn-primary" type="submit">Kaydet</button>
  </form>
</section>

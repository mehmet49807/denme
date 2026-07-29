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
  <p class="muted" style="margin-top:0">
    Satış fişi ve gün sonu belgelerinde görünecek bilgiler.
    Menü ürünlerinin kendi KDV oranı vardır (restoran yeme-içme %10, alkollü içecek %20).
    Aşağıdaki oran, ürün oranı yoksa kullanılan varsayılandır.
  </p>
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
    <label>Varsayılan KDV oranı
      <?php $vatSelected = (float) ($company['vat_rate'] ?? 10); ?>
      <select name="vat_rate" required>
        <option value="1" <?= abs($vatSelected - 1.0) < 0.001 ? 'selected' : '' ?>>%1 — temel gıda</option>
        <option value="10" <?= abs($vatSelected - 10.0) < 0.001 ? 'selected' : '' ?>>%10 — restoran yeme-içme</option>
        <option value="20" <?= abs($vatSelected - 20.0) < 0.001 ? 'selected' : '' ?>>%20 — alkollü / genel</option>
      </select>
    </label>
    <button class="btn btn-primary" type="submit">Kaydet</button>
  </form>
</section>

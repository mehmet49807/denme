<?php
/** @var array $company */
/** @var bool $slipAutoprint */
/** @var string $slipPaperWidth */
$company = $company ?? [];
$slipAutoprint = !isset($slipAutoprint) || !empty($slipAutoprint);
$slipPaperWidth = ($slipPaperWidth ?? '80') === '58' ? '58' : '80';
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

    <hr style="border:0;border-top:1px solid var(--line);margin:8px 0">
    <p class="eyebrow" style="margin:0">Mutfak / bar XPrinter</p>
    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer">
      <input type="checkbox" name="slip_autoprint" value="1" <?= $slipAutoprint ? 'checked' : '' ?> style="margin-top:4px">
      <span>Garson siparişi ve online onay sonrası otomatik yazdır</span>
    </label>
    <label>Fiş kağıt genişliği
      <select name="slip_paper_width">
        <option value="80" <?= $slipPaperWidth === '80' ? 'selected' : '' ?>>80mm (çoğu XPrinter)</option>
        <option value="58" <?= $slipPaperWidth === '58' ? 'selected' : '' ?>>58mm</option>
      </select>
    </label>
    <p class="muted small" style="margin:0">
      Otomatik yazdırma, personel bilgisayarındaki yazıcı diyaloğunu açar.
      XPrinter’ı Windows’ta varsayılan yazıcı yapın; tarayıcıda “bu site için yazıcıyı hatırla” seçeneğini kullanın.
    </p>

    <button class="btn btn-primary" type="submit">Kaydet</button>
  </form>
</section>

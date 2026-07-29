<?php
/** @var bool $enabled */
/** @var string $number */
/** @var bool $autoOpen */
/** @var string $apiToken */
/** @var string $phoneNumberId */
/** @var array|null $pending */
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Franchise</p>
    <h1>WhatsApp sipariş bildirimi</h1>
  </div>
  <div class="cta-row">
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/franchise/subeler')) ?>">Şubeler</a>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/online-siparisler')) ?>">Online siparişler</a>
  </div>
</div>

<?php if ($msg = flash('success')): ?><div class="alert alert-ok"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>

<section class="panel" style="max-width:640px">
  <p class="muted" style="margin-top:0">
    Yeni online sipariş geldiğinde kasa/yönetici ekranında WhatsApp bildirimi hazırlanır.
    Meta Cloud API token’ı varsa sunucu da otomatik mesaj göndermeyi dener.
  </p>
  <form method="post" action="<?= e(url('/yonetici/franchise/whatsapp')) ?>" class="stack">
    <?= csrf_field() ?>
    <label class="check-line">
      <input type="checkbox" name="whatsapp_enabled" value="1" <?= !empty($enabled) ? 'checked' : '' ?>>
      <span>WhatsApp bildirimlerini aç</span>
    </label>
    <label>Bildirim numarası (işletme WhatsApp)
      <input name="whatsapp_notify_number" value="<?= e($number) ?>" placeholder="905xxxxxxxxx">
    </label>
    <label class="check-line">
      <input type="checkbox" name="whatsapp_auto_open" value="1" <?= !empty($autoOpen) ? 'checked' : '' ?>>
      <span>Personel panelinde yeni siparişte WhatsApp’ı otomatik aç</span>
    </label>
    <hr style="border:none;border-top:1px solid var(--line);margin:8px 0">
    <p class="small muted" style="margin:0">İsteğe bağlı — Meta WhatsApp Cloud API</p>
    <label>Phone Number ID
      <input name="whatsapp_phone_number_id" value="<?= e($phoneNumberId) ?>" autocomplete="off">
    </label>
    <label>Access Token
      <input name="whatsapp_api_token" value="<?= e($apiToken) ?>" autocomplete="off">
    </label>
    <button class="btn btn-accent" type="submit">Kaydet</button>
  </form>
</section>

<?php if (!empty($pending) && !empty($pending['url'])): ?>
  <section class="panel" style="margin-top:16px;max-width:640px">
    <h2 style="margin:0 0 8px;font-family:var(--font-display);font-size:1.15rem">Son bekleyen bildirim</h2>
    <p class="muted small">Sipariş #<?= e((string) ($pending['order_code'] ?? $pending['order_id'])) ?></p>
    <a class="btn btn-primary btn-sm" href="<?= e((string) $pending['url']) ?>" target="_blank" rel="noopener">WhatsApp’ta aç</a>
  </section>
<?php endif; ?>

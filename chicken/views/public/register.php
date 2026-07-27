<?php
/** @var string $title */
/** @var string $welcomeCode */
/** @var int $welcomePercent */
?>
<div class="page-shell auth-page">
  <div class="auth-card" style="margin:0 auto;max-width:440px">
    <div class="auth-logo">
      <img src="<?= e(url('/assets/img/logo.svg')) ?>" alt="" width="56" height="56">
    </div>
    <p class="eyebrow">Yeni üyelik</p>
    <h1>Üye ol</h1>
    <div class="promo-banner" style="margin:0 0 16px">
      Yeni kayıt olan müşterilere <strong>%<?= (int) $welcomePercent ?> indirim</strong>
      · Kod: <strong><?= e($welcomeCode) ?></strong>
    </div>
    <p class="lede">Hemen kayıt olun, ilk siparişinizde indirim kodunu sepete yazın.</p>
    <?php if ($msg = flash('error')): ?>
      <div class="alert alert-error"><?= e($msg) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= e(url('/uye-ol')) ?>" class="stack">
      <?= csrf_field() ?>
      <label>Ad Soyad
        <input name="name" required autocomplete="name" placeholder="Adınız Soyadınız">
      </label>
      <label>E-posta
        <input type="email" name="email" required autocomplete="email" placeholder="ornek@mail.com">
      </label>
      <label>Telefon
        <input name="phone" required autocomplete="tel" placeholder="05xx...">
      </label>
      <label>Adres
        <textarea name="address" required rows="3" autocomplete="street-address" placeholder="Mahalle, sokak, bina no, ilçe / il"></textarea>
      </label>
      <label>Parola
        <input type="password" name="password" required minlength="6" autocomplete="new-password" placeholder="En az 6 karakter">
      </label>
      <button class="btn btn-accent btn-block" type="submit">Kayıt ol</button>
    </form>
    <p class="auth-foot">
      Zaten üye misiniz?
      <a class="btn btn-nav-login btn-sm" href="<?= e(url('/giris')) ?>">Giriş yap</a>
    </p>
  </div>
</div>

<?php /** @var string $title */ ?>
<div class="page-shell auth-page">
  <div class="auth-card" style="margin:0 auto;max-width:420px">
    <div class="auth-logo">
      <img class="auth-logo-img" src="<?= e(logo_url()) ?>" alt="Lezzet Tavukçusu" width="88" height="88">
    </div>
    <p class="eyebrow">Lezzet Tavukçusu</p>
    <h1>Giriş</h1>
    <p class="lede">Hesabınıza giriş yapın, sipariş verin.</p>
    <?php if ($msg = flash('error')): ?>
      <div class="alert alert-error"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash('success')): ?>
      <div class="alert alert-ok"><?= e($msg) ?></div>
    <?php endif; ?>

    <?php partial('partials/google_auth_button', ['label' => 'Google ile giriş yap']); ?>
    <div class="auth-divider"><span>veya</span></div>

    <form method="post" action="<?= e(url('/giris')) ?>" class="stack">
      <?= csrf_field() ?>
      <label>E-posta veya kullanıcı adı
        <input name="login" required autocomplete="username" placeholder="ornek@mail.com">
      </label>
      <label>Parola
        <input type="password" name="password" required autocomplete="current-password">
      </label>
      <button class="btn btn-accent btn-block" type="submit">Giriş yap</button>
    </form>
    <p class="auth-foot">
      Hesabınız yok mu?
      <a class="btn btn-nav-login btn-sm" href="<?= e(url('/uye-ol')) ?>">Üye ol</a>
    </p>
    <p class="muted small" style="margin-top:12px;text-align:center">
      Yeni üyelere <strong>YENI10</strong> ile %10 indirim
    </p>
  </div>
</div>

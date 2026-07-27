<?php /** @var string $title */ ?>
<div class="page-shell auth-page">
  <div class="auth-card" style="margin:0 auto;max-width:420px">
    <p class="eyebrow">Müşteri & personel</p>
    <h1>Giriş</h1>
    <p class="lede">Üye girişi. Garson, kasa veya yönetici hesabıyla da giriş yapabilirsiniz.</p>
    <?php if ($msg = flash('error')): ?>
      <div class="alert alert-error"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash('success')): ?>
      <div class="alert alert-ok"><?= e($msg) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= e(url('/giris')) ?>" class="stack">
      <?= csrf_field() ?>
      <label>E-posta veya kullanıcı adı
        <input name="login" required autocomplete="username" placeholder="ornek@mail.com / admin">
      </label>
      <label>Parola
        <input type="password" name="password" required autocomplete="current-password">
      </label>
      <button class="btn btn-primary" type="submit">Giriş yap</button>
    </form>
    <p class="muted small" style="margin-top:16px">
      Hesabınız yok mu? <a href="<?= e(url('/uye-ol')) ?>">Üye ol</a>
      · Yeni üyelere <strong>YENI10</strong> ile %10 indirim
    </p>
  </div>
</div>

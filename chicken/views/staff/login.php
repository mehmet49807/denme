<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="auth-body">
  <main class="auth-card">
    <p class="eyebrow">Personel alanı</p>
    <h1>Giriş</h1>
    <p class="lede">Garson, kasa ve yönetici girişi.</p>
    <?php if ($msg = flash('error')): ?>
      <div class="alert alert-error"><?= e($msg) ?></div>
    <?php endif; ?>
    <form method="post" action="/personel/giris" class="stack">
      <?= csrf_field() ?>
      <label>Kullanıcı adı
        <input name="username" required autocomplete="username">
      </label>
      <label>Parola
        <input type="password" name="password" required autocomplete="current-password">
      </label>
      <button class="btn btn-primary" type="submit">Giriş yap</button>
    </form>
    <p class="muted small" style="margin-top:16px"><a href="/">← Siteye dön</a></p>
  </main>
</body>
</html>

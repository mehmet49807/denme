<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap">
  <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>?v=<?= e((string) (@filemtime(dirname(__DIR__, 2) . '/assets/css/app.css') ?: time())) ?>">
</head>
<body class="auth-body" data-base="<?= e(base_path()) ?>">
  <main class="auth-card">
    <p class="eyebrow">Personel alanı</p>
    <h1>Giriş</h1>
    <p class="lede">Garson, kasa ve yönetici girişi.</p>
    <?php if ($msg = flash('error')): ?>
      <div class="alert alert-error"><?= e($msg) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= e(url('/personel/giris')) ?>" class="stack">
      <?= csrf_field() ?>
      <label>Kullanıcı adı
        <input name="username" required autocomplete="username">
      </label>
      <label>Parola
        <input type="password" name="password" required autocomplete="current-password">
      </label>
      <button class="btn btn-primary" type="submit">Giriş yap</button>
    </form>
    <p class="muted small" style="margin-top:16px"><a href="<?= e(url('/')) ?>">← Siteye dön</a></p>
  </main>
</body>
</html>

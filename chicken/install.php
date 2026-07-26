<?php

declare(strict_types=1);

/**
 * One-time installer. Delete or protect after setup.
 */
require __DIR__ . '/app/helpers.php';

date_default_timezone_set((string) config('timezone', 'Europe/Istanbul'));

$messages = [];
$errors = [];
$done = false;

function run_sql_file(PDO $pdo, string $path): void
{
    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException('SQL dosyası okunamadı: ' . $path);
    }
    $pdo->exec($sql);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim((string) ($_POST['db_host'] ?? 'localhost'));
    $port = (int) ($_POST['db_port'] ?? 3306);
    $name = trim((string) ($_POST['db_name'] ?? 'gonulkop_chicken'));
    $user = trim((string) ($_POST['db_user'] ?? ''));
    $pass = (string) ($_POST['db_pass'] ?? '');
    $appUrl = rtrim(trim((string) ($_POST['app_url'] ?? 'https://chicken.gonulkoprusu.com')), '/');

    try {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $name);
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        run_sql_file($pdo, __DIR__ . '/sql/schema.sql');
        $messages[] = 'Şema oluşturuldu.';

        $menuCount = (int) $pdo->query('SELECT COUNT(*) FROM menu_items')->fetchColumn();
        if ($menuCount === 0) {
            run_sql_file($pdo, __DIR__ . '/sql/seed.sql');
            $messages[] = 'Örnek menü ve masalar yüklendi.';
        } else {
            $messages[] = 'Menü zaten mevcut, seed atlandı.';
        }

        $defaults = [
            ['Yönetici', 'admin', 'Admin123!', 'admin', '0000'],
            ['Kasa', 'kasa', 'Kasa123!', 'cashier', '1111'],
            ['Garson Ayşe', 'garson1', 'Garson123!', 'waiter', '2222'],
            ['Garson Mehmet', 'garson2', 'Garson123!', 'waiter', '3333'],
        ];
        $upsert = $pdo->prepare(
            'INSERT INTO staff (name, username, password_hash, role, pin, is_active)
             VALUES (?, ?, ?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE
               name = VALUES(name),
               password_hash = VALUES(password_hash),
               role = VALUES(role),
               pin = VALUES(pin),
               is_active = 1'
        );
        foreach ($defaults as $row) {
            $upsert->execute([
                $row[0],
                $row[1],
                password_hash($row[2], PASSWORD_DEFAULT),
                $row[3],
                $row[4],
            ]);
        }
        $messages[] = 'Personel hesapları hazır.';

        $local = "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n"
            . "    'app_url' => " . var_export($appUrl, true) . ",\n"
            . "    'db' => [\n"
            . "        'host' => " . var_export($host, true) . ",\n"
            . "        'port' => {$port},\n"
            . "        'name' => " . var_export($name, true) . ",\n"
            . "        'user' => " . var_export($user, true) . ",\n"
            . "        'pass' => " . var_export($pass, true) . ",\n"
            . "        'charset' => 'utf8mb4',\n"
            . "    ],\n"
            . "];\n";

        if (file_put_contents(__DIR__ . '/config/config.local.php', $local) === false) {
            throw new RuntimeException('config.local.php yazılamadı.');
        }
        $messages[] = 'config.local.php kaydedildi.';
        $done = true;
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}

?><!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Chicken Kurulum</title>
  <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>">
</head>
<body class="auth-body">
  <main class="auth-card">
    <p class="eyebrow">Chicken</p>
    <h1>Kurulum</h1>
    <p class="lede">Veritabanını oluşturur, örnek menüyü yükler ve personel hesaplarını hazırlar.</p>

    <?php foreach ($errors as $error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endforeach; ?>
    <?php foreach ($messages as $message): ?>
      <div class="alert alert-ok"><?= e($message) ?></div>
    <?php endforeach; ?>

    <?php if ($done): ?>
      <div class="stack">
        <p><strong>Giriş bilgileri</strong></p>
        <ul>
          <li>Yönetici: admin / Admin123!</li>
          <li>Kasa: kasa / Kasa123!</li>
          <li>Garson: garson1 / Garson123!</li>
        </ul>
        <a class="btn btn-primary" href="<?= e(url('/')) ?>">Siteye git</a>
        <a class="btn btn-ghost" href="<?= e(url('/personel/giris')) ?>">Personel girişi</a>
        <p class="muted small">Güvenlik için kurulumdan sonra <code>install.php</code> dosyasını silin.</p>
      </div>
    <?php else: ?>
      <form method="post" class="stack">
        <?php
          $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
          $guessUrl = isset($_SERVER['HTTP_HOST'])
            ? ($scheme . '://' . $_SERVER['HTTP_HOST'] . base_path())
            : 'https://chicken.gonulkoprusu.com';
        ?>
        <label>Uygulama URL
          <input name="app_url" value="<?= e($guessUrl) ?>" required>
        </label>
        <label>DB Host
          <input name="db_host" value="localhost" required>
        </label>
        <label>DB Port
          <input name="db_port" value="3306" required>
        </label>
        <label>DB Adı
          <input name="db_name" value="gonulkop_chicken" required>
        </label>
        <label>DB Kullanıcı
          <input name="db_user" value="gonulkop_admin" required>
        </label>
        <label>DB Parola
          <input name="db_pass" type="password" required>
        </label>
        <button class="btn btn-primary" type="submit">Kurulumu başlat</button>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>

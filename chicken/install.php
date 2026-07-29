<?php

declare(strict_types=1);

/**
 * One-time installer. Locked after DB is installed unless ops_secret is provided.
 */
require __DIR__ . '/app/helpers.php';
require __DIR__ . '/app/Database.php';

date_default_timezone_set((string) config('timezone', 'Europe/Istanbul'));

$messages = [];
$errors = [];
$done = false;
$defaultPasswords = [];

try {
    $alreadyInstalled = Database::isInstalled();
} catch (Throwable) {
    $alreadyInstalled = false;
}
if ($alreadyInstalled) {
    $unlock = (string) ($_GET['key'] ?? $_POST['ops_key'] ?? '');
    $secret = ops_secret();
    if ($secret === '' || !hash_equals($secret, $unlock)) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="tr"><meta charset="utf-8"><body style="font-family:sans-serif;padding:24px">';
        echo '<h1>Kurulum kilitli</h1><p>Sistem zaten kurulu. Yeniden kurulum için ops anahtarı gerekir.</p>';
        echo '</body></html>';
        exit;
    }
}

if (!function_exists('run_sql_file')) {
    function run_sql_file(PDO $pdo, string $path): void
    {
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException('SQL dosyası okunamadı: ' . $path);
        }
        $pdo->exec($sql);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim((string) ($_POST['db_host'] ?? 'localhost'));
    $port = (int) ($_POST['db_port'] ?? 3306);
    $name = trim((string) ($_POST['db_name'] ?? 'gonulkop_chicken'));
    $user = trim((string) ($_POST['db_user'] ?? ''));
    $pass = (string) ($_POST['db_pass'] ?? '');
    $appUrl = rtrim(trim((string) ($_POST['app_url'] ?? 'https://gonulkoprusu.com/chicken')), '/');

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

        $staffCount = (int) $pdo->query('SELECT COUNT(*) FROM staff')->fetchColumn();
        $defaultPasswords = [];
        if ($staffCount === 0) {
            $defaults = [
                ['Yönetici', 'admin', 'admin', '0000'],
                ['Kasa', 'kasa', 'cashier', '1111'],
                ['Garson Ayşe', 'garson1', 'waiter', '2222'],
                ['Garson Mehmet', 'garson2', 'waiter', '3333'],
            ];
            $insertStaff = $pdo->prepare(
                'INSERT INTO staff (name, username, password_hash, role, pin, is_active)
                 VALUES (?, ?, ?, ?, ?, 1)'
            );
            foreach ($defaults as $row) {
                $plain = bin2hex(random_bytes(4)) . 'Aa1!';
                $defaultPasswords[] = [$row[1], $plain, $row[2]];
                $insertStaff->execute([
                    $row[0],
                    $row[1],
                    password_hash($plain, PASSWORD_DEFAULT),
                    $row[2],
                    $row[3],
                ]);
            }
            $messages[] = 'Personel hesapları oluşturuldu (parolalar aşağıda — bir kez gösterilir).';
        } else {
            $messages[] = 'Personel zaten mevcut; parolalar değiştirilmedi.';
        }

        $opsSecret = bin2hex(random_bytes(24));
        $local = "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n"
            . "    'app_url' => " . var_export($appUrl, true) . ",\n"
            . "    'ops_secret' => " . var_export($opsSecret, true) . ",\n"
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
  <title>Crisp &amp; Co. Kurulum</title>
  <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>">
</head>
<body class="auth-body">
  <main class="auth-card">
    <p class="eyebrow">Crisp &amp; Co.</p>
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
        <?php if (!empty($defaultPasswords)): ?>
          <p><strong>Giriş bilgileri (bir kez gösterilir)</strong></p>
          <ul>
            <?php foreach ($defaultPasswords as $cred): ?>
              <li><?= e($cred[2]) ?>: <code><?= e($cred[0]) ?></code> / <code><?= e($cred[1]) ?></code></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <p class="muted small">Ops anahtarı <code>config.local.php</code> içinde <code>ops_secret</code> olarak kaydedildi (cron/tools için).</p>
        <a class="btn btn-primary" href="<?= e(url('/')) ?>">Siteye git</a>
        <a class="btn btn-ghost" href="<?= e(url('/giris')) ?>">Personel girişi</a>
        <p class="muted small">Güvenlik için kurulumdan sonra <code>install.php</code> dosyasını silin.</p>
      </div>
    <?php else: ?>
      <form method="post" class="stack">
        <?php
          $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
          $guessUrl = isset($_SERVER['HTTP_HOST'])
            ? ($scheme . '://' . $_SERVER['HTTP_HOST'] . base_path())
            : 'https://gonulkoprusu.com/chicken';
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

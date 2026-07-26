<?php

declare(strict_types=1);

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = (string) config('db.host', 'localhost');
        $port = (int) config('db.port', 3306);
        $name = (string) config('db.name', '');
        $user = (string) config('db.user', '');
        $pass = (string) config('db.pass', '');
        $charset = (string) config('db.charset', 'utf8mb4');

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $name, $charset);
        self::$pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$pdo;
    }

    public static function isInstalled(): bool
    {
        try {
            $pdo = self::pdo();
            $pdo->query('SELECT 1 FROM staff LIMIT 1');
            return true;
        } catch (Throwable) {
            return false;
        }
    }
}

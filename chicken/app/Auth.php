<?php

declare(strict_types=1);

final class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['staff'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function role(): ?string
    {
        $user = self::user();
        return $user['role'] ?? null;
    }

    public static function attempt(string $username, string $password): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM staff WHERE username = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([trim($username)]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        unset($user['password_hash']);
        session_regenerate_id(true);
        $_SESSION['staff'] = $user;
        if (class_exists('OpsService')) {
            OpsService::logStaffLogin((int) $user['id'], (string) $user['username'], (string) $user['role']);
            try {
                OpsService::openShift((int) $user['id']);
            } catch (Throwable) {
            }
        }
        return true;
    }

    public static function logout(): void
    {
        $user = self::user();
        if ($user && class_exists('OpsService')) {
            $staffId = (int) ($user['id'] ?? 0);
            try {
                if ($staffId > 0) {
                    OpsService::closeShift($staffId);
                }
            } catch (Throwable) {
            }
            OpsService::logStaffLogout(
                $staffId,
                (string) ($user['username'] ?? ''),
                (string) ($user['role'] ?? '')
            );
        }
        unset($_SESSION['staff']);
    }

    public static function requireRole(string ...$roles): void
    {
        if (!self::check() || !in_array(self::role(), $roles, true)) {
            $path = current_path();
            if (str_starts_with($path, '/api/')) {
                json_response(['ok' => false, 'error' => 'Yetkisiz'], 401);
            }
            flash('error', 'Bu alan için yetkiniz yok.');
            redirect('/giris');
        }
    }
}

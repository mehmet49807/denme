<?php

declare(strict_types=1);

final class CustomerAuth
{
    public static function user(): ?array
    {
        return $_SESSION['customer'] ?? null;
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

    public static function attempt(string $login, string $password): bool
    {
        $pdo = Database::pdo();
        $login = trim($login);
        if ($login === '' || $password === '') {
            return false;
        }
        $stmt = $pdo->prepare(
            'SELECT * FROM customers
             WHERE is_active = 1 AND (email = ? OR phone = ?)
             LIMIT 1'
        );
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        unset($user['password_hash']);
        session_regenerate_id(true);
        $_SESSION['customer'] = $user;
        return true;
    }

    public static function register(
        string $name,
        string $email,
        string $phone,
        string $password,
        string $address = ''
    ): array {
        $name = trim($name);
        $email = strtolower(trim($email));
        $phone = preg_replace('/\s+/', '', trim($phone)) ?? '';
        $address = trim($address);
        if ($name === '' || $email === '' || $phone === '' || strlen($password) < 6) {
            throw new InvalidArgumentException('Ad, e-posta, telefon ve en az 6 karakter parola gerekli.');
        }
        if ($address === '') {
            throw new InvalidArgumentException('Adres gerekli.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Geçerli bir e-posta girin.');
        }

        $pdo = Database::pdo();
        $check = $pdo->prepare('SELECT id FROM customers WHERE email = ? OR phone = ? LIMIT 1');
        $check->execute([$email, $phone]);
        if ($check->fetch()) {
            throw new InvalidArgumentException('Bu e-posta veya telefon zaten kayıtlı.');
        }

        $stmt = $pdo->prepare(
            'INSERT INTO customers (name, email, phone, address, password_hash, is_active, welcome_discount_used)
             VALUES (?, ?, ?, ?, ?, 1, 0)'
        );
        $stmt->execute([$name, $email, $phone, $address, password_hash($password, PASSWORD_DEFAULT)]);
        $id = (int) $pdo->lastInsertId();
        $user = [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'is_active' => 1,
            'welcome_discount_used' => 0,
        ];
        session_regenerate_id(true);
        $_SESSION['customer'] = $user;
        return $user;
    }

    public static function refresh(): void
    {
        $id = self::id();
        if (!$id) {
            return;
        }
        $stmt = Database::pdo()->prepare(
            'SELECT id, name, email, phone, address, is_active, welcome_discount_used, created_at
             FROM customers WHERE id = ? AND is_active = 1 LIMIT 1'
        );
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) {
            self::logout();
            return;
        }
        $_SESSION['customer'] = $user;
    }

    public static function logout(): void
    {
        unset($_SESSION['customer']);
    }

    public static function markWelcomeUsed(int $customerId): void
    {
        Database::pdo()
            ->prepare('UPDATE customers SET welcome_discount_used = 1, updated_at = NOW() WHERE id = ?')
            ->execute([$customerId]);
        if (self::id() === $customerId && isset($_SESSION['customer'])) {
            $_SESSION['customer']['welcome_discount_used'] = 1;
        }
    }
}

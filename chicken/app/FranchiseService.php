<?php

declare(strict_types=1);

final class FranchiseService
{
    public const STATUSES = ['new', 'reviewing', 'approved', 'rejected'];

    public static function create(array $data): int
    {
        $name = trim((string) ($data['name'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $city = trim((string) ($data['city'] ?? ''));
        $district = trim((string) ($data['district'] ?? ''));
        $budget = trim((string) ($data['budget'] ?? ''));
        $experience = trim((string) ($data['experience'] ?? ''));
        $message = trim((string) ($data['message'] ?? ''));

        if ($name === '' || mb_strlen($name) < 3) {
            throw new InvalidArgumentException('Ad soyad en az 3 karakter olmalı.');
        }
        if ($phone === '' || mb_strlen($phone) < 10) {
            throw new InvalidArgumentException('Geçerli bir telefon girin.');
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Geçerli bir e-posta girin.');
        }
        if ($city === '') {
            throw new InvalidArgumentException('Şehir zorunludur.');
        }
        if (empty($data['accept_terms'])) {
            throw new InvalidArgumentException('Franchise şartlarını kabul etmelisiniz.');
        }
        if (empty($data['accept_kvkk'])) {
            throw new InvalidArgumentException('KVKK bilgilendirmesini onaylamalısınız.');
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO franchise_applications
              (full_name, phone, email, city, district, budget_range, experience, message, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $name,
            $phone,
            mb_strtolower($email),
            $city,
            $district !== '' ? $district : null,
            $budget !== '' ? $budget : null,
            $experience !== '' ? $experience : null,
            $message !== '' ? $message : null,
            'new',
        ]);

        return (int) $pdo->lastInsertId();
    }

    /** @return list<array<string,mixed>> */
    public static function list(?string $status = null, int $limit = 200): array
    {
        $limit = max(1, min(500, $limit));
        $pdo = Database::pdo();
        if ($status !== null && $status !== '' && in_array($status, self::STATUSES, true)) {
            $stmt = $pdo->prepare(
                'SELECT * FROM franchise_applications WHERE status = ? ORDER BY created_at DESC LIMIT ' . $limit
            );
            $stmt->execute([$status]);
            return $stmt->fetchAll();
        }
        return $pdo->query(
            'SELECT * FROM franchise_applications ORDER BY
              FIELD(status, \'new\', \'reviewing\', \'approved\', \'rejected\'),
              created_at DESC
             LIMIT ' . $limit
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM franchise_applications WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updateStatus(int $id, string $status, ?string $adminNote = null): void
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Geçersiz durum.');
        }
        $note = $adminNote !== null ? trim($adminNote) : null;
        $stmt = Database::pdo()->prepare(
            'UPDATE franchise_applications
             SET status = ?, admin_note = ?, reviewed_at = CURRENT_TIMESTAMP
             WHERE id = ?'
        );
        $stmt->execute([$status, $note !== '' ? $note : null, $id]);
        if ($stmt->rowCount() < 1) {
            throw new RuntimeException('Başvuru bulunamadı.');
        }
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'new' => 'Yeni',
            'reviewing' => 'İnceleniyor',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
            default => $status,
        };
    }

    public static function countsByStatus(): array
    {
        $rows = Database::pdo()
            ->query('SELECT status, COUNT(*) AS c FROM franchise_applications GROUP BY status')
            ->fetchAll();
        $out = ['new' => 0, 'reviewing' => 0, 'approved' => 0, 'rejected' => 0, 'all' => 0];
        foreach ($rows as $row) {
            $key = (string) $row['status'];
            $c = (int) $row['c'];
            if (isset($out[$key])) {
                $out[$key] = $c;
            }
            $out['all'] += $c;
        }
        return $out;
    }
}

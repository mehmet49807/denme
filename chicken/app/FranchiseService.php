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
        $branchId = isset($data['preferred_branch_id']) ? (int) $data['preferred_branch_id'] : 0;
        if ($branchId < 1) {
            $branchId = 0;
        }

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
        if ($branchId > 0 && class_exists('BranchService') && !BranchService::find($branchId)) {
            throw new InvalidArgumentException('Seçilen şube bulunamadı.');
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO franchise_applications
              (full_name, phone, email, city, district, preferred_branch_id, budget_range, experience, message, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $name,
            $phone,
            mb_strtolower($email),
            $city,
            $district !== '' ? $district : null,
            $branchId > 0 ? $branchId : null,
            $budget !== '' ? $budget : null,
            $experience !== '' ? $experience : null,
            $message !== '' ? $message : null,
            'new',
        ]);

        return (int) $pdo->lastInsertId();
    }

    /** @return list<array<string,mixed>> */
    public static function list(?string $status = null, int $limit = 200, ?int $branchId = null): array
    {
        $limit = max(1, min(500, $limit));
        $pdo = Database::pdo();
        $where = ['1=1'];
        $params = [];
        if ($status !== null && $status !== '' && in_array($status, self::STATUSES, true)) {
            $where[] = 'f.status = ?';
            $params[] = $status;
        }
        if ($branchId !== null && $branchId > 0) {
            $where[] = 'f.preferred_branch_id = ?';
            $params[] = $branchId;
        }
        $sql = 'SELECT f.*, b.name AS branch_name, b.city AS branch_city
                FROM franchise_applications f
                LEFT JOIN branches b ON b.id = f.preferred_branch_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY FIELD(f.status, \'new\', \'reviewing\', \'approved\', \'rejected\'),
                         f.created_at DESC
                LIMIT ' . $limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
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

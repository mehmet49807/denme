<?php

declare(strict_types=1);

final class BranchService
{
    /** @return list<array<string,mixed>> */
    public static function listAll(): array
    {
        return Database::pdo()
            ->query('SELECT * FROM branches ORDER BY sort_order ASC, id ASC')
            ->fetchAll() ?: [];
    }

    /** @return list<array<string,mixed>> */
    public static function listActive(): array
    {
        return Database::pdo()
            ->query('SELECT * FROM branches WHERE is_active = 1 ORDER BY sort_order ASC, id ASC')
            ->fetchAll() ?: [];
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM branches WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $row = self::normalize($data);
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO branches (name, city, phone, whatsapp, address, is_active, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $row['name'],
            $row['city'],
            $row['phone'],
            $row['whatsapp'],
            $row['address'],
            $row['is_active'],
            $row['sort_order'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        if (!self::find($id)) {
            throw new RuntimeException('Şube bulunamadı.');
        }
        $row = self::normalize($data);
        $stmt = Database::pdo()->prepare(
            'UPDATE branches
             SET name = ?, city = ?, phone = ?, whatsapp = ?, address = ?, is_active = ?, sort_order = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $row['name'],
            $row['city'],
            $row['phone'],
            $row['whatsapp'],
            $row['address'],
            $row['is_active'],
            $row['sort_order'],
            $id,
        ]);
    }

    public static function setActive(int $id, bool $active): void
    {
        $stmt = Database::pdo()->prepare('UPDATE branches SET is_active = ? WHERE id = ?');
        $stmt->execute([$active ? 1 : 0, $id]);
        if ($stmt->rowCount() < 1 && !self::find($id)) {
            throw new RuntimeException('Şube bulunamadı.');
        }
    }

    /** @return array{name:string,city:string,phone:?string,whatsapp:?string,address:?string,is_active:int,sort_order:int} */
    private static function normalize(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $city = trim((string) ($data['city'] ?? ''));
        if ($name === '' || mb_strlen($name) < 2) {
            throw new InvalidArgumentException('Şube adı en az 2 karakter olmalı.');
        }
        if ($city === '') {
            throw new InvalidArgumentException('Şehir zorunludur.');
        }
        $phone = trim((string) ($data['phone'] ?? ''));
        $whatsapp = trim((string) ($data['whatsapp'] ?? ''));
        $address = trim((string) ($data['address'] ?? ''));
        return [
            'name' => $name,
            'city' => $city,
            'phone' => $phone !== '' ? $phone : null,
            'whatsapp' => $whatsapp !== '' ? $whatsapp : null,
            'address' => $address !== '' ? $address : null,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }

    public static function ensureSeed(): void
    {
        $pdo = Database::pdo();
        $count = (int) $pdo->query('SELECT COUNT(*) FROM branches')->fetchColumn();
        if ($count > 0) {
            return;
        }
        self::create([
            'name' => 'Antalya Merkez',
            'city' => 'Antalya',
            'phone' => '',
            'whatsapp' => '',
            'address' => 'Antalya',
            'is_active' => 1,
            'sort_order' => 1,
        ]);
    }
}

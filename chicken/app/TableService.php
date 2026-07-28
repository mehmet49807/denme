<?php

declare(strict_types=1);

final class TableService
{
    /**
     * @return array{id:int,code:string,label:string,seats:int}
     */
    public static function create(string $masaNo, int $seats, ?int $openedByStaffId, string $openedByName): array
    {
        $masaNo = trim($masaNo);
        $openedByName = trim($openedByName);
        $seats = max(1, min(50, $seats));

        if ($masaNo === '') {
            throw new InvalidArgumentException('Masa no gerekli.');
        }
        if ($openedByName === '') {
            throw new InvalidArgumentException('Masa açan kişi gerekli.');
        }

        // Accept "12", "M12", "Masa 12"
        $normalized = preg_replace('/\s+/', ' ', $masaNo) ?? $masaNo;
        if (preg_match('/^(?:masa\s*)?([a-z]?)(\d+)$/iu', $normalized, $m)) {
            $num = ltrim($m[2], '0');
            if ($num === '') {
                $num = '0';
            }
            $code = 'M' . $num;
            $label = 'Masa ' . $num;
        } else {
            $code = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', $normalized) ?? '');
            if ($code === '') {
                throw new InvalidArgumentException('Masa no geçersiz.');
            }
            $label = 'Masa ' . $masaNo;
        }

        $pdo = Database::pdo();
        $exists = $pdo->prepare('SELECT id FROM dining_tables WHERE code = ? LIMIT 1');
        $exists->execute([$code]);
        if ($exists->fetch()) {
            throw new RuntimeException('Bu masa no zaten kayıtlı: ' . $label);
        }

        $token = bin2hex(random_bytes(16));
        $stmt = $pdo->prepare(
            'INSERT INTO dining_tables (code, label, seats, is_active, qr_token, opened_by_staff_id, opened_by_name)
             VALUES (?, ?, ?, 1, ?, ?, ?)'
        );
        $stmt->execute([
            $code,
            $label,
            $seats,
            $token,
            $openedByStaffId,
            $openedByName,
        ]);

        return [
            'id' => (int) $pdo->lastInsertId(),
            'code' => $code,
            'label' => $label,
            'seats' => $seats,
        ];
    }

    /** @return list<array{id:int,name:string,role:string}> */
    public static function staffOptions(): array
    {
        $rows = Database::pdo()
            ->query(
                "SELECT id, name, role FROM staff
                 WHERE is_active = 1 AND role IN ('waiter','cashier','admin')
                 ORDER BY FIELD(role,'waiter','cashier','admin'), name"
            )
            ->fetchAll();

        return array_map(static fn(array $r): array => [
            'id' => (int) $r['id'],
            'name' => (string) $r['name'],
            'role' => (string) $r['role'],
        ], $rows);
    }
}

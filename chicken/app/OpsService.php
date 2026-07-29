<?php

declare(strict_types=1);

/**
 * Operasyon yardımcıları: fiş geçmişi, stok, vardiya, istatistik ekleri.
 */
final class OpsService
{
    public static function waitAlertMinutes(): int
    {
        $n = (int) BrochureService::getSetting('station_wait_alert_minutes', '15');
        return max(5, min(120, $n > 0 ? $n : 15));
    }

    public static function etaMinutes(): int
    {
        $n = (int) BrochureService::getSetting('online_eta_minutes', '35');
        return max(10, min(180, $n > 0 ? $n : 35));
    }

    public static function minOnlineTotal(): float
    {
        return max(0, (float) BrochureService::getSetting('online_min_total', '0'));
    }

    public static function deliveryZones(): array
    {
        $raw = (string) BrochureService::getSetting('delivery_zones_json', '[]');
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }
        $out = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $out[] = [
                'name' => $name,
                'min_total' => max(0, (float) ($row['min_total'] ?? 0)),
                'fee' => max(0, (float) ($row['fee'] ?? 0)),
            ];
        }
        return $out;
    }

    public static function qzConfig(): array
    {
        return [
            'enabled' => BrochureService::getSetting('qz_enabled', '0') === '1',
            'printer_kitchen' => (string) BrochureService::getSetting('qz_printer_kitchen', ''),
            'printer_bar' => (string) BrochureService::getSetting('qz_printer_bar', ''),
        ];
    }

    /** @return list<array<string,mixed>> */
    public static function slipHistory(string $station, int $limit = 30): array
    {
        if (!in_array($station, ['kitchen', 'bar'], true)) {
            throw new InvalidArgumentException('Geçersiz istasyon.');
        }
        $limit = max(5, min(100, $limit));
        $sentCol = $station === 'bar' ? 'bar_slip_sent_at' : 'kitchen_slip_sent_at';
        $ackCol = $station === 'bar' ? 'bar_slip_acked_at' : 'kitchen_slip_acked_at';
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            "SELECT o.id, o.order_code, o.source, o.status, o.customer_name, o.created_at,
                    o.{$sentCol} AS slip_sent_at, o.{$ackCol} AS slip_acked_at,
                    t.label AS table_label, s.name AS waiter_name
             FROM orders o
             LEFT JOIN dining_tables t ON t.id = o.table_id
             LEFT JOIN staff s ON s.id = o.waiter_id
             WHERE o.{$sentCol} IS NOT NULL
             ORDER BY o.{$sentCol} DESC
             LIMIT {$limit}"
        );
        $stmt->execute();
        $rows = $stmt->fetchAll() ?: [];
        foreach ($rows as &$row) {
            $row['fis_url'] = station_slip_url((int) $row['id'], [
                'station' => $station,
                'back' => $station === 'bar' ? '/bar' : '/mutfak',
            ]);
            $row['reprint_url'] = station_slip_url((int) $row['id'], [
                'station' => $station,
                'autoprint' => true,
                'back' => $station === 'bar' ? '/bar/fisler' : '/mutfak/fisler',
            ]);
            $row['source_label'] = source_label((string) ($row['source'] ?? ''));
            $row['status_label'] = status_label((string) ($row['status'] ?? ''));
        }
        unset($row);
        return $rows;
    }

    /** @return list<array<string,mixed>> */
    public static function topSellingProducts(string $from, string $to, int $limit = 10): array
    {
        $limit = max(3, min(50, $limit));
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            "SELECT oi.item_name,
                    SUM(oi.quantity) AS qty_sold,
                    COALESCE(SUM(oi.unit_price * oi.quantity), 0) AS sales_total
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             WHERE o.status = 'paid'
               AND oi.status != 'cancelled'
               AND o.paid_at BETWEEN ? AND ?
             GROUP BY oi.item_name
             ORDER BY qty_sold DESC, sales_total DESC
             LIMIT {$limit}"
        );
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll() ?: [];
    }

    /** @return list<array<string,mixed>> */
    public static function stockAlerts(): array
    {
        $pdo = Database::pdo();
        try {
            $stmt = $pdo->query(
                "SELECT id, name, station, stock_qty, stock_alert_qty, is_available
                 FROM menu_items
                 WHERE stock_alert_qty IS NOT NULL
                   AND stock_qty IS NOT NULL
                   AND stock_qty <= stock_alert_qty
                 ORDER BY stock_qty ASC, name ASC
                 LIMIT 100"
            );
            return $stmt ? ($stmt->fetchAll() ?: []) : [];
        } catch (Throwable) {
            return [];
        }
    }

    public static function logStaffLogin(int $staffId, string $username, string $role): void
    {
        try {
            $pdo = Database::pdo();
            $pdo->prepare(
                'INSERT INTO staff_login_logs (staff_id, username, role, event_type, ip_address)
                 VALUES (?, ?, ?, ?, ?)'
            )->execute([
                $staffId,
                $username,
                $role,
                'login',
                substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64),
            ]);
        } catch (Throwable) {
        }
    }

    public static function logStaffLogout(?int $staffId, ?string $username, ?string $role): void
    {
        if (!$staffId) {
            return;
        }
        try {
            $pdo = Database::pdo();
            $pdo->prepare(
                'INSERT INTO staff_login_logs (staff_id, username, role, event_type, ip_address)
                 VALUES (?, ?, ?, ?, ?)'
            )->execute([
                $staffId,
                (string) $username,
                (string) $role,
                'logout',
                substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64),
            ]);
        } catch (Throwable) {
        }
    }

    public static function openShift(int $staffId): array
    {
        $pdo = Database::pdo();
        $open = $pdo->prepare(
            'SELECT * FROM staff_shifts WHERE staff_id = ? AND closed_at IS NULL ORDER BY id DESC LIMIT 1'
        );
        $open->execute([$staffId]);
        $existing = $open->fetch();
        if ($existing) {
            return $existing;
        }
        $pdo->prepare('INSERT INTO staff_shifts (staff_id, opened_at) VALUES (?, NOW())')
            ->execute([$staffId]);
        $id = (int) $pdo->lastInsertId();
        $stmt = $pdo->prepare('SELECT * FROM staff_shifts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: [];
    }

    public static function closeShift(int $staffId): ?array
    {
        $pdo = Database::pdo();
        $open = $pdo->prepare(
            'SELECT * FROM staff_shifts WHERE staff_id = ? AND closed_at IS NULL ORDER BY id DESC LIMIT 1'
        );
        $open->execute([$staffId]);
        $row = $open->fetch();
        if (!$row) {
            return null;
        }
        $pdo->prepare('UPDATE staff_shifts SET closed_at = NOW() WHERE id = ?')
            ->execute([(int) $row['id']]);
        $stmt = $pdo->prepare('SELECT * FROM staff_shifts WHERE id = ? LIMIT 1');
        $stmt->execute([(int) $row['id']]);
        return $stmt->fetch() ?: null;
    }

    public static function currentShift(int $staffId): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT * FROM staff_shifts WHERE staff_id = ? AND closed_at IS NULL ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$staffId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return list<array<string,mixed>> */
    public static function recentLoginLogs(int $limit = 50): array
    {
        $limit = max(10, min(200, $limit));
        $pdo = Database::pdo();
        try {
            $stmt = $pdo->query(
                "SELECT l.*, s.name AS staff_name
                 FROM staff_login_logs l
                 LEFT JOIN staff s ON s.id = l.staff_id
                 ORDER BY l.id DESC
                 LIMIT {$limit}"
            );
            return $stmt ? ($stmt->fetchAll() ?: []) : [];
        } catch (Throwable) {
            return [];
        }
    }

    /** Garson için hazır ürünler (bildirim). */
    public static function readyItemsForWaiter(?int $waiterId, bool $allOpen = false): array
    {
        $pdo = Database::pdo();
        if ($allOpen || !$waiterId) {
            $stmt = $pdo->query(
                "SELECT oi.id, oi.item_name, oi.quantity, oi.station, oi.status,
                        o.id AS order_id, o.order_code, o.waiter_id,
                        t.label AS table_label, oi.ready_at
                 FROM order_items oi
                 JOIN orders o ON o.id = oi.order_id
                 LEFT JOIN dining_tables t ON t.id = o.table_id
                 WHERE oi.status = 'ready'
                   AND o.status NOT IN ('cancelled','paid','pending')
                 ORDER BY oi.id DESC
                 LIMIT 40"
            );
            return $stmt ? ($stmt->fetchAll() ?: []) : [];
        }
        $stmt = $pdo->prepare(
            "SELECT oi.id, oi.item_name, oi.quantity, oi.station, oi.status,
                    o.id AS order_id, o.order_code, o.waiter_id,
                    t.label AS table_label, oi.ready_at
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             LEFT JOIN dining_tables t ON t.id = o.table_id
             WHERE oi.status = 'ready'
               AND o.waiter_id = ?
               AND o.status NOT IN ('cancelled','paid','pending')
             ORDER BY oi.id DESC
             LIMIT 40"
        );
        $stmt->execute([$waiterId]);
        return $stmt->fetchAll() ?: [];
    }

    public static function posBranchId(): ?int
    {
        $id = (int) BrochureService::getSetting('pos_branch_id', '0');
        return $id > 0 ? $id : null;
    }

    public static function branchPrice(int $menuItemId, ?int $branchId, float $fallback): float
    {
        if (!$branchId) {
            return $fallback;
        }
        try {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare(
                'SELECT price FROM menu_item_branch_prices WHERE menu_item_id = ? AND branch_id = ? LIMIT 1'
            );
            $stmt->execute([$menuItemId, $branchId]);
            $price = $stmt->fetchColumn();
            return $price !== false ? (float) $price : $fallback;
        } catch (Throwable) {
            return $fallback;
        }
    }

    /** @param list<array<string,mixed>> $items */
    public static function applyBranchPricesToItems(array $items, ?int $branchId = null): array
    {
        $branchId = $branchId ?? self::posBranchId();
        if (!$branchId) {
            return $items;
        }
        foreach ($items as &$item) {
            $id = (int) ($item['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $base = (float) ($item['price'] ?? 0);
            $item['base_price'] = $base;
            $item['price'] = self::branchPrice($id, $branchId, $base);
        }
        unset($item);
        return $items;
    }

    /** @return array<int,float> branch_id => price */
    public static function branchPricesForItem(int $menuItemId): array
    {
        try {
            $stmt = Database::pdo()->prepare(
                'SELECT branch_id, price FROM menu_item_branch_prices WHERE menu_item_id = ?'
            );
            $stmt->execute([$menuItemId]);
            $out = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $out[(int) $row['branch_id']] = (float) $row['price'];
            }
            return $out;
        } catch (Throwable) {
            return [];
        }
    }

    /** @param array<int|string,mixed> $pricesByBranch branch_id => price (empty = delete) */
    public static function saveBranchPrices(int $menuItemId, array $pricesByBranch): void
    {
        $pdo = Database::pdo();
        $del = $pdo->prepare(
            'DELETE FROM menu_item_branch_prices WHERE menu_item_id = ? AND branch_id = ?'
        );
        $upsert = $pdo->prepare(
            'INSERT INTO menu_item_branch_prices (menu_item_id, branch_id, price)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE price = VALUES(price)'
        );
        foreach ($pricesByBranch as $branchId => $raw) {
            $bid = (int) $branchId;
            if ($bid <= 0) {
                continue;
            }
            $raw = trim((string) $raw);
            if ($raw === '') {
                $del->execute([$menuItemId, $bid]);
                continue;
            }
            $price = max(0, (float) str_replace(',', '.', $raw));
            $upsert->execute([$menuItemId, $bid, $price]);
        }
    }
}

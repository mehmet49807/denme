<?php

declare(strict_types=1);

final class OrderService
{
    public static function create(array $payload): array
    {
        $pdo = Database::pdo();
        $items = $payload['items'] ?? [];
        if (!is_array($items) || $items === []) {
            throw new InvalidArgumentException('Sipariş boş olamaz.');
        }

        $source = $payload['source'] ?? 'online';
        if (!in_array($source, ['online', 'waiter', 'cashier'], true)) {
            throw new InvalidArgumentException('Geçersiz sipariş kaynağı.');
        }

        $normalized = self::normalizeItems($pdo, $items);
        $subtotal = array_sum(array_map(
            static fn(array $i): float => $i['unit_price'] * $i['quantity'],
            $normalized
        ));

        $tableId = isset($payload['table_id']) ? (int) $payload['table_id'] : null;
        if ($tableId === 0) {
            $tableId = null;
        }
        if (in_array($source, ['waiter', 'cashier'], true) && !$tableId) {
            throw new InvalidArgumentException('Masa siparişi için masa seçilmeli.');
        }

        $waiterId = isset($payload['waiter_id']) ? (int) $payload['waiter_id'] : null;
        if ($waiterId === 0) {
            $waiterId = null;
        }

        $orderCode = generate_order_code($pdo);
        $status = $source === 'online' ? 'pending' : 'accepted';

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO orders
                (order_code, source, status, table_id, waiter_id, customer_name, customer_phone, customer_note, subtotal, total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $orderCode,
                $source,
                $status,
                $tableId,
                $waiterId,
                $payload['customer_name'] ?? null,
                $payload['customer_phone'] ?? null,
                $payload['customer_note'] ?? null,
                $subtotal,
                $subtotal,
            ]);
            $orderId = (int) $pdo->lastInsertId();

            self::insertItems($pdo, $orderId, $normalized);

            $label = match ($source) {
                'online' => 'Online sipariş oluşturuldu',
                'cashier' => 'Kasa siparişi oluşturuldu',
                default => 'Garson siparişi oluşturuldu',
            };
            self::addEvent($pdo, $orderId, $waiterId, 'created', $label);

            if ($source !== 'online') {
                self::addEvent($pdo, $orderId, $waiterId, 'sent_kitchen', 'Mutfak fişi gönderildi');
                self::addEvent($pdo, $orderId, $waiterId, 'sent_bar', 'Bar fişi gönderildi');
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return self::findById($orderId);
    }

    public static function addItems(int $orderId, array $items, ?int $staffId = null): array
    {
        $pdo = Database::pdo();
        $order = self::findById($orderId);
        if (!$order) {
            throw new InvalidArgumentException('Sipariş bulunamadı.');
        }
        if (in_array($order['status'], ['paid', 'cancelled'], true)) {
            throw new InvalidArgumentException('Kapalı veya iptal siparişe ürün eklenemez.');
        }

        $normalized = self::normalizeItems($pdo, $items);
        $pdo->beginTransaction();
        try {
            self::insertItems($pdo, $orderId, $normalized);
            self::recalcTotals($pdo, $orderId);
            $count = array_sum(array_map(static fn(array $i): int => $i['quantity'], $normalized));
            self::addEvent($pdo, $orderId, $staffId, 'items_added', $count . ' ürün eklendi');
            self::addEvent($pdo, $orderId, $staffId, 'sent_kitchen', 'Mutfak fişi güncellendi');
            self::addEvent($pdo, $orderId, $staffId, 'sent_bar', 'Bar fişi güncellendi');
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return self::findById($orderId);
    }

    public static function cancelOrder(int $orderId, ?int $staffId = null): void
    {
        $order = self::findById($orderId);
        if (!$order) {
            throw new InvalidArgumentException('Sipariş bulunamadı.');
        }
        if ($order['status'] === 'paid') {
            throw new InvalidArgumentException('Ödenmiş sipariş iptal edilemez.');
        }
        if ($order['status'] === 'cancelled') {
            return;
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?')
                ->execute(['cancelled', $orderId]);
            $pdo->prepare("UPDATE order_items SET status = 'cancelled' WHERE order_id = ? AND status != 'cancelled'")
                ->execute([$orderId]);
            self::addEvent($pdo, $orderId, $staffId, 'status_cancelled', 'Sipariş iptal edildi');
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function cancelItem(int $itemId, ?int $staffId = null): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT oi.*, o.status AS order_status, o.id AS oid
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             WHERE oi.id = ? LIMIT 1'
        );
        $stmt->execute([$itemId]);
        $item = $stmt->fetch();
        if (!$item) {
            throw new InvalidArgumentException('Ürün satırı bulunamadı.');
        }
        if (in_array($item['order_status'], ['paid', 'cancelled'], true)) {
            throw new InvalidArgumentException('Kapalı siparişte ürün iptal edilemez.');
        }
        if ($item['status'] === 'cancelled') {
            return;
        }

        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE order_items SET status = 'cancelled' WHERE id = ?")->execute([$itemId]);
            self::recalcTotals($pdo, (int) $item['oid']);

            $active = $pdo->prepare(
                "SELECT COUNT(*) FROM order_items WHERE order_id = ? AND status != 'cancelled'"
            );
            $active->execute([(int) $item['oid']]);
            if ((int) $active->fetchColumn() === 0) {
                $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?")
                    ->execute([(int) $item['oid']]);
                self::addEvent($pdo, (int) $item['oid'], $staffId, 'status_cancelled', 'Tüm ürünler iptal — sipariş iptal');
            } else {
                self::addEvent(
                    $pdo,
                    (int) $item['oid'],
                    $staffId,
                    'item_cancelled',
                    'Ürün iptal: ' . (string) $item['item_name']
                );
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function updateItemNote(int $itemId, string $note, ?int $staffId = null): void
    {
        $note = trim($note);
        if (mb_strlen($note) > 255) {
            throw new InvalidArgumentException('Ürün notu en fazla 255 karakter olabilir.');
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT oi.*, o.status AS order_status, o.id AS oid, o.waiter_id
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             WHERE oi.id = ? LIMIT 1'
        );
        $stmt->execute([$itemId]);
        $item = $stmt->fetch();
        if (!$item) {
            throw new InvalidArgumentException('Ürün satırı bulunamadı.');
        }
        if (in_array($item['order_status'], ['paid', 'cancelled'], true)) {
            throw new InvalidArgumentException('Kapalı siparişte not güncellenemez.');
        }

        $pdo->prepare('UPDATE order_items SET note = ? WHERE id = ?')
            ->execute([$note !== '' ? $note : null, $itemId]);
        self::addEvent(
            $pdo,
            (int) $item['oid'],
            $staffId,
            'item_note',
            ($item['station'] === 'bar' ? 'Bar' : 'Mutfak') . ' notu: ' . (string) $item['item_name']
        );
    }

    public static function payOrder(int $orderId, string $method, ?int $staffId = null): void
    {
        $method = self::normalizePaymentMethod($method);
        $order = self::findById($orderId);
        if (!$order) {
            throw new InvalidArgumentException('Sipariş bulunamadı.');
        }
        if ($order['status'] === 'cancelled') {
            throw new InvalidArgumentException('İptal sipariş tahsil edilemez.');
        }
        if ($order['status'] === 'paid') {
            return;
        }

        $pdo = Database::pdo();
        $pdo->prepare(
            'UPDATE orders SET status = ?, payment_method = ?, paid_at = COALESCE(paid_at, NOW()), updated_at = NOW() WHERE id = ?'
        )->execute(['paid', $method, $orderId]);
        self::addEvent(
            $pdo,
            $orderId,
            $staffId,
            'paid',
            'Ödeme alındı (' . payment_method_label($method) . ')'
        );
    }

    public static function closeTable(int $tableId, string $method, ?int $staffId = null): void
    {
        $method = self::normalizePaymentMethod($method);
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            "SELECT id FROM orders
             WHERE table_id = ?
               AND status NOT IN ('paid','cancelled')
             ORDER BY id ASC"
        );
        $stmt->execute([$tableId]);
        $ids = array_map('intval', array_column($stmt->fetchAll(), 'id'));
        if ($ids === []) {
            throw new InvalidArgumentException('Bu masada kapatılacak açık sipariş yok.');
        }

        foreach ($ids as $orderId) {
            self::payOrder($orderId, $method, $staffId);
        }
        self::addEvent(
            $pdo,
            $ids[0],
            $staffId,
            'table_closed',
            'Masa kapatıldı (' . payment_method_label($method) . ')'
        );
    }

    public static function tablesOverview(): array
    {
        $pdo = Database::pdo();
        $tables = $pdo->query(
            'SELECT * FROM dining_tables WHERE is_active = 1 ORDER BY id'
        )->fetchAll();

        $openStmt = $pdo->query(
            "SELECT o.*, s.name AS waiter_name
             FROM orders o
             LEFT JOIN staff s ON s.id = o.waiter_id
             WHERE o.table_id IS NOT NULL
               AND o.status NOT IN ('paid','cancelled')
             ORDER BY o.id ASC"
        );
        $openOrders = $openStmt->fetchAll();
        $byTable = [];
        foreach ($openOrders as $order) {
            $tid = (int) $order['table_id'];
            $byTable[$tid][] = $order;
        }

        foreach ($tables as &$table) {
            $tid = (int) $table['id'];
            $orders = $byTable[$tid] ?? [];
            $table['open_orders'] = $orders;
            $table['is_open'] = $orders !== [];
            $table['open_count'] = count($orders);
            $table['open_total'] = array_sum(array_map(
                static fn(array $o): float => (float) $o['total'],
                $orders
            ));
            $names = [];
            foreach ($orders as $o) {
                if (!empty($o['waiter_name'])) {
                    $names[$o['waiter_name']] = true;
                }
            }
            $table['waiter_names'] = array_keys($names);
        }
        unset($table);

        return $tables;
    }

    public static function openOrdersForTable(int $tableId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            "SELECT o.*, t.label AS table_label, t.code AS table_code, s.name AS waiter_name
             FROM orders o
             LEFT JOIN dining_tables t ON t.id = o.table_id
             LEFT JOIN staff s ON s.id = o.waiter_id
             WHERE o.table_id = ?
               AND o.status NOT IN ('paid','cancelled')
             ORDER BY o.id ASC"
        );
        $stmt->execute([$tableId]);
        $rows = $stmt->fetchAll();
        return array_map(static fn(array $row): array => self::hydrate($row), $rows);
    }

    public static function findTable(int $tableId): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM dining_tables WHERE id = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$tableId]);
        $table = $stmt->fetch();
        return $table ?: null;
    }

    public static function addEvent(PDO $pdo, int $orderId, ?int $staffId, string $type, string $message): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO order_events (order_id, staff_id, event_type, message) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$orderId, $staffId, $type, $message]);
    }

    public static function findByCode(string $code): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT o.*, t.label AS table_label, t.code AS table_code, s.name AS waiter_name
             FROM orders o
             LEFT JOIN dining_tables t ON t.id = o.table_id
             LEFT JOIN staff s ON s.id = o.waiter_id
             WHERE o.order_code = ? LIMIT 1'
        );
        $stmt->execute([trim($code)]);
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }
        return self::hydrate($order);
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT o.*, t.label AS table_label, t.code AS table_code, s.name AS waiter_name
             FROM orders o
             LEFT JOIN dining_tables t ON t.id = o.table_id
             LEFT JOIN staff s ON s.id = o.waiter_id
             WHERE o.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }
        return self::hydrate($order);
    }

    private static function hydrate(array $order): array
    {
        $pdo = Database::pdo();
        $items = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY station, id');
        $items->execute([(int) $order['id']]);
        $order['items'] = $items->fetchAll();

        $events = $pdo->prepare(
            'SELECT e.*, s.name AS staff_name
             FROM order_events e
             LEFT JOIN staff s ON s.id = e.staff_id
             WHERE e.order_id = ?
             ORDER BY e.id ASC'
        );
        $events->execute([(int) $order['id']]);
        $order['events'] = $events->fetchAll();

        $order['kitchen_items'] = array_values(array_filter(
            $order['items'],
            static fn(array $i): bool => $i['station'] === 'kitchen' && $i['status'] !== 'cancelled'
        ));
        $order['bar_items'] = array_values(array_filter(
            $order['items'],
            static fn(array $i): bool => $i['station'] === 'bar' && $i['status'] !== 'cancelled'
        ));
        $order['active_items'] = array_values(array_filter(
            $order['items'],
            static fn(array $i): bool => $i['status'] !== 'cancelled'
        ));

        return $order;
    }

    public static function updateStatus(int $orderId, string $status, ?int $staffId = null): void
    {
        $allowed = ['pending', 'accepted', 'preparing', 'ready', 'served', 'paid', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            throw new InvalidArgumentException('Geçersiz durum.');
        }
        if ($status === 'paid') {
            throw new InvalidArgumentException('Ödeme için Nakit veya Kart seçin.');
        }
        if ($status === 'cancelled') {
            self::cancelOrder($orderId, $staffId);
            return;
        }
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$status, $orderId]);
        self::addEvent($pdo, $orderId, $staffId, 'status_' . $status, 'Durum: ' . status_label($status));
    }

    public static function updateNote(int $orderId, string $note, ?int $staffId = null): void
    {
        $note = trim($note);
        if (mb_strlen($note) > 400) {
            throw new InvalidArgumentException('Not en fazla 400 karakter olabilir.');
        }
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('UPDATE orders SET customer_note = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$note !== '' ? $note : null, $orderId]);
        self::addEvent(
            $pdo,
            $orderId,
            $staffId,
            'note_updated',
            $note !== '' ? 'Sipariş notu güncellendi' : 'Sipariş notu temizlendi'
        );
    }

    public static function listRecent(array $filters = [], int $limit = 100): array
    {
        $pdo = Database::pdo();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['source'])) {
            $where[] = 'o.source = ?';
            $params[] = $filters['source'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'o.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['waiter_id'])) {
            $where[] = 'o.waiter_id = ?';
            $params[] = (int) $filters['waiter_id'];
        }
        if (!empty($filters['table_id'])) {
            $where[] = 'o.table_id = ?';
            $params[] = (int) $filters['table_id'];
        }
        if (!empty($filters['from'])) {
            $where[] = 'o.created_at >= ?';
            $params[] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $where[] = 'o.created_at <= ?';
            $params[] = $filters['to'];
        }
        if (!empty($filters['open_only'])) {
            $where[] = "o.status NOT IN ('paid','cancelled')";
        }

        $sql = 'SELECT o.*, t.label AS table_label, s.name AS waiter_name
                FROM orders o
                LEFT JOIN dining_tables t ON t.id = o.table_id
                LEFT JOIN staff s ON s.id = o.waiter_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY o.id DESC
                LIMIT ' . (int) $limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private static function normalizeItems(PDO $pdo, array $items): array
    {
        $normalized = [];
        foreach ($items as $raw) {
            $menuId = (int) ($raw['menu_item_id'] ?? 0);
            $qty = max(1, (int) ($raw['quantity'] ?? 1));
            $note = trim((string) ($raw['note'] ?? ''));
            if ($menuId <= 0) {
                continue;
            }
            $stmt = $pdo->prepare('SELECT * FROM menu_items WHERE id = ? AND is_available = 1 LIMIT 1');
            $stmt->execute([$menuId]);
            $menu = $stmt->fetch();
            if (!$menu) {
                throw new InvalidArgumentException('Menü ürünü bulunamadı veya satışta değil.');
            }
            $normalized[] = [
                'menu_item_id' => (int) $menu['id'],
                'item_name' => (string) $menu['name'],
                'station' => (string) $menu['station'],
                'unit_price' => (float) $menu['price'],
                'quantity' => $qty,
                'note' => $note !== '' ? $note : null,
            ];
        }
        if ($normalized === []) {
            throw new InvalidArgumentException('Geçerli ürün seçilmedi.');
        }
        return $normalized;
    }

    private static function insertItems(PDO $pdo, int $orderId, array $normalized): void
    {
        $itemStmt = $pdo->prepare(
            'INSERT INTO order_items
            (order_id, menu_item_id, item_name, station, unit_price, quantity, note, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($normalized as $item) {
            $itemStmt->execute([
                $orderId,
                $item['menu_item_id'],
                $item['item_name'],
                $item['station'],
                $item['unit_price'],
                $item['quantity'],
                $item['note'],
                'queued',
            ]);
        }
    }

    private static function recalcTotals(PDO $pdo, int $orderId): void
    {
        $stmt = $pdo->prepare(
            "SELECT COALESCE(SUM(unit_price * quantity), 0)
             FROM order_items
             WHERE order_id = ? AND status != 'cancelled'"
        );
        $stmt->execute([$orderId]);
        $total = (float) $stmt->fetchColumn();
        $pdo->prepare('UPDATE orders SET subtotal = ?, total = ?, updated_at = NOW() WHERE id = ?')
            ->execute([$total, $total, $orderId]);
    }

    private static function normalizePaymentMethod(string $method): string
    {
        $method = strtolower(trim($method));
        $map = [
            'cash' => 'cash',
            'nakit' => 'cash',
            'card' => 'card',
            'kart' => 'card',
            'kredi' => 'card',
        ];
        if (!isset($map[$method])) {
            throw new InvalidArgumentException('Ödeme yöntemi Nakit veya Kart olmalı.');
        }
        return $map[$method];
    }
}

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
        if (!in_array($source, ['online', 'waiter'], true)) {
            throw new InvalidArgumentException('Geçersiz sipariş kaynağı.');
        }

        $normalized = [];
        $subtotal = 0.0;

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
            $line = (float) $menu['price'] * $qty;
            $subtotal += $line;
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

        $tableId = isset($payload['table_id']) ? (int) $payload['table_id'] : null;
        if ($tableId === 0) {
            $tableId = null;
        }
        if ($source === 'waiter' && !$tableId) {
            throw new InvalidArgumentException('Garson siparişi için masa seçilmeli.');
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

            self::addEvent($pdo, $orderId, $waiterId, 'created', $source === 'online'
                ? 'Online sipariş oluşturuldu'
                : 'Garson siparişi oluşturuldu');

            if ($source === 'waiter') {
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
            static fn(array $i): bool => $i['station'] === 'kitchen'
        ));
        $order['bar_items'] = array_values(array_filter(
            $order['items'],
            static fn(array $i): bool => $i['station'] === 'bar'
        ));

        return $order;
    }

    public static function updateStatus(int $orderId, string $status, ?int $staffId = null): void
    {
        $allowed = ['pending', 'accepted', 'preparing', 'ready', 'served', 'paid', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            throw new InvalidArgumentException('Geçersiz durum.');
        }
        $pdo = Database::pdo();
        $paidAt = $status === 'paid' ? date('Y-m-d H:i:s') : null;
        $stmt = $pdo->prepare('UPDATE orders SET status = ?, paid_at = COALESCE(?, paid_at), updated_at = NOW() WHERE id = ?');
        $stmt->execute([$status, $paidAt, $orderId]);
        self::addEvent($pdo, $orderId, $staffId, 'status_' . $status, 'Durum: ' . status_label($status));
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
        if (!empty($filters['from'])) {
            $where[] = 'o.created_at >= ?';
            $params[] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $where[] = 'o.created_at <= ?';
            $params[] = $filters['to'];
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
}

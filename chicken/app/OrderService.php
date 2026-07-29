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

        $discountCode = null;
        $discountPercent = 0.0;
        $discountAmount = 0.0;
        $customerId = isset($payload['customer_id']) ? (int) $payload['customer_id'] : null;
        if ($customerId === 0) {
            $customerId = null;
        }
        $discountMeta = null;
        if (!empty($payload['discount_code'])) {
            $customer = $payload['customer'] ?? null;
            if (!$customer && $customerId) {
                $cstmt = $pdo->prepare(
                    'SELECT id, name, email, phone, welcome_discount_used FROM customers WHERE id = ? LIMIT 1'
                );
                $cstmt->execute([$customerId]);
                $customer = $cstmt->fetch() ?: null;
            }
            $discountMeta = DiscountService::apply(
                (string) $payload['discount_code'],
                $subtotal,
                is_array($customer) ? $customer : null
            );
            if ($discountMeta) {
                $discountCode = $discountMeta['code'];
                $discountPercent = $discountMeta['percent'];
                $discountAmount = $discountMeta['amount'];
            }
        }
        $total = max(0, round($subtotal - $discountAmount, 2));

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

        $paymentPreference = null;
        if ($source === 'online') {
            $pref = strtolower(trim((string) ($payload['payment_preference'] ?? '')));
            if (!in_array($pref, ['cash', 'card'], true)) {
                throw new InvalidArgumentException('Kapıda ödeme tercihi seçilmeli (nakit veya kart).');
            }
            $paymentPreference = $pref;
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO orders
                (order_code, source, status, table_id, waiter_id, customer_id, customer_name, customer_phone, customer_note,
                 payment_preference, subtotal, discount_code, discount_percent, discount_amount, total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $orderCode,
                $source,
                $status,
                $tableId,
                $waiterId,
                $customerId,
                $payload['customer_name'] ?? null,
                $payload['customer_phone'] ?? null,
                $payload['customer_note'] ?? null,
                $paymentPreference,
                $subtotal,
                $discountCode,
                $discountPercent,
                $discountAmount,
                $total,
            ]);
            $orderId = (int) $pdo->lastInsertId();

            self::insertItems($pdo, $orderId, $normalized);

            $label = match ($source) {
                'online' => 'Online sipariş oluşturuldu',
                'cashier' => 'Kasa siparişi oluşturuldu',
                default => 'Garson siparişi oluşturuldu',
            };
            self::addEvent($pdo, $orderId, $waiterId, 'created', $label);
            if ($paymentPreference) {
                self::addEvent(
                    $pdo,
                    $orderId,
                    $waiterId,
                    'payment_preference',
                    'Kapıda ödeme: ' . payment_preference_label($paymentPreference)
                );
            }
            if ($discountMeta) {
                self::addEvent(
                    $pdo,
                    $orderId,
                    $waiterId,
                    'discount',
                    $discountMeta['label'] . ' (−' . number_format($discountAmount, 2, ',', '.') . ' ₺)'
                );
                if (
                    $discountCode === DiscountService::WELCOME_CODE
                    && $customerId
                ) {
                    CustomerAuth::markWelcomeUsed($customerId);
                }
            }

            if ($source !== 'online') {
                $hasKitchen = false;
                $hasBar = false;
                foreach ($normalized as $row) {
                    if (($row['station'] ?? '') === 'bar') {
                        $hasBar = true;
                    } else {
                        $hasKitchen = true;
                    }
                }
                if ($hasKitchen) {
                    self::addEvent($pdo, $orderId, $waiterId, 'sent_kitchen', 'Mutfak fişi gönderildi');
                }
                if ($hasBar) {
                    self::addEvent($pdo, $orderId, $waiterId, 'sent_bar', 'Bar fişi gönderildi');
                }
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $order = self::findById($orderId);
        if ($order && ($order['source'] ?? '') === 'online' && class_exists('WhatsAppNotify')) {
            try {
                WhatsAppNotify::notifyNewOnlineOrder($order);
            } catch (Throwable $e) {
                error_log('WhatsAppNotify: ' . $e->getMessage());
            }
        }
        return $order;
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
            $newItemIds = self::insertItems($pdo, $orderId, $normalized);
            self::recalcTotals($pdo, $orderId);
            $count = array_sum(array_map(static fn(array $i): int => $i['quantity'], $normalized));
            self::addEvent($pdo, $orderId, $staffId, 'items_added', $count . ' ürün eklendi');
            $hasKitchen = false;
            $hasBar = false;
            foreach ($normalized as $row) {
                if (($row['station'] ?? '') === 'bar') {
                    $hasBar = true;
                } else {
                    $hasKitchen = true;
                }
            }
            if ($hasKitchen) {
                self::addEvent($pdo, $orderId, $staffId, 'sent_kitchen', 'Mutfak fişi güncellendi');
            }
            if ($hasBar) {
                self::addEvent($pdo, $orderId, $staffId, 'sent_bar', 'Bar fişi güncellendi');
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $order = self::findById($orderId);
        if ($order) {
            $order['new_item_ids'] = $newItemIds;
        }
        return $order ?: ['id' => $orderId, 'new_item_ids' => $newItemIds];
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
        if (class_exists('FiscalService') && FiscalService::isDayClosed(date('Y-m-d'))) {
            throw new InvalidArgumentException('Bugünün gün sonu kapanmıştır; tahsilat yapılamaz.');
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
        // Masa açılış bilgilerini temizle
        try {
            $pdo->prepare(
                'UPDATE dining_tables
                 SET opened_by_staff_id = NULL, opened_by_name = NULL
                 WHERE id = ?'
            )->execute([$tableId]);
        } catch (Throwable) {
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

    /** Tüm masalar (pasif dahil) + açık sipariş özeti */
    public static function tablesOverviewAll(): array
    {
        $pdo = Database::pdo();
        $tables = $pdo->query('SELECT * FROM dining_tables ORDER BY id')->fetchAll();
        $openMap = [];
        foreach (self::tablesOverview() as $row) {
            $openMap[(int) $row['id']] = $row;
        }
        foreach ($tables as &$table) {
            $id = (int) $table['id'];
            $open = $openMap[$id] ?? null;
            $table['is_open'] = !empty($open['is_open']);
            $table['open_count'] = (int) ($open['open_count'] ?? 0);
            $table['open_total'] = (float) ($open['open_total'] ?? 0);
            $table['waiter_names'] = $open['waiter_names'] ?? [];
        }
        unset($table);
        return $tables;
    }

    /**
     * Mutfak / bar bekleyen ürün satırları.
     * @return list<array<string,mixed>>
     */
    public static function stationQueued(string $station): array
    {
        if (!in_array($station, ['kitchen', 'bar'], true)) {
            throw new InvalidArgumentException('Geçersiz istasyon.');
        }
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            "SELECT oi.id, oi.quantity, oi.item_name, oi.status, oi.note, oi.station,
                    o.order_code, o.table_id, o.source, o.customer_note, o.created_at AS order_time,
                    t.label AS table_label
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             LEFT JOIN dining_tables t ON t.id = o.table_id
             WHERE oi.station = ?
               AND oi.status IN ('queued','preparing')
               AND o.status NOT IN ('cancelled','paid','pending')
             ORDER BY oi.id ASC"
        );
        $stmt->execute([$station]);
        return $stmt->fetchAll() ?: [];
    }

    /** Canlı yenileme için kısa sürüm anahtarı */
    public static function snapshotVersion(array $rows): string
    {
        $parts = [];
        foreach ($rows as $row) {
            $parts[] = implode(':', [
                (string) ($row['id'] ?? ''),
                (string) ($row['status'] ?? ''),
                (string) ($row['open_count'] ?? ''),
                (string) ($row['open_total'] ?? ''),
                (string) ($row['is_open'] ?? ''),
                (string) ($row['is_active'] ?? ''),
                (string) ($row['quantity'] ?? ''),
            ]);
        }
        return substr(sha1(implode('|', $parts)), 0, 16);
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

    /**
     * Online siparişi onayla: mutfak + bar fişleri gönderilir.
     */
    public static function acceptOnlineOrder(int $orderId, ?int $staffId = null): array
    {
        $order = self::findById($orderId);
        if (!$order) {
            throw new InvalidArgumentException('Sipariş bulunamadı.');
        }
        if (($order['source'] ?? '') !== 'online') {
            throw new InvalidArgumentException('Yalnızca online siparişler onaylanır.');
        }
        if (($order['status'] ?? '') === 'cancelled') {
            throw new InvalidArgumentException('İptal sipariş onaylanamaz.');
        }
        if (($order['status'] ?? '') === 'paid') {
            throw new InvalidArgumentException('Ödenmiş sipariş onaylanamaz.');
        }
        if (($order['status'] ?? '') !== 'pending') {
            // Already accepted — idempotent return
            return $order;
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ? AND status = ?')
                ->execute(['accepted', $orderId, 'pending']);
            self::addEvent($pdo, $orderId, $staffId, 'status_accepted', 'Online sipariş onaylandı');
            $hasKitchen = false;
            $hasBar = false;
            foreach ($order['active_items'] ?? $order['items'] ?? [] as $row) {
                if (($row['status'] ?? '') === 'cancelled') {
                    continue;
                }
                if (($row['station'] ?? '') === 'bar') {
                    $hasBar = true;
                } elseif (($row['station'] ?? '') === 'kitchen') {
                    $hasKitchen = true;
                }
            }
            if ($hasKitchen) {
                self::addEvent($pdo, $orderId, $staffId, 'sent_kitchen', 'Mutfak fişi gönderildi');
            }
            if ($hasBar) {
                self::addEvent($pdo, $orderId, $staffId, 'sent_bar', 'Bar fişi gönderildi');
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        return self::findById($orderId) ?: $order;
    }

    /** @return list<array> */
    public static function listOnlinePending(int $limit = 50): array
    {
        return self::listRecent([
            'source' => 'online',
            'status' => 'pending',
        ], $limit);
    }

    /** @return list<array> */
    public static function listOnlineActive(int $limit = 40): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            "SELECT o.*, t.label AS table_label, s.name AS waiter_name
             FROM orders o
             LEFT JOIN dining_tables t ON t.id = o.table_id
             LEFT JOIN staff s ON s.id = o.waiter_id
             WHERE o.source = 'online'
               AND o.status NOT IN ('pending', 'paid', 'cancelled')
             ORDER BY o.id DESC
             LIMIT " . (int) $limit
        );
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
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
            $vatRate = class_exists('FiscalService')
                ? FiscalService::normalizeVatRate($menu['vat_rate'] ?? FiscalService::DEFAULT_VAT_RATE)
                : 10.0;
            $normalized[] = [
                'menu_item_id' => (int) $menu['id'],
                'item_name' => (string) $menu['name'],
                'station' => (string) $menu['station'],
                'unit_price' => (float) $menu['price'],
                'vat_rate' => $vatRate,
                'quantity' => $qty,
                'note' => $note !== '' ? $note : null,
            ];
        }
        if ($normalized === []) {
            throw new InvalidArgumentException('Geçerli ürün seçilmedi.');
        }
        return $normalized;
    }

    /** @return list<int> newly inserted order_item ids */
    private static function insertItems(PDO $pdo, int $orderId, array $normalized): array
    {
        $itemStmt = $pdo->prepare(
            'INSERT INTO order_items
            (order_id, menu_item_id, item_name, station, unit_price, vat_rate, quantity, note, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $ids = [];
        foreach ($normalized as $item) {
            $itemStmt->execute([
                $orderId,
                $item['menu_item_id'],
                $item['item_name'],
                $item['station'],
                $item['unit_price'],
                $item['vat_rate'] ?? 10.0,
                $item['quantity'],
                $item['note'],
                'queued',
            ]);
            $ids[] = (int) $pdo->lastInsertId();
        }
        return $ids;
    }

    private static function recalcTotals(PDO $pdo, int $orderId): void
    {
        $stmt = $pdo->prepare(
            "SELECT COALESCE(SUM(unit_price * quantity), 0)
             FROM order_items
             WHERE order_id = ? AND status != 'cancelled'"
        );
        $stmt->execute([$orderId]);
        $subtotal = (float) $stmt->fetchColumn();

        $disc = $pdo->prepare(
            'SELECT discount_percent, discount_amount, discount_code FROM orders WHERE id = ? LIMIT 1'
        );
        $disc->execute([$orderId]);
        $row = $disc->fetch() ?: [];
        $percent = (float) ($row['discount_percent'] ?? 0);
        $amount = (float) ($row['discount_amount'] ?? 0);
        if ($percent > 0) {
            $amount = round($subtotal * ($percent / 100), 2);
        }
        $total = max(0, round($subtotal - $amount, 2));
        $pdo->prepare(
            'UPDATE orders SET subtotal = ?, discount_amount = ?, total = ?, updated_at = NOW() WHERE id = ?'
        )->execute([$subtotal, $amount, $total, $orderId]);
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

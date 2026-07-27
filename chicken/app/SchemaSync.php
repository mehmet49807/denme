<?php

declare(strict_types=1);

/**
 * Lightweight migrations for already-installed databases.
 */
final class SchemaSync
{
    public static function ensure(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        try {
            $pdo = Database::pdo();
        } catch (Throwable) {
            return;
        }

        try {
            self::ensureColumn(
                $pdo,
                'orders',
                'payment_method',
                "ALTER TABLE orders ADD COLUMN payment_method ENUM('cash','card') NULL DEFAULT NULL AFTER paid_at"
            );
        } catch (Throwable) {
        }

        try {
            self::ensureColumn(
                $pdo,
                'orders',
                'payment_preference',
                "ALTER TABLE orders ADD COLUMN payment_preference ENUM('cash','card') NULL DEFAULT NULL AFTER customer_note"
            );
        } catch (Throwable) {
        }

        try {
            $pdo->exec(
                "ALTER TABLE orders
                 MODIFY COLUMN source ENUM('online','waiter','cashier') NOT NULL"
            );
        } catch (Throwable) {
        }

        try {
            self::ensureColumn(
                $pdo,
                'menu_items',
                'image_url',
                'ALTER TABLE menu_items ADD COLUMN image_url VARCHAR(255) NULL AFTER is_available'
            );
        } catch (Throwable) {
        }

        try {
            self::ensureCustomers($pdo);
        } catch (Throwable) {
        }

        try {
            self::ensureDiscountCodes($pdo);
        } catch (Throwable) {
        }

        try {
            self::ensureOrderDiscountColumns($pdo);
        } catch (Throwable) {
        }

        try {
            self::ensureStaffDeleteSetNull(
                $pdo,
                'orders',
                'waiter_id',
                'fk_orders_waiter'
            );
        } catch (Throwable) {
        }

        try {
            self::ensureStaffDeleteSetNull(
                $pdo,
                'order_events',
                'staff_id',
                'fk_events_staff'
            );
        } catch (Throwable) {
        }
    }

    private static function ensureCustomers(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS customers (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              name VARCHAR(120) NOT NULL,
              email VARCHAR(160) NOT NULL UNIQUE,
              phone VARCHAR(40) NOT NULL UNIQUE,
              address VARCHAR(400) NULL,
              password_hash VARCHAR(255) NOT NULL,
              is_active TINYINT(1) NOT NULL DEFAULT 1,
              welcome_discount_used TINYINT(1) NOT NULL DEFAULT 0,
              created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        self::ensureColumn(
            $pdo,
            'customers',
            'address',
            'ALTER TABLE customers ADD COLUMN address VARCHAR(400) NULL AFTER phone'
        );
    }

    private static function ensureDiscountCodes(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS discount_codes (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              code VARCHAR(40) NOT NULL UNIQUE,
              label VARCHAR(120) NOT NULL,
              percent DECIMAL(5,2) NOT NULL,
              is_active TINYINT(1) NOT NULL DEFAULT 1,
              created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $pdo->prepare(
            'INSERT INTO discount_codes (code, label, percent, is_active)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE label = VALUES(label), percent = VALUES(percent), is_active = 1'
        )->execute([DiscountService::WELCOME_CODE, 'Yeni üye %10 indirim', DiscountService::WELCOME_PERCENT]);
    }

    private static function ensureOrderDiscountColumns(PDO $pdo): void
    {
        self::ensureColumn(
            $pdo,
            'orders',
            'customer_id',
            'ALTER TABLE orders ADD COLUMN customer_id INT UNSIGNED NULL AFTER waiter_id'
        );
        self::ensureColumn(
            $pdo,
            'orders',
            'discount_code',
            'ALTER TABLE orders ADD COLUMN discount_code VARCHAR(40) NULL AFTER subtotal'
        );
        self::ensureColumn(
            $pdo,
            'orders',
            'discount_percent',
            'ALTER TABLE orders ADD COLUMN discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER discount_code'
        );
        self::ensureColumn(
            $pdo,
            'orders',
            'discount_amount',
            'ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER discount_percent'
        );

        try {
            $stmt = $pdo->query(
                "SELECT CONSTRAINT_NAME
                 FROM information_schema.REFERENTIAL_CONSTRAINTS
                 WHERE CONSTRAINT_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'orders'
                   AND REFERENCED_TABLE_NAME = 'customers'
                 LIMIT 1"
            );
            $existing = $stmt ? $stmt->fetchColumn() : false;
            if (!$existing) {
                $pdo->exec(
                    'ALTER TABLE orders
                     ADD CONSTRAINT fk_orders_customer
                     FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL'
                );
            }
        } catch (Throwable) {
        }
    }

    private static function ensureStaffDeleteSetNull(
        PDO $pdo,
        string $table,
        string $column,
        string $constraintName
    ): void {
        // Allow hard-deleting waiters while keeping historical rows.
        $stmt = $pdo->query(
            "SELECT CONSTRAINT_NAME, DELETE_RULE
             FROM information_schema.REFERENTIAL_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE()
               AND TABLE_NAME = " . $pdo->quote($table) . "
               AND REFERENCED_TABLE_NAME = 'staff'"
        );
        $rows = $stmt ? $stmt->fetchAll() : [];
        foreach ($rows as $row) {
            $name = (string) ($row['CONSTRAINT_NAME'] ?? '');
            $rule = strtoupper((string) ($row['DELETE_RULE'] ?? ''));
            if ($name === '') {
                continue;
            }
            if ($rule === 'SET NULL') {
                return;
            }
            $pdo->exec('ALTER TABLE `' . str_replace('`', '``', $table) . '` DROP FOREIGN KEY `' . str_replace('`', '``', $name) . '`');
        }
        $safeTable = str_replace('`', '``', $table);
        $safeColumn = str_replace('`', '``', $column);
        $safeConstraint = str_replace('`', '``', $constraintName);
        $pdo->exec(
            "ALTER TABLE `{$safeTable}`
             ADD CONSTRAINT `{$safeConstraint}`
             FOREIGN KEY (`{$safeColumn}`) REFERENCES staff(id) ON DELETE SET NULL"
        );
    }

    private static function ensureColumn(PDO $pdo, string $table, string $column, string $alterSql): void
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->execute([$table, $column]);
        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }
        $pdo->exec($alterSql);
    }
}

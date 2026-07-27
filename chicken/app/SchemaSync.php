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

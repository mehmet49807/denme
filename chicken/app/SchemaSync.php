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

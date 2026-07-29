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
            self::ensureColumn(
                $pdo,
                'menu_items',
                'vat_rate',
                'ALTER TABLE menu_items ADD COLUMN vat_rate DECIMAL(5,2) NOT NULL DEFAULT 10.00 AFTER price'
            );
            // Restoran yeme-içme hizmeti: varsayılan KDV %10 (TR)
            $pdo->exec('UPDATE menu_items SET vat_rate = 10.00 WHERE vat_rate <= 0 OR vat_rate IS NULL');
            self::ensureColumn(
                $pdo,
                'order_items',
                'vat_rate',
                'ALTER TABLE order_items ADD COLUMN vat_rate DECIMAL(5,2) NOT NULL DEFAULT 10.00 AFTER unit_price'
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

        try {
            self::ensureFranchiseApplications($pdo);
        } catch (Throwable) {
        }

        try {
            self::ensureBranches($pdo);
        } catch (Throwable) {
        }

        try {
            self::ensureColumn(
                $pdo,
                'dining_tables',
                'opened_by_name',
                'ALTER TABLE dining_tables ADD COLUMN opened_by_name VARCHAR(120) NULL AFTER qr_token'
            );
            self::ensureColumn(
                $pdo,
                'dining_tables',
                'opened_by_staff_id',
                'ALTER TABLE dining_tables ADD COLUMN opened_by_staff_id INT UNSIGNED NULL AFTER opened_by_name'
            );
            self::ensureStaffDeleteSetNull(
                $pdo,
                'dining_tables',
                'opened_by_staff_id',
                'fk_tables_opened_by'
            );
        } catch (Throwable) {
        }

        try {
            self::ensureFiscalTables($pdo);
        } catch (Throwable) {
        }

        try {
            self::ensureKitchenBarRoles($pdo);
        } catch (Throwable) {
        }

        try {
            self::ensureOpsUpgrade($pdo);
        } catch (Throwable) {
        }

        try {
            // Eski marka adı kalmışsa Crisp & Co. ile hizala
            $pdo->prepare(
                "UPDATE settings SET setting_value = 'Crisp & Co.'
                 WHERE setting_key = 'restaurant_name' AND setting_value IN ('Chicken', 'chicken')"
            )->execute();
        } catch (Throwable) {
        }
    }

    private static function ensureFiscalTables(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS invoices (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              invoice_no VARCHAR(32) NOT NULL UNIQUE,
              invoice_date DATE NOT NULL,
              order_id INT UNSIGNED NOT NULL UNIQUE,
              staff_id INT UNSIGNED NULL,
              company_title VARCHAR(160) NOT NULL,
              company_vkn VARCHAR(11) NULL,
              company_tax_office VARCHAR(120) NULL,
              company_address VARCHAR(400) NULL,
              company_city VARCHAR(80) NULL,
              company_phone VARCHAR(40) NULL,
              buyer_name VARCHAR(160) NOT NULL DEFAULT 'Nihai Tüketici',
              buyer_tax_id VARCHAR(11) NULL,
              buyer_tax_office VARCHAR(120) NULL,
              buyer_address VARCHAR(400) NULL,
              vat_rate DECIMAL(5,2) NOT NULL DEFAULT 10.00,
              net_total DECIMAL(10,2) NOT NULL,
              vat_total DECIMAL(10,2) NOT NULL,
              gross_total DECIMAL(10,2) NOT NULL,
              payment_method ENUM('cash','card') NULL,
              lines_json LONGTEXT NOT NULL,
              created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              INDEX idx_invoices_date (invoice_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS day_closes (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              business_date DATE NOT NULL UNIQUE,
              closed_by_staff_id INT UNSIGNED NULL,
              paid_order_count INT UNSIGNED NOT NULL DEFAULT 0,
              invoice_count INT UNSIGNED NOT NULL DEFAULT 0,
              cash_total DECIMAL(10,2) NOT NULL DEFAULT 0,
              card_total DECIMAL(10,2) NOT NULL DEFAULT 0,
              net_total DECIMAL(10,2) NOT NULL DEFAULT 0,
              vat_total DECIMAL(10,2) NOT NULL DEFAULT 0,
              gross_total DECIMAL(10,2) NOT NULL DEFAULT 0,
              vat_rate DECIMAL(5,2) NOT NULL DEFAULT 10.00,
              is_auto TINYINT(1) NOT NULL DEFAULT 0,
              note VARCHAR(500) NULL,
              created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        self::ensureColumn(
            $pdo,
            'day_closes',
            'is_auto',
            'ALTER TABLE day_closes ADD COLUMN is_auto TINYINT(1) NOT NULL DEFAULT 0 AFTER vat_rate'
        );
        self::ensureColumn(
            $pdo,
            'orders',
            'invoice_id',
            'ALTER TABLE orders ADD COLUMN invoice_id INT UNSIGNED NULL AFTER payment_method'
        );
    }

    private static function ensureFranchiseApplications(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS franchise_applications (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              full_name VARCHAR(120) NOT NULL,
              phone VARCHAR(40) NOT NULL,
              email VARCHAR(160) NOT NULL,
              city VARCHAR(80) NOT NULL,
              district VARCHAR(80) NULL,
              preferred_branch_id INT UNSIGNED NULL,
              budget_range VARCHAR(80) NULL,
              experience VARCHAR(400) NULL,
              message TEXT NULL,
              status ENUM('new','reviewing','approved','rejected') NOT NULL DEFAULT 'new',
              admin_note VARCHAR(500) NULL,
              reviewed_at TIMESTAMP NULL DEFAULT NULL,
              created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              INDEX idx_franchise_status_created (status, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        self::ensureColumn(
            $pdo,
            'franchise_applications',
            'preferred_branch_id',
            'ALTER TABLE franchise_applications ADD COLUMN preferred_branch_id INT UNSIGNED NULL AFTER district'
        );
    }

    private static function ensureBranches(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS branches (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              name VARCHAR(120) NOT NULL,
              city VARCHAR(80) NOT NULL,
              phone VARCHAR(40) NULL,
              whatsapp VARCHAR(40) NULL,
              address VARCHAR(400) NULL,
              is_active TINYINT(1) NOT NULL DEFAULT 1,
              sort_order INT NOT NULL DEFAULT 0,
              created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              INDEX idx_branches_active_sort (is_active, sort_order),
              INDEX idx_branches_city (city)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        if (class_exists('BranchService')) {
            BranchService::ensureSeed();
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

    /** Mutfak / bar personel rolleri + sipariş fiş takip alanları */
    private static function ensureKitchenBarRoles(PDO $pdo): void
    {
        try {
            $pdo->exec(
                "ALTER TABLE staff
                 MODIFY COLUMN role ENUM('admin','cashier','waiter','kitchen','bar') NOT NULL"
            );
        } catch (Throwable) {
        }

        self::ensureColumn(
            $pdo,
            'orders',
            'kitchen_slip_sent_at',
            'ALTER TABLE orders ADD COLUMN kitchen_slip_sent_at DATETIME NULL DEFAULT NULL AFTER paid_at'
        );
        self::ensureColumn(
            $pdo,
            'orders',
            'bar_slip_sent_at',
            'ALTER TABLE orders ADD COLUMN bar_slip_sent_at DATETIME NULL DEFAULT NULL AFTER kitchen_slip_sent_at'
        );
        self::ensureColumn(
            $pdo,
            'orders',
            'kitchen_slip_acked_at',
            'ALTER TABLE orders ADD COLUMN kitchen_slip_acked_at DATETIME NULL DEFAULT NULL AFTER bar_slip_sent_at'
        );
        self::ensureColumn(
            $pdo,
            'orders',
            'bar_slip_acked_at',
            'ALTER TABLE orders ADD COLUMN bar_slip_acked_at DATETIME NULL DEFAULT NULL AFTER kitchen_slip_acked_at'
        );

        // Varsayılan mutfak / bar kullanıcıları (yoksa)
        $hash = password_hash('password', PASSWORD_DEFAULT);
        foreach (
            [
                ['Mutfak', 'mutfak', 'kitchen', '4444'],
                ['Bar', 'bar', 'bar', '5555'],
            ] as [$name, $username, $role, $pin]
        ) {
            $check = $pdo->prepare('SELECT id FROM staff WHERE username = ? LIMIT 1');
            $check->execute([$username]);
            if ($check->fetch()) {
                continue;
            }
            $ins = $pdo->prepare(
                'INSERT INTO staff (name, username, password_hash, role, pin, is_active)
                 VALUES (?, ?, ?, ?, ?, 1)'
            );
            $ins->execute([$name, $username, $hash, $role, $pin]);
        }
    }

    private static function ensureOpsUpgrade(PDO $pdo): void
    {
        self::ensureColumn(
            $pdo,
            'menu_items',
            'stock_qty',
            'ALTER TABLE menu_items ADD COLUMN stock_qty DECIMAL(10,2) NULL DEFAULT NULL AFTER is_available'
        );
        self::ensureColumn(
            $pdo,
            'menu_items',
            'stock_alert_qty',
            'ALTER TABLE menu_items ADD COLUMN stock_alert_qty DECIMAL(10,2) NULL DEFAULT NULL AFTER stock_qty'
        );
        self::ensureColumn(
            $pdo,
            'orders',
            'delivery_zone',
            'ALTER TABLE orders ADD COLUMN delivery_zone VARCHAR(120) NULL DEFAULT NULL AFTER customer_note'
        );
        self::ensureColumn(
            $pdo,
            'orders',
            'delivery_address',
            'ALTER TABLE orders ADD COLUMN delivery_address VARCHAR(400) NULL DEFAULT NULL AFTER delivery_zone'
        );
        self::ensureColumn(
            $pdo,
            'orders',
            'delivery_fee',
            'ALTER TABLE orders ADD COLUMN delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER delivery_address'
        );
        self::ensureColumn(
            $pdo,
            'orders',
            'eta_minutes',
            'ALTER TABLE orders ADD COLUMN eta_minutes INT UNSIGNED NULL DEFAULT NULL AFTER delivery_fee'
        );
        self::ensureColumn(
            $pdo,
            'orders',
            'branch_id',
            'ALTER TABLE orders ADD COLUMN branch_id INT UNSIGNED NULL DEFAULT NULL AFTER table_id'
        );
        self::ensureColumn(
            $pdo,
            'order_items',
            'ready_at',
            'ALTER TABLE order_items ADD COLUMN ready_at DATETIME NULL DEFAULT NULL AFTER status'
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS staff_login_logs (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              staff_id INT UNSIGNED NULL,
              username VARCHAR(80) NOT NULL DEFAULT '',
              role VARCHAR(40) NOT NULL DEFAULT '',
              event_type ENUM('login','logout') NOT NULL,
              ip_address VARCHAR(64) NULL,
              created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              INDEX idx_login_logs_created (created_at),
              INDEX idx_login_logs_staff (staff_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS staff_shifts (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              staff_id INT UNSIGNED NOT NULL,
              opened_at DATETIME NOT NULL,
              closed_at DATETIME NULL DEFAULT NULL,
              note VARCHAR(255) NULL,
              created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              INDEX idx_shifts_staff_open (staff_id, closed_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS menu_item_branch_prices (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              menu_item_id INT UNSIGNED NOT NULL,
              branch_id INT UNSIGNED NOT NULL,
              price DECIMAL(10,2) NOT NULL,
              updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY uq_menu_branch (menu_item_id, branch_id),
              INDEX idx_branch_price_branch (branch_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        // Varsayılan ops ayarları
        $defaults = [
            'station_wait_alert_minutes' => '15',
            'online_eta_minutes' => '35',
            'online_min_total' => '0',
            'delivery_zones_json' => '[]',
            'qz_enabled' => '0',
            'qz_printer_kitchen' => '',
            'qz_printer_bar' => '',
            'whatsapp_customer_status' => '1',
            'slip_history_limit' => '30',
            'pos_branch_id' => '0',
        ];
        foreach ($defaults as $key => $value) {
            $check = $pdo->prepare('SELECT 1 FROM settings WHERE setting_key = ? LIMIT 1');
            $check->execute([$key]);
            if (!$check->fetchColumn()) {
                $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)')
                    ->execute([$key, $value]);
            }
        }
    }
}

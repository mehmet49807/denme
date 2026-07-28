<?php

declare(strict_types=1);

/**
 * Türkiye restoran POS: satış faturası (KDV dahil) + gün sonu kapanış.
 * Not: GİB e-Fatura/e-Arşiv entegrasyonu değildir; işletme içi yasal kayıt fişi.
 */
final class FiscalService
{
    public const DEFAULT_VAT_RATE = 10.0; // Restoran hizmeti yaygın KDV

    /** @return array<string, string> */
    public static function companyProfile(): array
    {
        return [
            'title' => (string) (BrochureService::getSetting('fiscal_company_title', 'Crisp & Co.') ?: 'Crisp & Co.'),
            'vkn' => (string) (BrochureService::getSetting('fiscal_vkn', '') ?: ''),
            'tax_office' => (string) (BrochureService::getSetting('fiscal_tax_office', '') ?: ''),
            'address' => (string) (BrochureService::getSetting('fiscal_address', '') ?: ''),
            'city' => (string) (BrochureService::getSetting('fiscal_city', 'Antalya') ?: 'Antalya'),
            'phone' => (string) (BrochureService::getSetting('fiscal_phone', '') ?: ''),
            'vat_rate' => (string) (BrochureService::getSetting('fiscal_vat_rate', (string) self::DEFAULT_VAT_RATE) ?: (string) self::DEFAULT_VAT_RATE),
        ];
    }

    /** @param array<string, string> $data */
    public static function saveCompanyProfile(array $data): void
    {
        $map = [
            'fiscal_company_title' => trim((string) ($data['title'] ?? '')),
            'fiscal_vkn' => preg_replace('/\D+/', '', (string) ($data['vkn'] ?? '')) ?: '',
            'fiscal_tax_office' => trim((string) ($data['tax_office'] ?? '')),
            'fiscal_address' => trim((string) ($data['address'] ?? '')),
            'fiscal_city' => trim((string) ($data['city'] ?? '')),
            'fiscal_phone' => trim((string) ($data['phone'] ?? '')),
        ];
        $vat = (float) str_replace(',', '.', (string) ($data['vat_rate'] ?? self::DEFAULT_VAT_RATE));
        $vat = max(0, min(20, $vat));
        $map['fiscal_vat_rate'] = rtrim(rtrim(number_format($vat, 2, '.', ''), '0'), '.');

        if ($map['fiscal_company_title'] === '') {
            throw new InvalidArgumentException('Firma ünvanı gerekli.');
        }
        if ($map['fiscal_vkn'] !== '' && !preg_match('/^\d{10,11}$/', $map['fiscal_vkn'])) {
            throw new InvalidArgumentException('VKN/TCKN 10 veya 11 haneli olmalıdır.');
        }

        foreach ($map as $key => $value) {
            BrochureService::setSetting($key, $value);
        }
    }

    public static function vatRate(): float
    {
        $raw = BrochureService::getSetting('fiscal_vat_rate', (string) self::DEFAULT_VAT_RATE);
        $rate = (float) str_replace(',', '.', (string) $raw);
        if ($rate < 0 || $rate > 20) {
            return self::DEFAULT_VAT_RATE;
        }
        return $rate;
    }

    /**
     * KDV dahil tutardan matrah + KDV ayırır (Türkiye perakende/restoran uygulaması).
     * @return array{gross:float,net:float,vat:float,rate:float}
     */
    public static function splitVat(float $grossInclusive, ?float $rate = null): array
    {
        $rate = $rate ?? self::vatRate();
        $gross = round(max(0, $grossInclusive), 2);
        if ($rate <= 0) {
            return ['gross' => $gross, 'net' => $gross, 'vat' => 0.0, 'rate' => 0.0];
        }
        $net = round($gross / (1 + ($rate / 100)), 2);
        $vat = round($gross - $net, 2);
        return ['gross' => $gross, 'net' => $net, 'vat' => $vat, 'rate' => $rate];
    }

    public static function findInvoiceByOrder(int $orderId): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM invoices WHERE order_id = ? LIMIT 1');
        $stmt->execute([$orderId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findInvoice(int $invoiceId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT i.*, o.order_code, o.source, o.payment_method, o.table_id, t.label AS table_label
             FROM invoices i
             JOIN orders o ON o.id = i.order_id
             LEFT JOIN dining_tables t ON t.id = o.table_id
             WHERE i.id = ?
             LIMIT 1'
        );
        $stmt->execute([$invoiceId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Ödenmiş sipariş için satış faturası keser (veya mevcutu döner).
     * @return array invoice row
     */
    public static function issueForOrder(int $orderId, ?int $staffId, array $buyer = []): array
    {
        $existing = self::findInvoiceByOrder($orderId);
        if ($existing) {
            return $existing;
        }

        $order = OrderService::findById($orderId);
        if (!$order) {
            throw new InvalidArgumentException('Sipariş bulunamadı.');
        }
        if (($order['status'] ?? '') !== 'paid') {
            throw new InvalidArgumentException('Fatura yalnızca ödenmiş sipariş için kesilir.');
        }

        $businessDate = substr((string) ($order['paid_at'] ?? date('Y-m-d H:i:s')), 0, 10);
        if (self::isDayClosed($businessDate)) {
            throw new InvalidArgumentException('Bu güne ait gün sonu kapanmıştır; yeni fatura kesilemez.');
        }

        $company = self::companyProfile();
        $rate = self::vatRate();
        $gross = (float) $order['total'];
        $split = self::splitVat($gross, $rate);

        $lines = [];
        foreach ($order['items'] as $item) {
            if (($item['status'] ?? '') === 'cancelled') {
                continue;
            }
            $lineGross = round((float) $item['unit_price'] * (int) $item['quantity'], 2);
            $lineSplit = self::splitVat($lineGross, $rate);
            $lines[] = [
                'name' => (string) $item['item_name'],
                'qty' => (int) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
                'gross' => $lineGross,
                'net' => $lineSplit['net'],
                'vat' => $lineSplit['vat'],
            ];
        }
        if ($lines === []) {
            throw new InvalidArgumentException('Faturada kalem yok.');
        }

        $buyerName = trim((string) ($buyer['name'] ?? ($order['customer_name'] ?? 'Nihai Tüketici')));
        if ($buyerName === '') {
            $buyerName = 'Nihai Tüketici';
        }
        $buyerTax = preg_replace('/\D+/', '', (string) ($buyer['tax_id'] ?? '')) ?: null;
        $buyerTaxOffice = trim((string) ($buyer['tax_office'] ?? '')) ?: null;
        $buyerAddress = trim((string) ($buyer['address'] ?? '')) ?: null;

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $invoiceNo = self::nextInvoiceNumber($pdo, $businessDate);
            $stmt = $pdo->prepare(
                'INSERT INTO invoices (
                    invoice_no, invoice_date, order_id, staff_id,
                    company_title, company_vkn, company_tax_office, company_address, company_city, company_phone,
                    buyer_name, buyer_tax_id, buyer_tax_office, buyer_address,
                    vat_rate, net_total, vat_total, gross_total, payment_method, lines_json
                 ) VALUES (
                    ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?
                 )'
            );
            $stmt->execute([
                $invoiceNo,
                $businessDate,
                $orderId,
                $staffId,
                $company['title'],
                $company['vkn'] !== '' ? $company['vkn'] : null,
                $company['tax_office'] !== '' ? $company['tax_office'] : null,
                $company['address'] !== '' ? $company['address'] : null,
                $company['city'] !== '' ? $company['city'] : null,
                $company['phone'] !== '' ? $company['phone'] : null,
                $buyerName,
                $buyerTax,
                $buyerTaxOffice,
                $buyerAddress,
                $rate,
                $split['net'],
                $split['vat'],
                $split['gross'],
                $order['payment_method'] ?? null,
                json_encode($lines, JSON_UNESCAPED_UNICODE),
            ]);
            $id = (int) $pdo->lastInsertId();
            $pdo->prepare(
                'UPDATE orders SET invoice_id = ? WHERE id = ?'
            )->execute([$id, $orderId]);
            OrderService::addEvent(
                $pdo,
                $orderId,
                $staffId,
                'invoice_issued',
                'Satış faturası kesildi: ' . $invoiceNo
            );
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        $inv = self::findInvoice($id);
        if (!$inv) {
            throw new RuntimeException('Fatura kaydı okunamadı.');
        }
        return $inv;
    }

    private static function nextInvoiceNumber(PDO $pdo, string $date): string
    {
        $prefix = 'SF' . str_replace('-', '', $date);
        $stmt = $pdo->prepare(
            'SELECT invoice_no FROM invoices WHERE invoice_no LIKE ? ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$prefix . '%']);
        $last = (string) ($stmt->fetchColumn() ?: '');
        $seq = 1;
        if ($last !== '' && preg_match('/(\d{4})$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }
        return $prefix . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public static function isDayClosed(string $date): bool
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id FROM day_closes WHERE business_date = ? LIMIT 1'
        );
        $stmt->execute([$date]);
        return (bool) $stmt->fetch();
    }

    public static function findDayClose(string $date): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT d.*, s.name AS closed_by_name
             FROM day_closes d
             LEFT JOIN staff s ON s.id = d.closed_by_staff_id
             WHERE d.business_date = ?
             LIMIT 1'
        );
        $stmt->execute([$date]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return array{date:string,open_tables:int,open_orders:int,paid_orders:int,cash_total:float,card_total:float,gross_total:float,net_total:float,vat_total:float,invoice_count:int,invoice_gross:float,orders:list<array>} */
    public static function daySummary(string $date): array
    {
        $pdo = Database::pdo();
        $openTables = (int) $pdo->query(
            "SELECT COUNT(DISTINCT table_id) FROM orders
             WHERE table_id IS NOT NULL AND status NOT IN ('paid','cancelled')"
        )->fetchColumn();
        $openOrders = (int) $pdo->query(
            "SELECT COUNT(*) FROM orders WHERE status NOT IN ('paid','cancelled')"
        )->fetchColumn();

        $paidStmt = $pdo->prepare(
            "SELECT o.*, t.label AS table_label, s.name AS waiter_name
             FROM orders o
             LEFT JOIN dining_tables t ON t.id = o.table_id
             LEFT JOIN staff s ON s.id = o.waiter_id
             WHERE o.status = 'paid'
               AND DATE(COALESCE(o.paid_at, o.updated_at, o.created_at)) = ?
             ORDER BY o.id ASC"
        );
        $paidStmt->execute([$date]);
        $orders = $paidStmt->fetchAll();

        $cash = 0.0;
        $card = 0.0;
        $gross = 0.0;
        foreach ($orders as $o) {
            $total = (float) $o['total'];
            $gross += $total;
            if (($o['payment_method'] ?? '') === 'card') {
                $card += $total;
            } else {
                $cash += $total;
            }
        }
        $split = self::splitVat($gross);

        $invStmt = $pdo->prepare(
            'SELECT COUNT(*) AS cnt, COALESCE(SUM(gross_total),0) AS gross_sum
             FROM invoices WHERE invoice_date = ?'
        );
        $invStmt->execute([$date]);
        $inv = $invStmt->fetch() ?: ['cnt' => 0, 'gross_sum' => 0];

        return [
            'date' => $date,
            'open_tables' => $openTables,
            'open_orders' => $openOrders,
            'paid_orders' => count($orders),
            'cash_total' => round($cash, 2),
            'card_total' => round($card, 2),
            'gross_total' => round($gross, 2),
            'net_total' => $split['net'],
            'vat_total' => $split['vat'],
            'vat_rate' => $split['rate'],
            'invoice_count' => (int) $inv['cnt'],
            'invoice_gross' => round((float) $inv['gross_sum'], 2),
            'orders' => $orders,
            'is_closed' => self::isDayClosed($date),
            'close' => self::findDayClose($date),
        ];
    }

    /**
     * Gün sonu kapanışı.
     * Manuel: açık masa/sipariş varken engellenir (bugün için).
     * Otomatik / geçmiş gün: açık masa kontrolü atlanır (gece 00:00 kapanışı).
     *
     * @return array day_closes row
     */
    public static function closeDay(string $date, ?int $staffId, string $note = '', bool $isAuto = false): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new InvalidArgumentException('Geçersiz tarih.');
        }
        if (self::isDayClosed($date)) {
            throw new InvalidArgumentException('Bu gün zaten kapatılmış.');
        }

        $today = date('Y-m-d');
        $isPastDate = $date < $today;
        $summary = self::daySummary($date);

        // Bugünün manuel kapanışında açık masa/sipariş engeli; geçmiş gün / otomatik için değil.
        if (!$isAuto && !$isPastDate && ($summary['open_tables'] > 0 || $summary['open_orders'] > 0)) {
            throw new InvalidArgumentException(
                'Gün sonu için önce açık masaları/siparişleri kapatın. Açık masa: '
                . $summary['open_tables'] . ', açık sipariş: ' . $summary['open_orders']
            );
        }

        if ($isAuto && trim($note) === '') {
            $note = 'Otomatik gece 00:00 gün sonu';
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO day_closes (
                business_date, closed_by_staff_id,
                paid_order_count, invoice_count,
                cash_total, card_total, net_total, vat_total, gross_total,
                vat_rate, is_auto, note
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $date,
            $isAuto ? null : $staffId,
            $summary['paid_orders'],
            $summary['invoice_count'],
            $summary['cash_total'],
            $summary['card_total'],
            $summary['net_total'],
            $summary['vat_total'],
            $summary['gross_total'],
            $summary['vat_rate'],
            $isAuto ? 1 : 0,
            trim($note) !== '' ? trim($note) : null,
        ]);

        return self::findDayClose($date) ?? [];
    }

    /**
     * Gece 00:00 sonrası eksik gün sonlarını otomatik alır (son 14 gün).
     * Shared hosting'de cron yoksa ilk kasa/yönetici isteğinde tetiklenir.
     *
     * @return list<string> kapatılan tarihler
     */
    public static function ensureAutoDayCloses(int $lookbackDays = 14): array
    {
        $lookbackDays = max(1, min(60, $lookbackDays));
        $closed = [];
        $today = date('Y-m-d');

        for ($i = 1; $i <= $lookbackDays; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} day", strtotime($today . ' 12:00:00')));
            if ($date >= $today) {
                continue;
            }
            if (self::isDayClosed($date)) {
                continue;
            }
            try {
                self::closeDay($date, null, 'Otomatik gece 00:00 gün sonu', true);
                $closed[] = $date;
            } catch (Throwable $e) {
                error_log('Auto day close failed for ' . $date . ': ' . $e->getMessage());
            }
        }

        return $closed;
    }

    /** @return list<array> */
    public static function recentDayCloses(int $limit = 60): array
    {
        $limit = max(1, min(200, $limit));
        return Database::pdo()->query(
            "SELECT d.*, s.name AS closed_by_name
             FROM day_closes d
             LEFT JOIN staff s ON s.id = d.closed_by_staff_id
             ORDER BY d.business_date DESC
             LIMIT {$limit}"
        )->fetchAll();
    }
}

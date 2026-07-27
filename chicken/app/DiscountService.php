<?php

declare(strict_types=1);

final class DiscountService
{
    public const WELCOME_CODE = 'YENI10';
    public const WELCOME_PERCENT = 10.0;

    /**
     * @return array{code:string,percent:float,amount:float,label:string}|null
     */
    public static function apply(?string $code, float $subtotal, ?array $customer): ?array
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '' || $subtotal <= 0) {
            return null;
        }

        if ($code === self::WELCOME_CODE) {
            if (!$customer) {
                throw new InvalidArgumentException('YENI10 kodu için giriş yapmalısınız.');
            }
            if (!empty($customer['welcome_discount_used'])) {
                throw new InvalidArgumentException('Hoş geldin indiriminiz daha önce kullanıldı.');
            }
            $percent = self::WELCOME_PERCENT;
            $amount = round($subtotal * ($percent / 100), 2);
            return [
                'code' => self::WELCOME_CODE,
                'percent' => $percent,
                'amount' => $amount,
                'label' => 'Yeni üye %10 indirim',
            ];
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT * FROM discount_codes WHERE code = ? AND is_active = 1 LIMIT 1'
        );
        $stmt->execute([$code]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new InvalidArgumentException('İndirim kodu geçersiz.');
        }
        $percent = (float) $row['percent'];
        if ($percent <= 0 || $percent > 100) {
            throw new InvalidArgumentException('İndirim kodu geçersiz.');
        }
        $amount = round($subtotal * ($percent / 100), 2);
        return [
            'code' => (string) $row['code'],
            'percent' => $percent,
            'amount' => $amount,
            'label' => (string) ($row['label'] ?? $row['code']),
        ];
    }
}

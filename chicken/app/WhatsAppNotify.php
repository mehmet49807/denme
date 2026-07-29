<?php

declare(strict_types=1);

/**
 * WhatsApp sipariş bildirimi.
 * - wa.me bağlantısı (personel tarayıcısında açılır)
 * - İsteğe bağlı Meta Cloud API (token + phone_number_id ayarlıysa)
 */
final class WhatsAppNotify
{
    public static function isEnabled(): bool
    {
        return BrochureService::getSetting('whatsapp_enabled', '0') === '1';
    }

    public static function notifyNumber(): string
    {
        return self::digitsOnly((string) BrochureService::getSetting('whatsapp_notify_number', ''));
    }

    public static function autoOpen(): bool
    {
        return BrochureService::getSetting('whatsapp_auto_open', '1') === '1';
    }

    public static function digitsOnly(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            $digits = '90' . substr($digits, 1);
        }
        return $digits;
    }

    public static function buildOrderMessage(array $order): string
    {
        $code = (string) ($order['order_code'] ?? '');
        $status = (string) ($order['status'] ?? '');
        $statusText = function_exists('status_label')
            ? status_label($status)
            : $status;
        $note = trim((string) ($order['customer_note'] ?? ''));

        $lines = [
            'Sipariş kodu: ' . $code,
            'Sipariş durumu: ' . $statusText,
            'Ürünler:',
        ];

        $items = $order['items'] ?? [];
        $hasItem = false;
        if (is_array($items)) {
            foreach ($items as $item) {
                if (($item['status'] ?? '') === 'cancelled') {
                    continue;
                }
                $hasItem = true;
                $lines[] = '• ' . (int) ($item['quantity'] ?? 1) . '× ' . (string) ($item['item_name'] ?? '');
            }
        }
        if (!$hasItem) {
            $lines[] = '• —';
        }

        $lines[] = 'Not: ' . ($note !== '' ? $note : '—');
        $lines[] = 'Teşekkürler, afiyet olsun!';

        return implode("\n", $lines);
    }

    public static function chatUrl(string $phoneDigits, string $message): string
    {
        if ($phoneDigits === '') {
            return '';
        }
        return 'https://wa.me/' . $phoneDigits . '?text=' . rawurlencode($message);
    }

    public static function orderChatUrl(array $order): string
    {
        $to = self::notifyNumber();
        if ($to === '') {
            return '';
        }
        return self::chatUrl($to, self::buildOrderMessage($order));
    }

    /** Yeni online sipariş: kuyruğa al + Cloud API dene */
    public static function notifyNewOnlineOrder(array $order): void
    {
        if (!self::isEnabled()) {
            return;
        }
        $orderId = (int) ($order['id'] ?? 0);
        if ($orderId < 1) {
            return;
        }
        $url = self::orderChatUrl($order);
        BrochureService::setSetting('whatsapp_pending_order_id', (string) $orderId);
        BrochureService::setSetting('whatsapp_pending_url', $url);
        BrochureService::setSetting('whatsapp_pending_at', date('c'));
        BrochureService::setSetting('whatsapp_pending_code', (string) ($order['order_code'] ?? ''));

        self::tryCloudApi(self::buildOrderMessage($order));
    }

    public static function pendingPayload(): ?array
    {
        if (!self::isEnabled()) {
            return null;
        }
        $id = (int) BrochureService::getSetting('whatsapp_pending_order_id', '0');
        if ($id < 1) {
            return null;
        }
        $url = (string) BrochureService::getSetting('whatsapp_pending_url', '');
        return [
            'order_id' => $id,
            'order_code' => (string) BrochureService::getSetting('whatsapp_pending_code', ''),
            'url' => $url,
            'auto_open' => self::autoOpen(),
            'at' => (string) BrochureService::getSetting('whatsapp_pending_at', ''),
        ];
    }

    private static function tryCloudApi(string $message): void
    {
        $token = trim((string) BrochureService::getSetting('whatsapp_api_token', ''));
        $phoneId = trim((string) BrochureService::getSetting('whatsapp_phone_number_id', ''));
        $to = self::notifyNumber();
        if ($token === '' || $phoneId === '' || $to === '') {
            return;
        }
        $endpoint = 'https://graph.facebook.com/v19.0/' . rawurlencode($phoneId) . '/messages';
        $body = json_encode([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message],
        ], JSON_UNESCAPED_UNICODE);
        if ($body === false) {
            return;
        }
        if (!function_exists('curl_init')) {
            return;
        }
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => 12,
        ]);
        $resp = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code >= 400) {
            error_log('WhatsApp Cloud API HTTP ' . $code . ' ' . (string) $resp);
        }
    }
}

<?php

declare(strict_types=1);

final class BrochureService
{
    public const SETTING_SELECTED = 'brochure_theme';
    public const SETTING_STATUS = 'brochure_themes_status';

    /**
     * @return array<string, array{id:string,name:string,blurb:string,default_active:bool,layout:string}>
     */
    public static function catalog(): array
    {
        return [
            'classic' => [
                'id' => 'classic',
                'name' => 'Klasik Açık',
                'blurb' => 'Beyaz zemin, turuncu vurgu — sade ve okunaklı.',
                'default_active' => true,
                'layout' => 'list',
            ],
            'ember' => [
                'id' => 'ember',
                'name' => 'Ember Izgara',
                'blurb' => 'Sıcak amber tonlar, güçlü fiyat vurgusu.',
                'default_active' => true,
                'layout' => 'list',
            ],
            'garden' => [
                'id' => 'garden',
                'name' => 'Bahçe Ferah',
                'blurb' => 'Açık yeşil-mint, hafif ve ferah görünüm.',
                'default_active' => true,
                'layout' => 'list',
            ],
            'slate' => [
                'id' => 'slate',
                'name' => 'Slate Modern',
                'blurb' => 'Mavi-gri modern çizgi, net tipografi.',
                'default_active' => true,
                'layout' => 'list',
            ],
            'noir' => [
                'id' => 'noir',
                'name' => 'Noir Gece',
                'blurb' => 'Koyu zemin, altın vurgu — akşam menüsü hissi.',
                'default_active' => true,
                'layout' => 'list',
            ],
            'restoran' => [
                'id' => 'restoran',
                'name' => 'Restoran Broşürü',
                'blurb' => 'Izgara dumanı ve köz ateşi — klasik restoran menü hissi.',
                'default_active' => true,
                'layout' => 'list',
            ],
            'modern' => [
                'id' => 'modern',
                'name' => 'Modern Broşür',
                'blurb' => 'Keskin tipografi, ferah boşluk, çağdaş çizgi.',
                'default_active' => true,
                'layout' => 'list',
            ],
            'premium' => [
                'id' => 'premium',
                'name' => 'Premium Broşür',
                'blurb' => 'Mürekkep zemin, şampanya altın — lüks akşam menüsü.',
                'default_active' => true,
                'layout' => 'list',
            ],
            'cizgili' => [
                'id' => 'cizgili',
                'name' => 'Çizgili Broşür',
                'blurb' => 'Çizgili doku ve net ayırıcılar — ritmik menü düzeni.',
                'default_active' => true,
                'layout' => 'list',
            ],
            'efekli' => [
                'id' => 'efekli',
                'name' => 'Efekli Broşür',
                'blurb' => 'Hareketli ışık, parıltı ve yumuşak geçiş efektleri.',
                'default_active' => true,
                'layout' => 'list',
            ],
            // Farklı düzenler
            'kartlar' => [
                'id' => 'kartlar',
                'name' => 'Kart Menü',
                'blurb' => 'İki sütun kart düzeni — görsel odaklı modern menü.',
                'default_active' => true,
                'layout' => 'grid',
            ],
            'dergi' => [
                'id' => 'dergi',
                'name' => 'Dergi Broşürü',
                'blurb' => 'Kapak ürünü + dergi sütunları — editöryal düzen.',
                'default_active' => true,
                'layout' => 'magazine',
            ],
            'tahta' => [
                'id' => 'tahta',
                'name' => 'Menü Tahtası',
                'blurb' => 'Kara tahta stili, noktalı fiyat satırları.',
                'default_active' => true,
                'layout' => 'board',
            ],
            'galeri' => [
                'id' => 'galeri',
                'name' => 'Galeri Broşürü',
                'blurb' => 'Büyük görsel karolar — ürün fotoğrafı önde.',
                'default_active' => true,
                'layout' => 'gallery',
            ],
            'yanmenu' => [
                'id' => 'yanmenu',
                'name' => 'Yan Menü',
                'blurb' => 'Üstte kategori şeridi + akıcı ürün listesi.',
                'default_active' => true,
                'layout' => 'split',
            ],
        ];
    }

    public static function themeLayout(string $themeId): string
    {
        $catalog = self::catalog();
        $layout = (string) ($catalog[$themeId]['layout'] ?? 'list');
        $allowed = ['list', 'grid', 'magazine', 'board', 'gallery', 'split'];
        return in_array($layout, $allowed, true) ? $layout : 'list';
    }

    public static function getSetting(string $key, ?string $default = null): ?string
    {
        try {
            $stmt = Database::pdo()->prepare(
                'SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1'
            );
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            if ($row) {
                return (string) $row['setting_value'];
            }
        } catch (Throwable) {
        }
        return $default;
    }

    public static function setSetting(string $key, string $value): void
    {
        Database::pdo()->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        )->execute([$key, $value]);
    }

    /** @return array<string, bool> */
    public static function statusMap(): array
    {
        $catalog = self::catalog();
        $defaults = [];
        foreach ($catalog as $id => $meta) {
            $defaults[$id] = !empty($meta['default_active']);
        }
        $raw = self::getSetting(self::SETTING_STATUS, '');
        if ($raw === null || $raw === '') {
            return $defaults;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $defaults;
        }
        foreach ($catalog as $id => $_) {
            if (array_key_exists($id, $decoded)) {
                $defaults[$id] = (bool) $decoded[$id];
            }
        }
        return $defaults;
    }

    public static function setThemeActive(string $themeId, bool $active): void
    {
        $catalog = self::catalog();
        if (!isset($catalog[$themeId])) {
            throw new InvalidArgumentException('Tema bulunamadı.');
        }
        $map = self::statusMap();
        $map[$themeId] = $active;
        // En az bir aktif tema kalsın
        if (!$active) {
            $any = false;
            foreach ($map as $on) {
                if ($on) {
                    $any = true;
                    break;
                }
            }
            if (!$any) {
                throw new InvalidArgumentException('En az bir broşür teması aktif olmalı.');
            }
        }
        self::setSetting(self::SETTING_STATUS, json_encode($map, JSON_UNESCAPED_UNICODE));
        if (!$active && self::selectedThemeId() === $themeId) {
            foreach ($map as $id => $on) {
                if ($on) {
                    self::setSetting(self::SETTING_SELECTED, $id);
                    break;
                }
            }
        }
    }

    public static function selectedThemeId(): string
    {
        $id = (string) (self::getSetting(self::SETTING_SELECTED, 'classic') ?: 'classic');
        $catalog = self::catalog();
        $status = self::statusMap();
        if (!isset($catalog[$id]) || empty($status[$id])) {
            foreach ($status as $tid => $on) {
                if ($on && isset($catalog[$tid])) {
                    return $tid;
                }
            }
            return 'classic';
        }
        return $id;
    }

    public static function selectTheme(string $themeId): void
    {
        $catalog = self::catalog();
        $status = self::statusMap();
        if (!isset($catalog[$themeId])) {
            throw new InvalidArgumentException('Tema bulunamadı.');
        }
        if (empty($status[$themeId])) {
            throw new InvalidArgumentException('Pasif tema seçilemez. Önce aktifleştirin.');
        }
        self::setSetting(self::SETTING_SELECTED, $themeId);
    }

    /** @return list<array{id:string,name:string,blurb:string,layout:string,is_active:bool,is_selected:bool}> */
    public static function themesForAdmin(): array
    {
        $status = self::statusMap();
        $selected = self::selectedThemeId();
        $layoutLabels = [
            'list' => 'Liste',
            'grid' => 'Kart',
            'magazine' => 'Dergi',
            'board' => 'Tahta',
            'gallery' => 'Galeri',
            'split' => 'Yan menü',
        ];
        $out = [];
        foreach (self::catalog() as $id => $meta) {
            $layout = (string) ($meta['layout'] ?? 'list');
            $out[] = [
                'id' => $id,
                'name' => $meta['name'],
                'blurb' => $meta['blurb'],
                'layout' => $layout,
                'layout_label' => $layoutLabels[$layout] ?? $layout,
                'is_active' => !empty($status[$id]),
                'is_selected' => $id === $selected,
            ];
        }
        return $out;
    }

    public static function brochurePublicUrl(): string
    {
        return self::publicBaseUrl() . '/menu/brosur';
    }

    public static function publicBaseUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        if ($host !== '') {
            return $scheme . '://' . $host . base_path();
        }
        return rtrim((string) config('app_url'), '/');
    }

    /** @return array{0:string,1:string}|null [absolute filesystem path, public absolute URL] */
    public static function logoAsset(): ?array
    {
        $candidates = [
            '/assets/img/brand-crisp-co-v6.png',
            '/assets/img/brand-crisp-co-v5.png',
            '/assets/img/brand-crisp-co-v4.png',
            '/assets/img/brand-crisp-co-v3.png',
            '/assets/img/logo-crisp-co.png',
            '/assets/img/logo-crisp.png',
            '/assets/img/logo-mark.png',
            '/assets/img/logo.png',
        ];
        $root = dirname(__DIR__);
        foreach ($candidates as $rel) {
            $path = $root . $rel;
            if (is_file($path)) {
                return [$path, self::publicBaseUrl() . $rel];
            }
        }
        return null;
    }

    /**
     * Ekranda gösterim için yüksek ECC QR (logo CSS ile ortalanır).
     */
    public static function qrImageUrl(?string $brochureUrl = null, int $size = 320): string
    {
        return self::plainQrRemoteUrl($brochureUrl, $size);
    }

    /** İndirme / yazdırma için logosu gömülü PNG */
    public static function qrBrandedDownloadUrl(int $size = 320): string
    {
        $size = max(120, min(800, $size));
        return url('/qr/brosur.png') . '?size=' . $size;
    }

    /** Düz (logosuz) yüksek ECC QR URL */
    public static function plainQrRemoteUrl(?string $brochureUrl = null, int $size = 320): string
    {
        $size = max(120, min(800, $size));
        $data = $brochureUrl ?? self::brochurePublicUrl();
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size
            . '&ecc=H&margin=8&data=' . rawurlencode($data);
    }

    public static function quickChartQrUrl(?string $brochureUrl = null, int $size = 320): string
    {
        $size = max(120, min(800, $size));
        $data = $brochureUrl ?? self::brochurePublicUrl();
        $params = [
            'text' => $data,
            'size' => $size,
            'margin' => 2,
            'ecLevel' => 'H',
            'dark' => '14110e',
            'light' => 'ffffff',
        ];
        $logo = self::logoAsset();
        if ($logo) {
            $params['centerImageUrl'] = $logo[1];
            $params['centerImageSizeRatio'] = 0.28;
        }
        return 'https://quickchart.io/qr?' . http_build_query($params);
    }

    private static function fetchBinary(string $url): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return null;
            }
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 6,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_USERAGENT => 'CrispCo-QR/1.0',
            ]);
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($body !== false && $code >= 200 && $code < 300 && $body !== '') {
                return $body;
            }
        }
        $ctx = stream_context_create([
            'http' => ['timeout' => 12, 'header' => "User-Agent: CrispCo-QR/1.0\r\n"],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        return ($body !== false && $body !== '') ? $body : null;
    }

    /**
     * Broşür QR PNG üretir (ortada logo). Çıktıyı doğrudan gönderir.
     */
    public static function outputBrandedQrPng(int $size = 320): void
    {
        $size = max(120, min(800, $size));
        $brochureUrl = self::brochurePublicUrl();
        $logo = self::logoAsset();

        // 1) GD ile yerelde logo göm
        if ($logo && function_exists('imagecreatefromstring') && function_exists('imagecreatetruecolor')) {
            $qrBin = self::fetchBinary(self::plainQrRemoteUrl($brochureUrl, $size));
            if ($qrBin !== null) {
                $qr = @imagecreatefromstring($qrBin);
                $mark = @imagecreatefrompng($logo[0]);
                if ($qr !== false && $mark !== false) {
                    $qrW = imagesx($qr);
                    $qrH = imagesy($qr);
                    $logoTarget = (int) max(24, round(min($qrW, $qrH) * 0.26));
                    $pad = (int) max(4, round($logoTarget * 0.12));
                    $box = $logoTarget + ($pad * 2);
                    $x0 = (int) (($qrW - $box) / 2);
                    $y0 = (int) (($qrH - $box) / 2);

                    // Beyaz yuvarlak/kare zemin (okunabilirlik)
                    $white = imagecolorallocate($qr, 255, 255, 255);
                    imagefilledrectangle($qr, $x0, $y0, $x0 + $box - 1, $y0 + $box - 1, $white);

                    $srcW = imagesx($mark);
                    $srcH = imagesy($mark);
                    imagecopyresampled(
                        $qr,
                        $mark,
                        $x0 + $pad,
                        $y0 + $pad,
                        0,
                        0,
                        $logoTarget,
                        $logoTarget,
                        $srcW,
                        $srcH
                    );

                    header('Content-Type: image/png');
                    header('Cache-Control: public, max-age=300');
                    header('X-QR-Brand: gd');
                    imagepng($qr);
                    imagedestroy($qr);
                    imagedestroy($mark);
                    return;
                }
                if (isset($qr) && ($qr instanceof \GdImage || is_resource($qr))) {
                    imagedestroy($qr);
                }
                if (isset($mark) && ($mark instanceof \GdImage || is_resource($mark))) {
                    imagedestroy($mark);
                }
            }
        }

        // 2) QuickChart merkez logolu QR
        $qc = self::fetchBinary(self::quickChartQrUrl($brochureUrl, $size));
        if ($qc !== null) {
            header('Content-Type: image/png');
            header('Cache-Control: public, max-age=300');
            header('X-QR-Brand: quickchart');
            echo $qc;
            return;
        }

        // 3) Son çare: düz QR’a yönlendir
        header('Location: ' . self::plainQrRemoteUrl($brochureUrl, $size), true, 302);
    }
}

<?php

declare(strict_types=1);

final class BrochureService
{
    public const SETTING_SELECTED = 'brochure_theme';
    public const SETTING_STATUS = 'brochure_themes_status';

    /** @return array<string, array{id:string,name:string,blurb:string,default_active:bool}> */
    public static function catalog(): array
    {
        return [
            'classic' => [
                'id' => 'classic',
                'name' => 'Klasik Açık',
                'blurb' => 'Beyaz zemin, turuncu vurgu — sade ve okunaklı.',
                'default_active' => true,
            ],
            'ember' => [
                'id' => 'ember',
                'name' => 'Ember Izgara',
                'blurb' => 'Sıcak amber tonlar, güçlü fiyat vurgusu.',
                'default_active' => true,
            ],
            'garden' => [
                'id' => 'garden',
                'name' => 'Bahçe Ferah',
                'blurb' => 'Açık yeşil-mint, hafif ve ferah görünüm.',
                'default_active' => true,
            ],
            'slate' => [
                'id' => 'slate',
                'name' => 'Slate Modern',
                'blurb' => 'Mavi-gri modern çizgi, net tipografi.',
                'default_active' => true,
            ],
            'noir' => [
                'id' => 'noir',
                'name' => 'Noir Gece',
                'blurb' => 'Koyu zemin, altın vurgu — akşam menüsü hissi.',
                'default_active' => true,
            ],
            'restoran' => [
                'id' => 'restoran',
                'name' => 'Restoran Broşürü',
                'blurb' => 'Izgara dumanı ve köz ateşi — klasik restoran menü hissi.',
                'default_active' => true,
            ],
            'modern' => [
                'id' => 'modern',
                'name' => 'Modern Broşür',
                'blurb' => 'Keskin tipografi, ferah boşluk, çağdaş çizgi.',
                'default_active' => true,
            ],
            'premium' => [
                'id' => 'premium',
                'name' => 'Premium Broşür',
                'blurb' => 'Mürekkep zemin, şampanya altın — lüks akşam menüsü.',
                'default_active' => true,
            ],
            'cizgili' => [
                'id' => 'cizgili',
                'name' => 'Çizgili Broşür',
                'blurb' => 'Çizgili doku ve net ayırıcılar — ritmik menü düzeni.',
                'default_active' => true,
            ],
            'efekli' => [
                'id' => 'efekli',
                'name' => 'Efekli Broşür',
                'blurb' => 'Hareketli ışık, parıltı ve yumuşak geçiş efektleri.',
                'default_active' => true,
            ],
        ];
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

    /** @return list<array{id:string,name:string,blurb:string,is_active:bool,is_selected:bool}> */
    public static function themesForAdmin(): array
    {
        $status = self::statusMap();
        $selected = self::selectedThemeId();
        $out = [];
        foreach (self::catalog() as $id => $meta) {
            $out[] = [
                'id' => $id,
                'name' => $meta['name'],
                'blurb' => $meta['blurb'],
                'is_active' => !empty($status[$id]),
                'is_selected' => $id === $selected,
            ];
        }
        return $out;
    }

    public static function brochurePublicUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        $base = $host !== ''
            ? ($scheme . '://' . $host . base_path())
            : rtrim((string) config('app_url'), '/');
        return $base . '/menu/brosur';
    }

    public static function qrImageUrl(?string $brochureUrl = null, int $size = 320): string
    {
        $url = $brochureUrl ?? self::brochurePublicUrl();
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size
            . '&data=' . rawurlencode($url);
    }
}

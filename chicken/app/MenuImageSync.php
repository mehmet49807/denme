<?php

declare(strict_types=1);

/**
 * Ensures menu items have local product photos.
 */
final class MenuImageSync
{
    public static function catalog(): array
    {
        return [
            'Izgara Tavuk Şiş' => '/assets/img/menu/izgara-tavuk-sis.jpg',
            'Acılı Kanat' => '/assets/img/menu/acili-kanat.jpg',
            'Acı Kanat' => '/assets/img/menu/acili-kanat.jpg',
            'Ballı Hardallı Tavuk' => '/assets/img/menu/balli-hardalli-tavuk.jpg',
            'Chicken Menü' => '/assets/img/menu/chicken-menu.jpg',
            'Aile Menüsü' => '/assets/img/menu/aile-menusu.jpg',
            'Tavuk Dürüm' => '/assets/img/menu/tavuk-durum.jpg',
            'Köfte Dürüm' => '/assets/img/menu/kofte-durum.jpg',
            'Chicken Burger' => '/assets/img/menu/chicken-burger.jpg',
            'Cheese Burger' => '/assets/img/menu/cheese-burger.jpg',
            'Çıtır Patates' => '/assets/img/menu/citir-patates.jpg',
            'Soğan Halkası' => '/assets/img/menu/sogan-halkasi.jpg',
            'Coleslaw' => '/assets/img/menu/coleslaw.jpg',
            'Coleslow' => '/assets/img/menu/coleslaw.jpg',
            'Sufle' => '/assets/img/menu/sufle.jpg',
            'Dondurma' => '/assets/img/menu/dondurma.jpg',
            'Ayran' => '/assets/img/menu/ayran.jpg',
            'Kola' => '/assets/img/menu/kola.jpg',
            'Limonata' => '/assets/img/menu/limonata.jpg',
            'Su' => '/assets/img/menu/su.jpg',
        ];
    }

    public static function normalize(string $name): string
    {
        $name = trim(mb_strtolower($name, 'UTF-8'));
        $map = [
            'ı' => 'i', 'İ' => 'i', 'ş' => 's', 'Ş' => 's', 'ğ' => 'g', 'Ğ' => 'g',
            'ü' => 'u', 'Ü' => 'u', 'ö' => 'o', 'Ö' => 'o', 'ç' => 'c', 'Ç' => 'c',
        ];
        $name = strtr($name, $map);
        $name = preg_replace('/[^a-z0-9]+/', '', $name) ?? '';
        return $name;
    }

    public static function resolve(?array $item): string
    {
        if (!$item) {
            return '';
        }

        $current = trim((string) ($item['image_url'] ?? ''));
        if ($current !== '' && self::fileExists($current)) {
            return $current;
        }

        $name = trim((string) ($item['name'] ?? ''));
        if ($name === '') {
            return $current;
        }

        $catalog = self::catalog();
        if (isset($catalog[$name])) {
            return $catalog[$name];
        }

        $needle = self::normalize($name);
        // Treat "aci kanat" like "acili kanat"
        if ($needle === 'acikanat') {
            $needle = 'acilikanat';
        }
        if ($needle === 'coleslow') {
            $needle = 'coleslaw';
        }

        foreach ($catalog as $label => $path) {
            if (self::normalize($label) === $needle) {
                return $path;
            }
        }

        return $current;
    }

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
            $rows = $pdo->query('SELECT id, name, image_url FROM menu_items')->fetchAll();
        } catch (Throwable) {
            return;
        }

        $update = $pdo->prepare('UPDATE menu_items SET image_url = ? WHERE id = ?');
        foreach ($rows as $row) {
            $resolved = self::resolve($row);
            if ($resolved === '' || $resolved === (string) ($row['image_url'] ?? '')) {
                continue;
            }
            try {
                $update->execute([$resolved, (int) $row['id']]);
            } catch (Throwable) {
            }
        }

        // Also force known catalog names
        $byName = $pdo->prepare('UPDATE menu_items SET image_url = ? WHERE name = ?');
        foreach (self::catalog() as $name => $path) {
            try {
                $byName->execute([$path, $name]);
            } catch (Throwable) {
            }
        }
    }

    private static function fileExists(string $path): bool
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return true;
        }
        $relative = str_starts_with($path, '/') ? $path : '/' . $path;
        return is_file(dirname(__DIR__) . $relative);
    }
}

<?php

declare(strict_types=1);

/**
 * Ensures seeded menu items have local product photos.
 */
final class MenuImageSync
{
    public static function catalog(): array
    {
        return [
            'Izgara Tavuk Şiş' => '/assets/img/menu/izgara-tavuk-sis.jpg',
            'Acılı Kanat' => '/assets/img/menu/acili-kanat.jpg',
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
            'Sufle' => '/assets/img/menu/sufle.jpg',
            'Dondurma' => '/assets/img/menu/dondurma.jpg',
            'Ayran' => '/assets/img/menu/ayran.jpg',
            'Kola' => '/assets/img/menu/kola.jpg',
            'Limonata' => '/assets/img/menu/limonata.jpg',
            'Su' => '/assets/img/menu/su.jpg',
        ];
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

        $stmt = $pdo->prepare('UPDATE menu_items SET image_url = ? WHERE name = ?');
        foreach (self::catalog() as $name => $path) {
            try {
                $stmt->execute([$path, $name]);
            } catch (Throwable) {
            }
        }
    }
}

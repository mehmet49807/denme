<?php

declare(strict_types=1);

/**
 * Ensures catalog menu items exist (insert missing products by name).
 * Prices reflect Antalya mid-premium chicken restaurant levels (2026).
 */
final class MenuItemSync
{
    public static function catalog(): array
    {
        return [
            // Izgara
            [
                'category_slug' => 'izgara',
                'name' => 'Izgara Tavuk Şiş',
                'description' => 'Marine edilmiş tavuk şiş, köz biber',
                'price' => 420.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/izgara-tavuk-sis.jpg',
                'sort_order' => 1,
            ],
            [
                'category_slug' => 'izgara',
                'name' => 'Acılı Kanat',
                'description' => '8 adet acılı ızgara kanat',
                'price' => 390.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/acili-kanat.jpg',
                'sort_order' => 2,
            ],
            [
                'category_slug' => 'izgara',
                'name' => 'Ballı Hardallı Tavuk',
                'description' => 'Ballı hardal glaze, yeşillik',
                'price' => 445.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/balli-hardalli-tavuk.jpg',
                'sort_order' => 3,
            ],
            // Menüler
            [
                'category_slug' => 'menuler',
                'name' => 'Chicken Menü',
                'description' => 'Burger + patates + içecek',
                'price' => 520.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/chicken-menu.jpg',
                'sort_order' => 1,
            ],
            [
                'category_slug' => 'menuler',
                'name' => 'Aile Menüsü',
                'description' => '2 şiş + 2 kanat + patates + 4 içecek',
                'price' => 1450.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/aile-menusu.jpg',
                'sort_order' => 2,
            ],
            // Dürümler
            [
                'category_slug' => 'durumler',
                'name' => 'Tavuk Dürüm',
                'description' => 'Izgara tavuk, lavaş, turşu',
                'price' => 280.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/tavuk-durum.jpg',
                'sort_order' => 1,
            ],
            [
                'category_slug' => 'durumler',
                'name' => 'Köfte Dürüm',
                'description' => 'Izgara köfte, sos, yeşillik',
                'price' => 310.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/kofte-durum.jpg',
                'sort_order' => 2,
            ],
            [
                'category_slug' => 'durumler',
                'name' => 'Kaşarlı Tavuk Dürüm',
                'description' => 'Izgara tavuk, kaşar, lavaş',
                'price' => 320.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/kasarli-tavuk-durum.jpg',
                'sort_order' => 3,
            ],
            [
                'category_slug' => 'durumler',
                'name' => 'Et Dürüm',
                'description' => 'Izgara et, sos, yeşillik',
                'price' => 380.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/et-durum.jpg',
                'sort_order' => 4,
            ],
            [
                'category_slug' => 'durumler',
                'name' => 'Karışık Dürüm',
                'description' => 'Tavuk + köfte, özel sos',
                'price' => 350.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/karisik-durum.jpg',
                'sort_order' => 5,
            ],
            // Burgerler
            [
                'category_slug' => 'burgerler',
                'name' => 'Chicken Burger',
                'description' => 'Özel sos, çıtır tavuk, turşu',
                'price' => 310.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/chicken-burger.jpg',
                'sort_order' => 1,
            ],
            [
                'category_slug' => 'burgerler',
                'name' => 'Cheese Burger',
                'description' => 'Cheddar, özel sos',
                'price' => 340.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/cheese-burger.jpg',
                'sort_order' => 2,
            ],
            [
                'category_slug' => 'burgerler',
                'name' => 'Double Chicken Burger',
                'description' => 'Çift kat çıtır tavuk, özel sos',
                'price' => 420.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/double-chicken-burger.jpg',
                'sort_order' => 3,
            ],
            [
                'category_slug' => 'burgerler',
                'name' => 'BBQ Burger',
                'description' => 'BBQ sos, çıtır soğan',
                'price' => 360.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/bbq-burger.jpg',
                'sort_order' => 4,
            ],
            [
                'category_slug' => 'burgerler',
                'name' => 'Acılı Burger',
                'description' => 'Acılı sos, jalapeno, çıtır tavuk',
                'price' => 355.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/acili-burger.jpg',
                'sort_order' => 5,
            ],
            // Yan ürünler
            [
                'category_slug' => 'yan-urunler',
                'name' => 'Çıtır Patates',
                'description' => 'Ev yapımı baharatlı patates',
                'price' => 140.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/citir-patates.jpg',
                'sort_order' => 1,
            ],
            [
                'category_slug' => 'yan-urunler',
                'name' => 'Soğan Halkası',
                'description' => '6 adet çıtır soğan halkası',
                'price' => 150.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/sogan-halkasi.jpg',
                'sort_order' => 2,
            ],
            [
                'category_slug' => 'yan-urunler',
                'name' => 'Coleslaw',
                'description' => 'Taze lahana salatası',
                'price' => 110.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/coleslaw.jpg',
                'sort_order' => 3,
            ],
            // Tatlılar
            [
                'category_slug' => 'tatlilar',
                'name' => 'Sufle',
                'description' => 'Sıcak çikolatalı sufle',
                'price' => 190.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/sufle.jpg',
                'sort_order' => 1,
            ],
            [
                'category_slug' => 'tatlilar',
                'name' => 'Dondurma',
                'description' => '2 top',
                'price' => 130.00,
                'station' => 'bar',
                'image_url' => '/assets/img/menu/dondurma.jpg',
                'sort_order' => 2,
            ],
            [
                'category_slug' => 'tatlilar',
                'name' => 'Fırın Sütlaç',
                'description' => 'Fırında pişmiş klasik sütlaç',
                'price' => 160.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/firin-sutlac.jpg',
                'sort_order' => 3,
            ],
            [
                'category_slug' => 'tatlilar',
                'name' => 'Muzlu Supangle',
                'description' => 'Muzlu çikolatalı supangle',
                'price' => 170.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/muzlu-supangle.jpg',
                'sort_order' => 4,
            ],
            [
                'category_slug' => 'tatlilar',
                'name' => 'Baklava',
                'description' => 'Antep fıstıklı baklava',
                'price' => 210.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/baklava.jpg',
                'sort_order' => 5,
            ],
            [
                'category_slug' => 'tatlilar',
                'name' => 'Cheesecake',
                'description' => 'New York usulü cheesecake',
                'price' => 200.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/cheesecake.jpg',
                'sort_order' => 6,
            ],
            [
                'category_slug' => 'tatlilar',
                'name' => 'San Sebastian',
                'description' => 'Yanık cheesecake dilimi',
                'price' => 220.00,
                'station' => 'kitchen',
                'image_url' => '/assets/img/menu/san-sebastian.jpg',
                'sort_order' => 7,
            ],
            // İçecekler
            [
                'category_slug' => 'tum-icecekler',
                'name' => 'Ayran',
                'description' => '300 ml',
                'price' => 60.00,
                'station' => 'bar',
                'image_url' => '/assets/img/menu/ayran.jpg',
                'sort_order' => 1,
            ],
            [
                'category_slug' => 'tum-icecekler',
                'name' => 'Kola',
                'description' => '330 ml',
                'price' => 75.00,
                'station' => 'bar',
                'image_url' => '/assets/img/menu/kola.jpg',
                'sort_order' => 2,
            ],
            [
                'category_slug' => 'tum-icecekler',
                'name' => 'Fanta',
                'description' => '330 ml',
                'price' => 75.00,
                'station' => 'bar',
                'image_url' => '/assets/img/menu/fanta.jpg',
                'sort_order' => 3,
            ],
            [
                'category_slug' => 'tum-icecekler',
                'name' => 'Sprite',
                'description' => '330 ml',
                'price' => 75.00,
                'station' => 'bar',
                'image_url' => '/assets/img/menu/sprite.jpg',
                'sort_order' => 4,
            ],
            [
                'category_slug' => 'tum-icecekler',
                'name' => 'Soda',
                'description' => '200 ml',
                'price' => 50.00,
                'station' => 'bar',
                'image_url' => '/assets/img/menu/soda.jpg',
                'sort_order' => 5,
            ],
            [
                'category_slug' => 'tum-icecekler',
                'name' => 'Şalgam Acılı',
                'description' => 'Acılı şalgam suyu',
                'price' => 65.00,
                'station' => 'bar',
                'image_url' => '/assets/img/menu/salgam-acili.jpg',
                'sort_order' => 6,
            ],
            [
                'category_slug' => 'tum-icecekler',
                'name' => 'Şalgam Acısız',
                'description' => 'Acısız şalgam suyu',
                'price' => 65.00,
                'station' => 'bar',
                'image_url' => '/assets/img/menu/salgam-acisiz.jpg',
                'sort_order' => 7,
            ],
            [
                'category_slug' => 'tum-icecekler',
                'name' => 'Limonata',
                'description' => 'Ev yapımı',
                'price' => 90.00,
                'station' => 'bar',
                'image_url' => '/assets/img/menu/limonata.jpg',
                'sort_order' => 8,
            ],
            [
                'category_slug' => 'tum-icecekler',
                'name' => 'Su',
                'description' => '0.5 L',
                'price' => 35.00,
                'station' => 'bar',
                'image_url' => '/assets/img/menu/su.jpg',
                'sort_order' => 9,
            ],
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

        try {
            $catStmt = $pdo->query('SELECT id, slug FROM categories');
            $cats = [];
            foreach ($catStmt->fetchAll() as $row) {
                $cats[(string) $row['slug']] = (int) $row['id'];
            }
        } catch (Throwable) {
            return;
        }

        $find = $pdo->prepare('SELECT id FROM menu_items WHERE name = ? LIMIT 1');
        $insert = $pdo->prepare(
            'INSERT INTO menu_items
             (category_id, name, description, price, station, is_available, image_url, sort_order)
             VALUES (?, ?, ?, ?, ?, 1, ?, ?)'
        );
        $update = $pdo->prepare(
            'UPDATE menu_items
             SET category_id = ?, description = ?, price = ?, station = ?, image_url = ?, sort_order = ?, is_available = 1
             WHERE id = ?'
        );

        foreach (self::catalog() as $item) {
            $slug = (string) $item['category_slug'];
            if (!isset($cats[$slug])) {
                continue;
            }
            $categoryId = $cats[$slug];
            $name = (string) $item['name'];
            try {
                $find->execute([$name]);
                $id = $find->fetchColumn();
                if ($id) {
                    $update->execute([
                        $categoryId,
                        $item['description'],
                        $item['price'],
                        $item['station'],
                        $item['image_url'],
                        $item['sort_order'],
                        (int) $id,
                    ]);
                } else {
                    $insert->execute([
                        $categoryId,
                        $name,
                        $item['description'],
                        $item['price'],
                        $item['station'],
                        $item['image_url'],
                        $item['sort_order'],
                    ]);
                }
            } catch (Throwable) {
            }
        }
    }
}

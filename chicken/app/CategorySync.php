<?php

declare(strict_types=1);

final class CategorySync
{
    /** @return list<array{name:string,slug:string,sort_order:int,icon:string,color:string}> */
    public static function catalog(): array
    {
        return [
            ['name' => 'Izgara', 'slug' => 'izgara', 'sort_order' => 1, 'icon' => 'grill', 'color' => '#ff6a1a'],
            ['name' => 'Menüler', 'slug' => 'menuler', 'sort_order' => 2, 'icon' => 'menu', 'color' => '#3d9a6a'],
            ['name' => 'Dürümler', 'slug' => 'durumler', 'sort_order' => 3, 'icon' => 'wrap', 'color' => '#e0a33b'],
            ['name' => 'Burgerler', 'slug' => 'burgerler', 'sort_order' => 4, 'icon' => 'burger', 'color' => '#f0b429'],
            ['name' => 'Yan ürünler', 'slug' => 'yan-urunler', 'sort_order' => 5, 'icon' => 'sides', 'color' => '#2bb3a3'],
            ['name' => 'Tatlılar', 'slug' => 'tatlilar', 'sort_order' => 6, 'icon' => 'dessert', 'color' => '#e85d8a'],
            ['name' => 'Tüm İçecekler', 'slug' => 'tum-icecekler', 'sort_order' => 7, 'icon' => 'drinks', 'color' => '#4c8dff'],
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

        // Rename legacy slugs first
        $renames = [
            'yan-lezzetler' => 'yan-urunler',
            'icecekler' => 'tum-icecekler',
        ];
        foreach ($renames as $from => $to) {
            try {
                $exists = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
                $exists->execute([$to]);
                if ($exists->fetch()) {
                    // Target exists; move items then deactivate old
                    $pdo->prepare(
                        'UPDATE menu_items SET category_id = (SELECT id FROM categories WHERE slug = ? LIMIT 1)
                         WHERE category_id = (SELECT id FROM (SELECT id FROM categories WHERE slug = ? LIMIT 1) AS oldcat)'
                    )->execute([$to, $from]);
                    $pdo->prepare('UPDATE categories SET is_active = 0 WHERE slug = ?')->execute([$from]);
                } else {
                    $pdo->prepare('UPDATE categories SET slug = ? WHERE slug = ?')->execute([$to, $from]);
                }
            } catch (Throwable) {
                // ignore one-off sync errors
            }
        }

        $upsert = $pdo->prepare(
            'INSERT INTO categories (name, slug, sort_order, is_active)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE
               name = VALUES(name),
               sort_order = VALUES(sort_order),
               is_active = 1'
        );
        foreach (self::catalog() as $row) {
            try {
                $upsert->execute([$row['name'], $row['slug'], $row['sort_order']]);
            } catch (Throwable) {
                // continue
            }
        }
    }

    public static function meta(string $slug): array
    {
        foreach (self::catalog() as $row) {
            if ($row['slug'] === $slug) {
                return $row;
            }
        }
        return ['name' => $slug, 'slug' => $slug, 'icon' => 'all', 'color' => '#a7aea6'];
    }
}

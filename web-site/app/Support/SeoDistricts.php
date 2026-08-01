<?php

namespace App\Support;

/**
 * Büyük şehirlerde SEO ilçe sayfaları (sitemap + /sehir/{city}/{district}).
 */
final class SeoDistricts
{
    /**
     * @return array<string, list<string>> citySlug => district display names
     */
    public static function map(): array
    {
        return [
            'istanbul' => [
                'Kadıköy', 'Beşiktaş', 'Üsküdar', 'Bakırköy', 'Şişli',
                'Ataşehir', 'Pendik', 'Maltepe', 'Beylikdüzü', 'Başakşehir',
                'Ümraniye', 'Kartal', 'Sarıyer', 'Fatih', 'Eyüpsultan',
                'Avcılar', 'Bahçelievler', 'Küçükçekmece', 'Büyükçekmece', 'Gaziosmanpaşa',
            ],
            'ankara' => [
                'Çankaya', 'Keçiören', 'Yenimahalle', 'Mamak', 'Etimesgut', 'Sincan',
                'Pursaklar', 'Altındağ', 'Gölbaşı',
            ],
            'izmir' => [
                'Konak', 'Karşıyaka', 'Bornova', 'Buca', 'Bayraklı', 'Çiğli',
                'Gaziemir', 'Karabağlar', 'Menemen',
            ],
        ];
    }

    /** Sitemap’e giren öncelikli ilçeler (crawl bütçesi için daraltılmış). */
    public static function sitemapPrioritySlugs(): array
    {
        return [
            'istanbul' => ['kadikoy', 'besiktas', 'uskudar', 'sisli', 'atasehir', 'bakirkoy', 'pendik', 'umraniye'],
            'ankara' => ['cankaya', 'kecioren', 'yenimahalle', 'etimesgut'],
            'izmir' => ['konak', 'karsiyaka', 'bornova', 'buca'],
        ];
    }

    /** @return list<string> */
    public static function forCitySlug(string $citySlug): array
    {
        return self::map()[$citySlug] ?? [];
    }

    /**
     * @return list<array{city_slug: string, district: string, district_slug: string}>
     */
    public static function all(): array
    {
        $out = [];
        foreach (self::map() as $citySlug => $districts) {
            foreach ($districts as $district) {
                $out[] = [
                    'city_slug' => $citySlug,
                    'district' => $district,
                    'district_slug' => self::slug($district),
                ];
            }
        }

        return $out;
    }

    /**
     * @return list<array{city_slug: string, district: string, district_slug: string}>
     */
    public static function sitemapEntries(): array
    {
        $priority = self::sitemapPrioritySlugs();
        $out = [];
        foreach (self::all() as $row) {
            $allowed = $priority[$row['city_slug']] ?? [];
            if ($allowed !== [] && ! in_array($row['district_slug'], $allowed, true)) {
                continue;
            }
            $out[] = $row;
        }

        return $out;
    }

    public static function slug(string $name): string
    {
        $map = [
            'ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'İ' => 'i',
            'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u',
        ];
        $slug = strtr($name, $map);
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';

        return trim($slug, '-');
    }
}

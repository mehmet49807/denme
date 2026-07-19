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
            ],
            'ankara' => [
                'Çankaya', 'Keçiören', 'Yenimahalle', 'Mamak', 'Etimesgut', 'Sincan',
            ],
            'izmir' => [
                'Konak', 'Karşıyaka', 'Bornova', 'Buca', 'Bayraklı', 'Çiğli',
            ],
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

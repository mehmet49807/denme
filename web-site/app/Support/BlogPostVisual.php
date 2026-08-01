<?php

namespace App\Support;

/**
 * Blog kartları için şehir görseli / etiket eşlemesi.
 */
final class BlogPostVisual
{
    /** @var array<string, array{label: string, image: string}> */
    private const CITY_MAP = [
        'istanbul' => ['label' => 'İstanbul', 'image' => 'blog-city-istanbul'],
        'istanbulda' => ['label' => 'İstanbul', 'image' => 'blog-city-istanbul'],
        'kadikoy' => ['label' => 'Kadıköy', 'image' => 'blog-city-istanbul'],
        'uskudar' => ['label' => 'Üsküdar', 'image' => 'blog-city-istanbul'],
        'besiktas' => ['label' => 'Beşiktaş', 'image' => 'blog-city-istanbul'],
        'sisli' => ['label' => 'Şişli', 'image' => 'blog-city-istanbul'],
        'fatih' => ['label' => 'Fatih', 'image' => 'blog-city-istanbul'],
        'bakirkoy' => ['label' => 'Bakırköy', 'image' => 'blog-city-istanbul'],
        'ankara' => ['label' => 'Ankara', 'image' => 'blog-city-ankara'],
        'ankarada' => ['label' => 'Ankara', 'image' => 'blog-city-ankara'],
        'cankaya' => ['label' => 'Çankaya', 'image' => 'blog-city-ankara'],
        'keçiören' => ['label' => 'Keçiören', 'image' => 'blog-city-ankara'],
        'kecioren' => ['label' => 'Keçiören', 'image' => 'blog-city-ankara'],
        'yenimahalle' => ['label' => 'Yenimahalle', 'image' => 'blog-city-ankara'],
        'izmir' => ['label' => 'İzmir', 'image' => 'blog-city-izmir'],
        'izmirde' => ['label' => 'İzmir', 'image' => 'blog-city-izmir'],
        'karsiyaka' => ['label' => 'Karşıyaka', 'image' => 'blog-city-izmir'],
        'bornova' => ['label' => 'Bornova', 'image' => 'blog-city-izmir'],
        'konak' => ['label' => 'Konak', 'image' => 'blog-city-izmir'],
        'bursa' => ['label' => 'Bursa', 'image' => 'blog-city-bursa'],
        'nilufer' => ['label' => 'Nilüfer', 'image' => 'blog-city-bursa'],
        'antalya' => ['label' => 'Antalya', 'image' => 'blog-city-antalya'],
        'adana' => ['label' => 'Adana', 'image' => 'blog-city-adana'],
        'konya' => ['label' => 'Konya', 'image' => 'blog-city-konya'],
        'gaziantep' => ['label' => 'Gaziantep', 'image' => 'blog-city-gaziantep'],
        'kayseri' => ['label' => 'Kayseri', 'image' => 'blog-city-kayseri'],
        'mersin' => ['label' => 'Mersin', 'image' => 'blog-city-mersin'],
        'diyarbakir' => ['label' => 'Diyarbakır', 'image' => 'blog-city-diyarbakir'],
        'eskisehir' => ['label' => 'Eskişehir', 'image' => 'blog-city-eskisehir'],
        'samsun' => ['label' => 'Samsun', 'image' => 'blog-city-samsun'],
        'trabzon' => ['label' => 'Trabzon', 'image' => 'blog-city-trabzon'],
    ];

    /**
     * @param  array<string, mixed>  $post
     * @return array{image: string, label: string, alt: string}
     */
    public static function forPost(array $post): array
    {
        $slug = strtolower((string) ($post['slug'] ?? ''));
        $title = (string) ($post['title'] ?? 'Blog yazısı');
        $hit = self::matchCity($slug.' '.$title);

        if ($hit !== null) {
            return [
                'image' => $hit['image'],
                'label' => $hit['label'],
                'alt' => $hit['label'].' — '.$title,
            ];
        }

        return [
            'image' => 'blog-city-general',
            'label' => 'Rehber',
            'alt' => $title,
        ];
    }

    /**
     * @return array{label: string, image: string}|null
     */
    private static function matchCity(string $haystack): ?array
    {
        $haystack = self::asciiFold($haystack);
        // Longer keys first (kadikoy before generic patterns)
        $keys = array_keys(self::CITY_MAP);
        usort($keys, static fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($keys as $key) {
            $needle = self::asciiFold($key);
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return self::CITY_MAP[$key];
            }
        }

        return null;
    }

    private static function asciiFold(string $value): string
    {
        $map = [
            'ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'İ' => 'i',
            'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u',
        ];

        return strtr(mb_strtolower($value, 'UTF-8'), $map);
    }
}


<?php

namespace App\Support;

/** SEO ve çapraz linkler için öncelikli TR şehirleri (nüfus / arama hacmi). */
final class FeaturedCities
{
    /** @return list<string> Görünen şehir adları */
    public static function names(): array
    {
        return [
            'İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya', 'Adana', 'Konya', 'Gaziantep',
            'Şanlıurfa', 'Kocaeli', 'Mersin', 'Diyarbakır', 'Hatay', 'Manisa', 'Kayseri', 'Samsun',
            'Balıkesir', 'Kahramanmaraş', 'Van', 'Aydın', 'Tekirdağ', 'Sakarya', 'Denizli', 'Muğla',
            'Eskişehir', 'Trabzon', 'Erzurum', 'Ordu', 'Malatya', 'Afyonkarahisar', 'Sivas', 'Çanakkale',
            'Tokat', 'Elazığ', 'Zonguldak', 'Osmaniye', 'Çorum', 'Giresun', 'Isparta', 'Aksaray',
        ];
    }

    /** @return list<array{name: string, slug: string}> */
    public static function links(\App\Services\LocationDataService $locations): array
    {
        $out = [];
        foreach (self::names() as $name) {
            if (! $locations->isValidCity('Türkiye', $name)) {
                continue;
            }
            $out[] = [
                'name' => $name,
                'slug' => $locations->citySlug($name),
            ];
        }

        return $out;
    }
}

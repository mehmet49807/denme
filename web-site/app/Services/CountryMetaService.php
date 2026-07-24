<?php

namespace App\Services;

class CountryMetaService
{
    /** @var array<string, array{iso: string, dial: string}> */
    private const COUNTRIES = [
        'Türkiye' => ['iso' => 'tr', 'dial' => '+90'],
        'Almanya' => ['iso' => 'de', 'dial' => '+49'],
        'Fransa' => ['iso' => 'fr', 'dial' => '+33'],
        'İngiltere' => ['iso' => 'gb', 'dial' => '+44'],
        'Amerika Birleşik Devletleri' => ['iso' => 'us', 'dial' => '+1'],
        'Kanada' => ['iso' => 'ca', 'dial' => '+1'],
        'Avustralya' => ['iso' => 'au', 'dial' => '+61'],
        'Hollanda' => ['iso' => 'nl', 'dial' => '+31'],
        'Belçika' => ['iso' => 'be', 'dial' => '+32'],
        'İsviçre' => ['iso' => 'ch', 'dial' => '+41'],
        'Avusturya' => ['iso' => 'at', 'dial' => '+43'],
        'İtalya' => ['iso' => 'it', 'dial' => '+39'],
        'İspanya' => ['iso' => 'es', 'dial' => '+34'],
        'Rusya' => ['iso' => 'ru', 'dial' => '+7'],
        'Ukrayna' => ['iso' => 'ua', 'dial' => '+380'],
        'Azerbaycan' => ['iso' => 'az', 'dial' => '+994'],
        'Gürcistan' => ['iso' => 'ge', 'dial' => '+995'],
        'Suudi Arabistan' => ['iso' => 'sa', 'dial' => '+966'],
        'BAE' => ['iso' => 'ae', 'dial' => '+971'],
        'Katar' => ['iso' => 'qa', 'dial' => '+974'],
        'Mısır' => ['iso' => 'eg', 'dial' => '+20'],
        'Fas' => ['iso' => 'ma', 'dial' => '+212'],
        'Brezilya' => ['iso' => 'br', 'dial' => '+55'],
        'Arjantin' => ['iso' => 'ar', 'dial' => '+54'],
        'Meksika' => ['iso' => 'mx', 'dial' => '+52'],
        'Japonya' => ['iso' => 'jp', 'dial' => '+81'],
        'Güney Kore' => ['iso' => 'kr', 'dial' => '+82'],
        'Çin' => ['iso' => 'cn', 'dial' => '+86'],
        'Hindistan' => ['iso' => 'in', 'dial' => '+91'],
        'Pakistan' => ['iso' => 'pk', 'dial' => '+92'],
        'Endonezya' => ['iso' => 'id', 'dial' => '+62'],
        'Malezya' => ['iso' => 'my', 'dial' => '+60'],
        'Singapur' => ['iso' => 'sg', 'dial' => '+65'],
        'Tayland' => ['iso' => 'th', 'dial' => '+66'],
        'Yeni Zelanda' => ['iso' => 'nz', 'dial' => '+64'],
        'İsveç' => ['iso' => 'se', 'dial' => '+46'],
        'Norveç' => ['iso' => 'no', 'dial' => '+47'],
        'Danimarka' => ['iso' => 'dk', 'dial' => '+45'],
        'Finlandiya' => ['iso' => 'fi', 'dial' => '+358'],
        'Polonya' => ['iso' => 'pl', 'dial' => '+48'],
        'Yunanistan' => ['iso' => 'gr', 'dial' => '+30'],
        'Bulgaristan' => ['iso' => 'bg', 'dial' => '+359'],
        'Romanya' => ['iso' => 'ro', 'dial' => '+40'],
        'Portekiz' => ['iso' => 'pt', 'dial' => '+351'],
        'Çekya' => ['iso' => 'cz', 'dial' => '+420'],
        'Slovakya' => ['iso' => 'sk', 'dial' => '+421'],
        'Macaristan' => ['iso' => 'hu', 'dial' => '+36'],
        'Sırbistan' => ['iso' => 'rs', 'dial' => '+381'],
        'Hırvatistan' => ['iso' => 'hr', 'dial' => '+385'],
        'Bosna-Hersek' => ['iso' => 'ba', 'dial' => '+387'],
        'Karadağ' => ['iso' => 'me', 'dial' => '+382'],
        'Arnavutluk' => ['iso' => 'al', 'dial' => '+355'],
        'Kuzey Makedonya' => ['iso' => 'mk', 'dial' => '+389'],
        'Slovenya' => ['iso' => 'si', 'dial' => '+386'],
        'Litvanya' => ['iso' => 'lt', 'dial' => '+370'],
        'Letonya' => ['iso' => 'lv', 'dial' => '+371'],
        'Estonya' => ['iso' => 'ee', 'dial' => '+372'],
        'Belarus' => ['iso' => 'by', 'dial' => '+375'],
        'Moldova' => ['iso' => 'md', 'dial' => '+373'],
        'Kazakistan' => ['iso' => 'kz', 'dial' => '+7'],
        'Özbekistan' => ['iso' => 'uz', 'dial' => '+998'],
        'Kırgızistan' => ['iso' => 'kg', 'dial' => '+996'],
        'Tacikistan' => ['iso' => 'tj', 'dial' => '+992'],
        'Türkmenistan' => ['iso' => 'tm', 'dial' => '+993'],
        'Afganistan' => ['iso' => 'af', 'dial' => '+93'],
        'İran' => ['iso' => 'ir', 'dial' => '+98'],
        'Irak' => ['iso' => 'iq', 'dial' => '+964'],
        'Suriye' => ['iso' => 'sy', 'dial' => '+963'],
        'Ürdün' => ['iso' => 'jo', 'dial' => '+962'],
        'Lübnan' => ['iso' => 'lb', 'dial' => '+961'],
        'İsrail' => ['iso' => 'il', 'dial' => '+972'],
        'Filistin' => ['iso' => 'ps', 'dial' => '+970'],
        'Cezayir' => ['iso' => 'dz', 'dial' => '+213'],
        'Tunus' => ['iso' => 'tn', 'dial' => '+216'],
        'Nijerya' => ['iso' => 'ng', 'dial' => '+234'],
        'Güney Afrika' => ['iso' => 'za', 'dial' => '+27'],
        'Kenya' => ['iso' => 'ke', 'dial' => '+254'],
        'Etiyopya' => ['iso' => 'et', 'dial' => '+251'],
        'Şili' => ['iso' => 'cl', 'dial' => '+56'],
        'Peru' => ['iso' => 'pe', 'dial' => '+51'],
        'Kolombiya' => ['iso' => 'co', 'dial' => '+57'],
        'Venezuela' => ['iso' => 've', 'dial' => '+58'],
    ];

    public function isoForCountry(string $country): string
    {
        $country = trim($country);

        return self::COUNTRIES[$country]['iso'] ?? '';
    }

    public function flagUrl(string $iso): string
    {
        $iso = strtolower(trim($iso));
        if ($iso === '' || strlen($iso) !== 2) {
            $iso = 'tr';
        }

        return 'https://flagcdn.com/w20/'.$iso.'.png';
    }

    /**
     * @return list<array{dial: string, iso: string, name: string}>
     */
    public function dialCodes(): array
    {
        $items = [];
        foreach (self::COUNTRIES as $name => $meta) {
            $items[] = [
                'dial' => $meta['dial'],
                'iso' => $meta['iso'],
                'name' => $name,
            ];
        }

        usort($items, static fn (array $a, array $b) => strcmp($a['name'], $b['name']));

        return $items;
    }

    public function isoForDialCode(string $dial): string
    {
        $dial = trim($dial);
        foreach (self::COUNTRIES as $meta) {
            if ($meta['dial'] === $dial) {
                return $meta['iso'];
            }
        }

        return 'tr';
    }

    public function isValidDialCode(string $dial): bool
    {
        $dial = trim($dial);
        foreach (self::COUNTRIES as $meta) {
            if ($meta['dial'] === $dial) {
                return true;
            }
        }

        return false;
    }

    public function composePhone(string $dial, string $local): string
    {
        $digits = preg_replace('/\D+/', '', $local) ?? '';
        $digits = ltrim($digits, '0');

        return trim($dial).$digits;
    }
}

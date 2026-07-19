<?php

namespace App\Support;

class SeoHelper
{
    protected static array $data = [];

    protected static array $pages = [
        'home' => [
            'title' => 'Gönül Köprüsü — Ücretsiz Tanışma, Evlilik ve Ciddi İlişki Sitesi',
            'description' => 'Türkiye\'nin güvenli tanışma ve evlilik sitesi. Ücretsiz üye ol, ciddi ilişki için tanış, şehirine göre keşfet. İstanbul, Ankara, İzmir ve 80+ şehirde online sohbet — kadınlarda mesajlaşma ücretsiz.',
            'keywords' => 'gönül köprüsü, tanışma sitesi, ücretsiz tanışma sitesi, evlilik sitesi, ciddi ilişki, arkadaşlık sitesi, güvenli tanışma, online tanışma, online sohbet, sohbet sitesi, eş bulma, Türkiye tanışma sitesi, istanbul tanışma, ankara tanışma, izmir tanışma, ücretsiz evlilik sitesi',
            'ogType' => 'website',
        ],
        'about' => [
            'title' => 'Hakkımızda — Gönül Köprüsü Tanışma Platformu',
            'description' => 'Gönül Köprüsü hakkında: güvenli, ciddi ve modern tanışma platformu. Evlilik ve anlamlı ilişki odaklı üye topluluğu.',
            'keywords' => 'gönül köprüsü hakkında, tanışma platformu, güvenli tanışma, evlilik platformu, gönül köprüsü nedir',
        ],
        'blog' => [
            'title' => 'Blog — Tanışma, Sohbet ve Evlilik Rehberleri',
            'description' => 'Gönül Köprüsü blog: şehir şehir tanışma, güvenli sohbet, ciddi ilişki ve evlilik odaklı Türkçe rehber yazıları.',
            'keywords' => 'tanışma blog, evlilik rehberi, sohbet tavsiyeleri, flört rehberi, gönül köprüsü blog, şehir tanışma',
            'ogType' => 'website',
        ],
        'sss' => [
            'title' => 'SSS — Tanışma Sitesi Sıkça Sorulan Sorular',
            'description' => 'Gönül Köprüsü SSS: ücretsiz üyelik, premium, güvenli tanışma, sohbet ve şehir kaydı hakkında sık sorulan sorular.',
            'keywords' => 'gönül köprüsü SSS, tanışma sitesi sorular, ücretsiz üyelik, güvenli tanışma, sohbet sitesi SSS',
            'ogType' => 'website',
        ],
        'register' => [
            'title' => 'Ücretsiz Üye Ol — Tanışma ve Sohbete Başla',
            'description' => 'Gönül Köprüsü\'ne ücretsiz üye olun. Profilinizi oluşturun, şehrinizdeki üyelerle tanışın ve güvenli sohbet edin.',
            'keywords' => 'ücretsiz üye ol, tanışma sitesi kayıt, ücretsiz tanışma, online sohbet kayıt, gönül köprüsü kayıt',
        ],
        'login' => [
            'title' => 'Giriş Yap',
            'description' => 'Gönül Köprüsü hesabınıza giriş yapın. Tanışma, mesajlaşma ve keşfe devam edin.',
            'keywords' => 'giriş yap, gönül köprüsü giriş, tanışma sitesi login',
        ],
        'privacy' => [
            'title' => 'Gizlilik Politikası',
            'description' => 'Gönül Köprüsü gizlilik politikası ve kişisel veri koruma bilgileri.',
            'keywords' => 'gizlilik politikası, KVKK, kişisel veri',
        ],
        'kvkk' => [
            'title' => 'KVKK Aydınlatma Metni',
            'description' => 'Gönül Köprüsü KVKK aydınlatma metni.',
            'keywords' => 'KVKK, kişisel veri, aydınlatma metni',
        ],
        'terms' => [
            'title' => 'Kullanım Koşulları',
            'description' => 'Gönül Köprüsü kullanım koşulları ve şartları.',
            'keywords' => 'kullanım koşulları, üyelik sözleşmesi',
        ],
        'report' => [
            'title' => 'Şikayet ve Engelleme',
            'description' => 'Gönül Köprüsü şikayet ve engelleme politikası.',
            'keywords' => 'şikayet, engelleme, güvenlik',
        ],
        'safety' => [
            'title' => 'Güvenli Tanışma Rehberi',
            'description' => 'Online tanışma ve sohbette güvende kalma rehberi. İlk buluşma ve güvenlik ipuçları.',
            'keywords' => 'güvenli tanışma, online güvenlik, ilk buluşma, güvenli sohbet',
        ],
        'search' => [
            'title' => 'Üye Ara — Şehir ve Profil Keşfi',
            'description' => 'Gönül Köprüsü üye arama: şehir, ilçe ve ilgi alanına göre tanışma profilleri keşfedin.',
            'keywords' => 'üye ara, profil ara, şehir tanışma, tanışma sitesi arama',
        ],
    ];

    public static function setPage(string $page): void
    {
        if (isset(static::$pages[$page])) {
            static::$data = array_merge(static::$data, static::$pages[$page]);
        }
    }

    public static function set(string $key, mixed $value): void
    {
        static::$data[$key] = $value;
    }

    public static function setMultiple(array $data): void
    {
        static::$data = array_merge(static::$data, $data);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::$data[$key] ?? $default;
    }

    public static function all(): array
    {
        return static::$data;
    }

    public static function setBlogPost(array $post, string $slug): void
    {
        static::$data = array_merge(static::$data, [
            'title' => (string) ($post['title'] ?? 'Blog'),
            'description' => (string) ($post['description'] ?? ($post['title'] ?? 'Blog yazısı')),
            'keywords' => is_array($post['keywords'] ?? null)
                ? implode(', ', $post['keywords'])
                : (string) ($post['keywords'] ?? 'Gönül Köprüsü blog'),
            'ogType' => 'article',
            'canonical' => url('/blog/'.$slug),
        ]);
    }

    public static function setUserProfile(object $user): void
    {
        $city = $user->city ?? '';

        static::$data = array_merge(static::$data, [
            'title' => ($user->first_name ?? $user->username).' — Profil',
            'description' => ($user->first_name ?? $user->username).($city ? ' — '.$city : '').' | Gönül Köprüsü profili.',
            'keywords' => 'profil, tanışma, '.$city.', Gönül Köprüsü',
            'ogType' => 'profile',
            'ogImage' => $user->profile_photo_url ?? null,
            'noindex' => true,
        ]);
    }

    public static function setLocationPage(string $city, ?string $district = null, ?string $country = null): void
    {
        $location = $district ? $district.', '.$city : $city;

        static::$data = array_merge(static::$data, [
            'title' => $location.' Tanışma, Sohbet ve Evlilik Sitesi',
            'description' => $location.' tanışma sitesi — Gönül Köprüsü ile '.$location.' bölgesinde ciddi ilişki, güvenli sohbet ve evlilik odaklı ücretsiz tanışma. Hemen üye ol.',
            'keywords' => implode(', ', [
                $location.' tanışma',
                $location.' tanışma sitesi',
                $city.' evlilik',
                $city.' eş bulma',
                $city.' sohbet',
                $city.' flört',
                'gönül köprüsü '.$city,
                'ücretsiz tanışma '.$city,
                'online tanışma '.$city,
            ]),
        ]);
    }

    public static function reset(): void
    {
        static::$data = [];
    }
}

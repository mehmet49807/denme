<?php

namespace App\Support;

class SeoHelper
{
    protected static array $data = [];

    protected static array $pages = [
        'home' => [
            'title' => 'Evlilik ve Tanışma Platformu',
            'description' => 'Gönül Köprüsü — Ciddi ilişki ve evlilik arayan yetişkinler için güvenli, saygılı tanışma platformu. Ücretsiz üye ol, profilini oluştur, anlamlı bağlar kur.',
            'keywords' => 'tanışma sitesi, evlilik sitesi, ciddi ilişki, güvenli tanışma, Gönül Köprüsü, eş bulma, arkadaşlık sitesi, online tanışma, evlilik platformu, ücretsiz tanışma, Türkiye tanışma sitesi',
            'ogType' => 'website',
        ],
        'about' => [
            'title' => 'Hakkımızda',
            'description' => 'Gönül Köprüsü hakkında bilgi edinin. Türkiye\'nin güvenli, ciddi ve modern tanışma platformu.',
            'keywords' => 'Gönül Köprüsü hakkında, tanışma platformu, güvenli tanışma, evlilik platformu',
        ],
        'blog' => [
            'title' => 'Blog',
            'description' => 'Gönül Köprüsü blog — ciddi ilişki, güvenli tanışma ve evlilik odaklı Türkçe rehber yazıları.',
            'keywords' => 'ilişki blog, evlilik rehberi, tanışma tavsiyeleri, Gönül Köprüsü blog',
            'ogType' => 'website',
        ],
        'sss' => [
            'title' => 'Sıkça Sorulan Sorular',
            'description' => 'Gönül Köprüsü SSS — güvenli tanışma, ciddi ilişki, üyelik ve moderasyon hakkında sıkça sorulan sorular.',
            'keywords' => 'Gönül Köprüsü SSS, tanışma sitesi sorular, üyelik, güvenli tanışma',
            'ogType' => 'website',
        ],
        'register' => [
            'title' => 'Ücretsiz Üye Ol',
            'description' => 'Gönül Köprüsü\'ne ücretsiz üye olun. Profilinizi oluşturun, binlerce kullanıcı arasında doğru insanı bulun.',
            'keywords' => 'üye ol, kayıt ol, ücretsiz tanışma sitesi, Gönül Köprüsü kayıt',
        ],
        'login' => [
            'title' => 'Giriş Yap',
            'description' => 'Gönül Köprüsü hesabınıza giriş yapın.',
            'keywords' => 'giriş yap, login, Gönül Köprüsü giriş',
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
            'description' => 'Online tanışmada güvende kalma rehberi ve güvenlik ipuçları.',
            'keywords' => 'güvenli tanışma, online güvenlik, ilk buluşma',
        ],
        'search' => [
            'title' => 'Üye Ara',
            'description' => 'Gönül Köprüsü üye arama sayfası.',
            'keywords' => 'üye ara, profil ara, tanışma sitesi arama',
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
        ]);
    }

    public static function setLocationPage(string $city, ?string $district = null, ?string $country = null): void
    {
        $location = $district ? $district.', '.$city : $city;

        static::$data = array_merge(static::$data, [
            'title' => $location.' Tanışma ve Evlilik Sitesi',
            'description' => $location.' bölgesinde ciddi ilişki ve evlilik arayan kişilerle tanışın. Gönül Köprüsü\'nde '.$location.' kullanıcılarını keşfedin.',
            'keywords' => $location.' tanışma, '.$location.' evlilik, '.$city.' eş bulma',
        ]);
    }

    public static function reset(): void
    {
        static::$data = [];
    }
}

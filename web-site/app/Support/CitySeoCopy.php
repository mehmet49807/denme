<?php

namespace App\Support;

/**
 * Şehir SEO sayfaları için özgün kısa metin + yerel SSS.
 */
final class CitySeoCopy
{
    /**
     * @return array{lead: string, why: list<string>, faqs: list<array{question: string, answer: string}>}
     */
    public static function forCity(string $city, string $slug, int $memberCount, int $femaleCount, int $maleCount): array
    {
        $members = number_format(max(0, $memberCount));
        $female = number_format(max(0, $femaleCount));
        $male = number_format(max(0, $maleCount));

        $hooks = [
            'istanbul' => "Türkiye'nin en kalabalık tanışma havuzu — {$city}'da ciddi ilişki arayan yetişkinler Gönül Köprüsü'nde buluşuyor.",
            'ankara' => "Başkentte güvenli sohbet ve evlilik niyeti: {$city} üyeleri profilini tamamlayıp mesajlaşmaya başlıyor.",
            'izmir' => "Ege'nin sıcak atmosferinde {$city} tanışma — flört değil, saygılı ve ciddi bağlantılar.",
            'bursa' => "{$city} ve çevresinde evlilik odaklı tanışma; ücretsiz kayıt, moderasyonlu ortam.",
            'antalya' => "Akdeniz'de yeni bir başlangıç: {$city} tanışma sitesi olarak güvenli online sohbet sunuyoruz.",
            'adana' => "{$city}'da ücretsiz üye ol, şehrindeki profilleri keşfet, ciddi ilişki için ilk adımı at.",
            'konya' => "{$city} tanışma ve evlilik arayışı için sakin, saygılı bir platform.",
            'gaziantep' => "{$city}'te güvenli tanışma — ailevi değerlere saygılı, ciddi niyetli üyeler.",
            'kayseri' => "{$city} ve Orta Anadolu'da online sohbet ile anlamlı bağlantılar kur.",
            'mersin' => "{$city} sahil kentinde güvenli flört değil; ciddi ilişki ve evlilik odaklı tanışma.",
        ];

        $lead = $hooks[$slug] ?? (
            "{$city} tanışma sitesi Gönül Köprüsü: ücretsiz üye ol, güvenli online sohbet et, "
            .'ciddi ilişki ve evlilik odaklı profilleri keşfet.'
        );

        if ($memberCount > 0) {
            $lead .= " Şu an {$city}'da yaklaşık {$members} kayıtlı üye"
                .($femaleCount + $maleCount > 0 ? " ({$female} kadın · {$male} erkek)" : '')
                .' görünür durumda.';
        }

        $why = [
            "{$city} odaklı üye keşfi ve konum filtreleri",
            'Güvenli sohbet, engelleme ve şikayet araçları',
            'Moderasyonlu ortam — kadın üyelerde mesaj / kimler baktı ücretsiz',
            "Ücretsiz kayıt — birkaç dakikada {$city} tanışmaya başla",
            'Instagram ve davet linkleriyle arkadaşlarını da getirebilirsin',
        ];

        $faqs = [
            [
                'question' => "{$city} tanışma sitesi ücretsiz mi?",
                'answer' => "Evet. Gönül Köprüsü'ne {$city}'dan ücretsiz üye olabilirsin. Kadın üyelerde mesajlaşma ve kimler baktı ücretsizdir; erkek üyeler deneme süresi ve premium paketlerle ek özelliklere erişir.",
            ],
            [
                'question' => "{$city}'da ciddi ilişki / evlilik için uygun mu?",
                'answer' => "Platform ciddi ilişki ve evlilik niyetiyle tasarlandı. {$city} ve Türkiye genelinde yetişkin üyeler profil, ilgi alanı ve konum bilgileriyle keşfedilir; spam ve uygunsuz içerik moderasyonla sınırlanır.",
            ],
            [
                'question' => "{$city} üyeleriyle nasıl güvenli sohbet ederim?",
                'answer' => 'Kayıt sonrası profilini tamamla, fotoğraf ekle ve mesajlaşmaya başla. İlk buluşmalarda kişisel/finansal bilgi paylaşma; şüpheli davranışı şikayet et. Detaylar için Güvenli Tanışma sayfamıza bak.',
            ],
            [
                'question' => "{$city} dışından da üye olabilir miyim?",
                'answer' => "Evet. Konumunu profilinde güncelleyebilir, {$city} dahil Türkiye şehirlerindeki üyeleri keşfedebilirsin. Şehir SEO sayfaları yalnızca keşif içindir; kayıt tüm Türkiye'den açıktır.",
            ],
        ];

        return [
            'lead' => $lead,
            'why' => $why,
            'faqs' => $faqs,
        ];
    }
}

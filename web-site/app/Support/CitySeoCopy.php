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
            'istanbul' => "Türkiye'nin en kalabalık tanışma havuzu — {$city}'da ciddi ilişki arayan yetişkinler Gönül Köprüsü'nde buluşuyor. İstanbul evlilik sitesi arayanlar için güvenli sohbet ve şehir içi keşif.",
            'ankara' => "Başkentte güvenli sohbet ve evlilik niyeti: {$city} üyeleri profilini tamamlayıp mesajlaşmaya başlıyor. Ankara tanışma sitesi olarak ücretsiz kayıt açıktır.",
            'izmir' => "Ege'nin sıcak atmosferinde {$city} tanışma — flört değil, saygılı ve ciddi bağlantılar. İzmir evlilik ve ciddi ilişki arayışına uygun moderasyonlu ortam.",
            'bursa' => "{$city} ve çevresinde evlilik odaklı tanışma; ücretsiz kayıt, moderasyonlu ortam. Marmara'da güvenli online sohbet.",
            'antalya' => "Akdeniz'de yeni bir başlangıç: {$city} tanışma sitesi olarak güvenli online sohbet sunuyoruz. Turizm kentinde ciddi ilişki niyeti taşıyan üyeler.",
            'adana' => "{$city}'da ücretsiz üye ol, şehrindeki profilleri keşfet, ciddi ilişki için ilk adımı at. Çukurova'da güvenli tanışma.",
            'konya' => "{$city} tanışma ve evlilik arayışı için sakin, saygılı bir platform. Ailevi değerlere uygun ciddi niyetli üyeler.",
            'gaziantep' => "{$city}'te güvenli tanışma — ailevi değerlere saygılı, ciddi niyetli üyeler. Güneydoğu'da online sohbet ve evlilik odaklı keşif.",
            'kayseri' => "{$city} ve Orta Anadolu'da online sohbet ile anlamlı bağlantılar kur. Ciddi ilişki ve evlilik niyeti ön planda.",
            'mersin' => "{$city} sahil kentinde güvenli flört değil; ciddi ilişki ve evlilik odaklı tanışma. Ücretsiz kayıt, moderasyonlu mesajlaşma.",
            'diyarbakir' => "{$city}'da güvenli tanışma ve ciddi ilişki — ücretsiz üye ol, profilini tamamla, saygılı sohbete başla.",
            'eskişehir' => "Öğrenci ve profesyonel kent {$city}'de anlamlı tanışma. Ciddi ilişki ve güvenli online sohbet için Gönül Köprüsü.",
            'samsun' => "Karadeniz'de {$city} tanışma sitesi: ücretsiz kayıt, şehir filtreleri, evlilik odaklı üye kitlesi.",
            'trabzon' => "{$city} ve Doğu Karadeniz'de güvenli tanışma. Ciddi niyet, moderasyon ve ücretsiz üyelik.",
            'malatya' => "{$city}'da evlilik ve ciddi ilişki arayışı için sakin, güvenli bir tanışma ortamı.",
            'erzurum' => "{$city} tanışma — Doğu Anadolu'da online sohbet, ciddi ilişki ve ücretsiz kayıt.",
            'van' => "{$city}'de güvenli tanışma sitesi deneyimi: profil, konum ve saygılı mesajlaşma.",
            'denizli' => "{$city} ve Ege içlerinde evlilik odaklı tanışma. Ücretsiz üye ol, şehrindeki profilleri keşfet.",
            'sanliurfa' => "{$city}'da ailevi değerlere saygılı, ciddi niyetli tanışma. Güvenli sohbet ve moderasyon.",
            'kahramanmaras' => "{$city} tanışma sitesi Gönül Köprüsü ile ücretsiz kayıt, ciddi ilişki ve güvenli mesajlaşma.",
            'hatay' => "{$city}'da yeni bir başlangıç: güvenli online sohbet, ciddi ilişki ve şehir bazlı keşif.",
            'manisa' => "{$city} ve çevresinde evlilik / ciddi ilişki odaklı tanışma. Ücretsiz üyelik, moderasyonlu ortam.",
            'balikesir' => "{$city} tanışma — Marmara ve Ege kesişiminde güvenli sohbet ve anlamlı bağlantılar.",
            'tekirdag' => "{$city}'da ücretsiz tanışma sitesi deneyimi: konum filtresi, ciddi niyet, güvenli mesaj.",
            'sakarya' => "{$city} üyeleriyle tanış: ciddi ilişki, evlilik niyeti ve ücretsiz kayıt Gönül Köprüsü'nde.",
            'kocaeli' => "{$city} (İzmit çevresi) sanayi kentinde güvenli tanışma ve online sohbet. Ücretsiz üye ol.",
            'mugla' => "{$city} ve Ege sahillerinde ciddi ilişki arayışı — Bodrum, Fethiye ve çevresi dahil keşif.",
            'aydin' => "{$city}'da güvenli tanışma sitesi: ücretsiz kayıt, ciddi niyet, moderasyonlu sohbet.",
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

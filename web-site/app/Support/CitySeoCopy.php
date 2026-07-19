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
            'afyonkarahisar' => "{$city} tanışma — İç Ege'de evlilik odaklı, ücretsiz ve moderasyonlu platform.",
            'isparta' => "{$city}'da ciddi ilişki ve güvenli online sohbet. Ücretsiz üye ol, profilini tamamla.",
            'mardin' => "{$city} tanışma sitesi: ailevi değerlere saygılı, ciddi niyetli üyeler için güvenli ortam.",
            'ordu' => "Karadeniz'de {$city} tanışma — ücretsiz kayıt, şehir filtresi, evlilik odaklı keşif.",
            'tokat' => "{$city}'da güvenli tanışma ve ciddi ilişki. Gönül Köprüsü ile ücretsiz başla.",
            'corum' => "{$city} tanışma sitesi: online sohbet, moderasyon ve ücretsiz üyelik.",
            'osmaniye' => "{$city}'da evlilik ve ciddi ilişki arayışı için sakin, güvenli tanışma ortamı.",
            'canakkale' => "{$city} ve çevresinde güvenli tanışma — ücretsiz kayıt, saygılı sohbet.",
            'edirne' => "{$city} tanışma: Trakya'da ciddi ilişki ve evlilik odaklı üye keşfi.",
            'kirklareli' => "{$city}'da ücretsiz tanışma sitesi deneyimi — konum filtresi ve güvenli mesaj.",
            'yozgat' => "{$city} tanışma ve evlilik arayışı için Gönül Köprüsü; ücretsiz üye ol.",
        ];

        $lead = $hooks[$slug] ?? (
            "{$city} tanışma sitesi Gönül Köprüsü: ücretsiz üye ol, güvenli online sohbet et, "
            .'ciddi ilişki ve evlilik odaklı profilleri keşfet.'
        );

        // Long-tail H2 sinyali için büyük şehirlerde ek cümle
        $extra = [
            'istanbul' => ' İstanbul evlilik sitesi ve İstanbul tanışma aramalarında güvenli, moderasyonlu bir alternatif sunuyoruz.',
            'ankara' => ' Ankara evlilik sitesi arayanlar için ücretsiz kayıt ve şehir içi keşif açıktır.',
            'izmir' => ' İzmir evlilik sitesi ve İzmir tanışma niyetiyle gelen üyeler için saygılı bir ortam hedeflenir.',
            'bursa' => ' Bursa tanışma ve Bursa evlilik arayışında konum filtresi avantaj sağlar.',
            'antalya' => ' Antalya tanışma sayfamızdan ücretsiz kayıt olup ciddi ilişki odaklı profilleri görebilirsin.',
        ];
        if (isset($extra[$slug])) {
            $lead .= $extra[$slug];
        }

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
            "{$city} evlilik ve ciddi ilişki niyeti taşıyan üye kitlesi",
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
            [
                'question' => "{$city} evlilik sitesi olarak Gönül Köprüsü'nü seçmeli miyim?",
                'answer' => "Evlilik ve uzun soluklu ilişki arıyorsan niyetin net olduğu, moderasyonlu bir platform daha uygundur. {$city} için ücretsiz kayıt olup profilini tamamlayarak başlayabilirsin.",
            ],
        ];

        return [
            'lead' => $lead,
            'why' => $why,
            'faqs' => $faqs,
        ];
    }

    /**
     * @return array{lead: string, why: list<string>, faqs: list<array{question: string, answer: string}>}
     */
    public static function forDistrict(
        string $city,
        string $citySlug,
        string $district,
        int $memberCount,
        int $femaleCount,
        int $maleCount,
    ): array {
        $members = number_format(max(0, $memberCount));
        $place = $district.', '.$city;

        $lead = "{$district} tanışma — {$city} içinde {$district} ve çevresinde ciddi ilişki, güvenli sohbet ve evlilik odaklı ücretsiz tanışma. "
            ."{$city} evlilik sitesi arayanlar için ilçe bazlı keşif Gönül Köprüsü'nde.";

        if ($memberCount > 0) {
            $lead .= " {$place} bölgesinde yaklaşık {$members} kayıtlı üye görünür.";
        }

        $why = [
            "{$district} / {$city} konumuna göre üye keşfi",
            'Ücretsiz kayıt — kadınlarda mesajlaşma ücretsiz',
            'Moderasyon, engelleme ve şikayet araçları',
            "{$city} genelinden {$district} odaklı ciddi niyet",
            'Güvenli ilk buluşma için rehber ve destek',
        ];

        $faqs = [
            [
                'question' => "{$district} tanışma sitesi ücretsiz mi?",
                'answer' => "Evet. {$city} / {$district} için Gönül Köprüsü'ne ücretsiz üye olabilirsin. Profilinde ilçeni seçerek yakındaki üyeleri önceliklendirebilirsin.",
            ],
            [
                'question' => "{$district}'da ciddi ilişki bulabilir miyim?",
                'answer' => "Platform ciddi ilişki ve evlilik niyetiyle tasarlandı. {$district} ve {$city} genelindeki yetişkin üyelerle güvenli sohbet edebilirsin.",
            ],
            [
                'question' => "{$city} şehir sayfasına nasıl dönerim?",
                'answer' => "{$city} genel tanışma sayfası /sehir/{$citySlug} adresindedir. İlçe sayfaları yerel aramalar için ek keşif sunar.",
            ],
            [
                'question' => 'İlk buluşmada nelere dikkat etmeliyim?',
                'answer' => 'Halka açık yer seç, kişisel/finansal bilgi paylaşma, şüpheli davranışı bildir. Güvenli Tanışma rehberimizi oku.',
            ],
        ];

        return [
            'lead' => $lead,
            'why' => $why,
            'faqs' => $faqs,
        ];
    }
}

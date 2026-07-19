<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\FeaturedCities;
use App\Support\InstagramUrl;
use App\Support\SeoHelper;
use App\Support\SeoSchema;
use App\Services\LocationDataService;
use App\Services\PublishedBlogFaqService;
use Illuminate\View\View;

/**
 * Google’da öne çıkmak için anahtar kelime pillar sayfaları
 * (evlilik sitesi, ciddi ilişki, ücretsiz tanışma).
 */
class SeoPillarPageController extends Controller
{
    public function __construct(
        private LocationDataService $locations,
        private PublishedBlogFaqService $blogFaq,
    ) {}

    public function marriage(): View
    {
        return $this->render('marriage');
    }

    public function serious(): View
    {
        return $this->render('serious');
    }

    public function freeDating(): View
    {
        return $this->render('free');
    }

    public function friendship(): View
    {
        return $this->render('friendship');
    }

    private function render(string $key): View
    {
        $page = $this->pages()[$key] ?? abort(404);

        SeoHelper::setMultiple([
            'title' => $page['title'],
            'description' => $page['description'],
            'keywords' => $page['keywords'],
            'ogType' => 'website',
            'canonical' => url($page['path']),
        ]);

        $cityLinks = FeaturedCities::links($this->locations);
        $relatedPosts = collect($this->blogFaq->blogPosts())->take(4)->values()->all();

        $breadcrumb = SeoSchema::breadcrumb($page['h1'], url($page['path']));
        $jsonLd = SeoSchema::faqPage($page['faqs'], $breadcrumb);
        $jsonLd['@graph'][] = [
            '@type' => 'WebPage',
            'name' => $page['h1'],
            'url' => url($page['path']),
            'description' => $page['description'],
            'isPartOf' => ['@type' => 'WebSite', 'name' => 'Gönül Köprüsü', 'url' => url('/')],
        ];

        return view('web.seo-pillar', [
            'lastUpdated' => '19 Temmuz 2026',
            'pageKey' => $key,
            'documentTitle' => $page['title'],
            'h1' => $page['h1'],
            'eyebrow' => $page['eyebrow'],
            'lead' => $page['lead'],
            'sections' => $page['sections'],
            'faqs' => $page['faqs'],
            'cityLinks' => $cityLinks,
            'relatedPosts' => $relatedPosts,
            'pillarLinks' => $this->pillarNav($key),
            'registerUrl' => route('register', [
                'utm_source' => 'seo',
                'utm_medium' => 'pillar',
                'utm_campaign' => $key,
            ]),
            'instagramUrl' => InstagramUrl::withUtm('seo', 'pillar', $key),
            'jsonLd' => $jsonLd,
        ]);
    }

    /** @return list<array{label: string, url: string, active: bool}> */
    private function pillarNav(string $active): array
    {
        $items = [
            'marriage' => ['label' => 'Evlilik sitesi', 'path' => '/evlilik-sitesi'],
            'serious' => ['label' => 'Ciddi ilişki', 'path' => '/ciddi-iliski'],
            'free' => ['label' => 'Ücretsiz tanışma', 'path' => '/ucretsiz-tanisma-sitesi'],
            'friendship' => ['label' => 'Arkadaşlık sitesi', 'path' => '/arkadaslik-sitesi'],
        ];

        $nav = [];
        foreach ($items as $key => $item) {
            $nav[] = [
                'label' => $item['label'],
                'url' => url($item['path']),
                'active' => $key === $active,
            ];
        }

        return $nav;
    }

    /** @return array<string, array<string, mixed>> */
    private function pages(): array
    {
        return [
            'marriage' => [
                'path' => '/evlilik-sitesi',
                'eyebrow' => 'Evlilik odaklı tanışma',
                'h1' => 'Evlilik sitesi — Gönül Köprüsü',
                'title' => 'Evlilik Sitesi — Ücretsiz ve Güvenli Tanışma | Gönül Köprüsü',
                'description' => 'Türkiye\'nin güvenli evlilik sitesi Gönül Köprüsü. Ücretsiz üye ol, ciddi ilişki ve evlilik niyetiyle tanış, şehirine göre keşfet. İstanbul, Ankara, İzmir ve 80+ şehir.',
                'keywords' => 'evlilik sitesi, ücretsiz evlilik sitesi, türkiye evlilik sitesi, ciddi evlilik, eş bulma sitesi, evlilik için tanışma, gönül köprüsü evlilik',
                'lead' => 'Evlilik ve uzun soluklu birliktelik arıyorsan Gönül Köprüsü; flört uygulamalarından farklı olarak ciddi niyet, moderasyon ve şehir bazlı keşif sunar.',
                'sections' => [
                    [
                        'title' => 'Neden bir evlilik sitesi?',
                        'body' => 'Klasik arkadaşlık uygulamalarında niyet belirsiz kalabilir. Gönül Köprüsü evlilik ve ciddi ilişki odaklı bir topluluk kurar: profilini tamamla, fotoğraf ekle, şehrindeki uygun üyelerle güvenli sohbet et.',
                    ],
                    [
                        'title' => 'Nasıl çalışır?',
                        'body' => 'Ücretsiz kayıt → profil ve konum → keşfet / akış → mesajlaşma. Kadın üyelerde mesajlaşma ve kimler baktı ücretsizdir. Slack ve uygunsuz içerik moderasyonla sınırlanır.',
                    ],
                    [
                        'title' => 'Şehir şehir evlilik arayışı',
                        'body' => 'İstanbul evlilik sitesi, Ankara evlilik sitesi veya İzmir evlilik sitesi arayanlar için her ilde ayrı keşif sayfalarımız vardır. Konumunu seçerek yakındaki ciddi niyetli üyeleri görebilirsin.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Gönül Köprüsü ücretsiz bir evlilik sitesi mi?',
                        'answer' => 'Evet, üyelik ücretsizdir. Kadınlarda mesajlaşma ücretsizdir; erkekler deneme süresi ve isteğe bağlı premium ile ek özelliklere erişebilir.',
                    ],
                    [
                        'question' => 'Evlilik sitesinde güvenlik nasıl sağlanıyor?',
                        'answer' => 'Moderasyon, engelleme, şikayet ve güvenli tanışma rehberi vardır. Profil fotoğrafları ve mesajlar denetlenir; Google’da özel mesajların görünmez.',
                    ],
                    [
                        'question' => 'Hangi şehirlerde evlilik için tanışabilirim?',
                        'answer' => 'Türkiye geneli 80+ şehir desteklenir. İstanbul, Ankara, İzmir, Bursa, Antalya ve diğer iller için özel tanışma sayfalarımız vardır.',
                    ],
                ],
            ],
            'serious' => [
                'path' => '/ciddi-iliski',
                'eyebrow' => 'Ciddi ilişki',
                'h1' => 'Ciddi ilişki için tanışma sitesi',
                'title' => 'Ciddi İlişki — Güvenli Tanışma ve Sohbet | Gönül Köprüsü',
                'description' => 'Ciddi ilişki arayanlar için Gönül Köprüsü. Ücretsiz kayıt, güvenli sohbet, şehir bazlı keşif. Flört değil; anlamlı ve saygılı tanışma.',
                'keywords' => 'ciddi ilişki, ciddi ilişki sitesi, ciddi tanışma, ciddi sohbet, evlilik niyeti, güvenli tanışma, gönül köprüsü ciddi ilişki',
                'lead' => 'Ciddi ilişki niyetiyle yola çıkan yetişkinler için tasarlandı. Spam ve hafif flört yerine saygı, net beklenti ve güvenli mesajlaşma ön planda.',
                'sections' => [
                    [
                        'title' => 'Ciddi ilişki ne demek?',
                        'body' => 'Ortak gelecek, karşılıklı saygı ve açık iletişim. Gönül Köprüsü bu niyeti olan üyeleri bir araya getirir; profilinde beklentini ve ilgi alanlarını paylaşabilirsin.',
                    ],
                    [
                        'title' => 'Güvenli ilk adımlar',
                        'body' => 'Profil fotoğrafı ekle, bio yaz, şehrini seç. İlk mesajlarda kişisel/finansal bilgi paylaşma. Detaylar Güvenli Tanışma rehberimizde.',
                    ],
                    [
                        'title' => 'Şehrinde ciddi ilişki',
                        'body' => 'Konum filtreleriyle aynı şehirdeki üyeleri keşfet. Şehir sayfalarımız (ör. İstanbul tanışma, Ankara tanışma) yerel aramalarda da görünür.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Ciddi ilişki için ücretsiz üye olabilir miyim?',
                        'answer' => 'Evet. Kayıt ücretsizdir. Ciddi ilişki arayanlarla tanışmak için hemen profil oluşturabilirsin.',
                    ],
                    [
                        'question' => 'Platform flört sitesi midir?',
                        'answer' => 'Odak ciddi ilişki ve evliliktir. Hafif flört uygulamalarından farklı bir üye kitlesi hedeflenir; moderasyon uygunsuz içerikleri kısıtlar.',
                    ],
                    [
                        'question' => 'Kimler üye olabilir?',
                        'answer' => '18 yaş üzeri yetişkinler. Farklı yaş ve şehirlerden, ciddi niyet taşıyan herkes kayıt olabilir.',
                    ],
                ],
            ],
            'free' => [
                'path' => '/ucretsiz-tanisma-sitesi',
                'eyebrow' => 'Ücretsiz tanışma',
                'h1' => 'Ücretsiz tanışma sitesi — hemen kayıt ol',
                'title' => 'Ücretsiz Tanışma Sitesi — Online Sohbet | Gönül Köprüsü',
                'description' => 'Ücretsiz tanışma sitesi Gönül Köprüsü. Kayıt ol, şehirine göre keşfet, güvenli online sohbet et. Kadınlarda mesajlaşma ücretsiz.',
                'keywords' => 'ücretsiz tanışma sitesi, ücretsiz tanışma, ücretsiz sohbet sitesi, ücretsiz üye ol, online tanışma ücretsiz, gönül köprüsü ücretsiz',
                'lead' => 'Kart bilgisi olmadan ücretsiz üye ol. Profilini oluştur, şehrindeki üyeleri gör, güvenli sohbete başla.',
                'sections' => [
                    [
                        'title' => 'Ücretsiz neler var?',
                        'body' => 'Kayıt, profil, keşif ve temel mesajlaşma. Kadın üyelerde mesajlaşma ve kimler baktı ücretsizdir. Erkekler deneme süresiyle başlar; isteğe bağlı premium ek özellikler sunar.',
                    ],
                    [
                        'title' => 'Online sohbet güvenli mi?',
                        'body' => 'SSL, moderasyon, engelleme ve şikayet araçları aktiftir. İlk buluşmada halka açık yer tercih et; güvenli tanışma ipuçları sayfamızda.',
                    ],
                    [
                        'title' => 'Hemen başla',
                        'body' => 'Google ile veya e-posta ile kayıt. Birkaç dakikada profilin hazır; ardından akış ve mesajlara geçebilirsin.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Gerçekten ücretsiz mi?',
                        'answer' => 'Üyelik ücretsizdir. Temel tanışma ve sohbet özellikleri açıktır; premium isteğe bağlıdır.',
                    ],
                    [
                        'question' => 'Ücretsiz tanışma sitesinde fotoğraf zorunlu mu?',
                        'answer' => 'Zorunlu değildir ama fotoğraflı ve tamamlanmış profiller daha çok etkileşim alır. Doğrulanmış görünüm için profil onay süreci vardır.',
                    ],
                    [
                        'question' => 'Hangi cihazlarda çalışır?',
                        'answer' => 'Mobil ve masaüstü tarayıcıda çalışır. Ayrı bir uygulama indirmeden tarayıcıdan kullanabilirsin.',
                    ],
                ],
            ],
            'friendship' => [
                'path' => '/arkadaslik-sitesi',
                'eyebrow' => 'Arkadaşlık ve tanışma',
                'h1' => 'Arkadaşlık sitesi — güvenli tanışma',
                'title' => 'Arkadaşlık Sitesi — Tanışma ve Sohbet | Gönül Köprüsü',
                'description' => 'Güvenli arkadaşlık ve tanışma sitesi. Ciddi niyet, online sohbet, şehir bazlı keşif. Ücretsiz üye ol — Gönül Köprüsü.',
                'keywords' => 'arkadaşlık sitesi, tanışma sitesi, sohbet sitesi, online arkadaşlık, güvenli arkadaşlık, gönül köprüsü',
                'lead' => 'Yeni insanlarla tanışmak istiyorsan: saygılı sohbet, net niyet ve şehirine göre keşif. Gönül Köprüsü arkadaşlık ile ciddi ilişki arasında güvenli bir köprü kurar.',
                'sections' => [
                    [
                        'title' => 'Arkadaşlık sitesi farkı',
                        'body' => 'Rastgele eşleşmeler yerine profil, ilgi alanı ve konumla bilinçli keşif. Topluluk kuralları ve moderasyon ile daha sağlıklı bir ortam.',
                    ],
                    [
                        'title' => 'Sohbet ve mesajlaşma',
                        'body' => 'Özel mesajlar yalnızca üyeler arasında kalır. Engelleme ve şikayet her zaman elinin altında.',
                    ],
                    [
                        'title' => 'Türkiye geneli',
                        'body' => '81 ile yayılan üye ağı; büyük şehirlerden Anadolu’ya kadar tanışma imkânı.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Arkadaşlık sitesi mi evlilik sitesi mi?',
                        'answer' => 'Her iki niyete de açık bir platformuz; vurgu ciddi ilişki ve evliliktedir. Profilinde beklentini belirtebilirsin.',
                    ],
                    [
                        'question' => 'Üye olmak ücretli mi?',
                        'answer' => 'Hayır, kayıt ücretsizdir. İsteğe bağlı premium paketler vardır.',
                    ],
                    [
                        'question' => 'Yaş sınırı nedir?',
                        'answer' => '18 yaş ve üzeri. Reşit olmayanlar üye olamaz.',
                    ],
                ],
            ],
        ];
    }
}

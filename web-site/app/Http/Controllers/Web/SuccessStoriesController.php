<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\InstagramUrl;
use App\Support\SeoHelper;
use App\Support\SeoSchema;
use Illuminate\View\View;

/**
 * Başarı / tanışma hikâyeleri — Google’da güven ve E-E-A-T sinyali.
 */
class SuccessStoriesController extends Controller
{
    public function show(): View
    {
        SeoHelper::setPage('stories');
        SeoHelper::set('canonical', url('/basari-hikayeleri'));

        $stories = $this->stories();
        $faqs = [
            [
                'question' => 'Gönül Köprüsü’nde gerçekten ciddi ilişki kurulur mu?',
                'answer' => 'Platform evlilik ve ciddi ilişki niyetiyle tasarlandı. Moderasyon, engelleme ve güvenli tanışma rehberi ile saygılı bir ortam sunulur. Hikâyeler üye deneyimlerinden esinlenerek hazırlanmış örnek anlatımlardır.',
            ],
            [
                'question' => 'Ücretsiz üye olup tanışabilir miyim?',
                'answer' => 'Evet. Kayıt ücretsizdir. Kadın üyelerde mesajlaşma ücretsizdir; şehir filtreleriyle İstanbul, Ankara, İzmir ve diğer illerdeki üyeleri keşfedebilirsin.',
            ],
            [
                'question' => 'Hikâyemi nasıl paylaşabilirim?',
                'answer' => 'destek@gonulkoprusu.com adresine yazabilir veya Instagram @gonulkoprusucom üzerinden ulaşabilirsin. Onayınla paylaşırız.',
            ],
        ];

        $breadcrumb = SeoSchema::breadcrumb('Başarı hikâyeleri', url('/basari-hikayeleri'));
        $jsonLd = SeoSchema::faqPage($faqs, $breadcrumb);
        $jsonLd['@graph'][] = [
            '@type' => 'CollectionPage',
            'name' => 'Gönül Köprüsü Başarı Hikâyeleri',
            'url' => url('/basari-hikayeleri'),
            'description' => (string) SeoHelper::get('description'),
        ];

        return view('web.success-stories', [
            'lastUpdated' => '19 Temmuz 2026',
            'stories' => $stories,
            'faqs' => $faqs,
            'registerUrl' => route('register', [
                'utm_source' => 'seo',
                'utm_medium' => 'stories',
                'utm_campaign' => 'basari-hikayeleri',
            ]),
            'instagramUrl' => InstagramUrl::withUtm('seo', 'stories', 'instagram'),
            'jsonLd' => $jsonLd,
        ]);
    }

    /** @return list<array{names: string, city: string, quote: string, body: string}> */
    private function stories(): array
    {
        return [
            [
                'names' => 'Ayşe & Mehmet',
                'city' => 'İstanbul',
                'quote' => 'Ciddi niyet arıyorduk; burada buluştuk.',
                'body' => 'İstanbul’da kalabalık uygulamalar yormuştu. Gönül Köprüsü’nde profilleri okuyup birkaç hafta güvenli sohbet ettiler. İlk buluşmayı Kadıköy’de halka açık bir kafede yaptılar; bugün nişanlılar.',
            ],
            [
                'names' => 'Elif & Can',
                'city' => 'Ankara',
                'quote' => 'Başkentte sakin ve saygılı bir ortam.',
                'body' => 'Ankara’da evlilik odaklı bir platform arıyorlardı. Şehir filtresi ve tamamlanmış profiller sayesinde ortak hobileri olan biriyle tanıştılar. Mesajlaşma sonrası yüz yüze görüşüp ilişkiyi ilerlettiler.',
            ],
            [
                'names' => 'Zeynep & Emre',
                'city' => 'İzmir',
                'quote' => 'Flört değil, gerçek bağ istedik.',
                'body' => 'İzmir’de yüzeysel eşleşmelerden sıkılmışlardı. Gönül Köprüsü’nde niyetlerini net yazdılar; Karşıyaka ve Bornova çevresinde güvenli tanışma adımlarını izleyerek birliktelik kurdular.',
            ],
            [
                'names' => 'Selin & Burak',
                'city' => 'Bursa',
                'quote' => 'Aynı şehirde olmak her şeyi kolaylaştırdı.',
                'body' => 'Bursa tanışma sayfasından kayıt oldular. Konum yakınlığı sayesinde birkaç görüşmeden sonra aileleriyle tanışma aşamasına geçtiler.',
            ],
            [
                'names' => 'Deniz & Kerem',
                'city' => 'Antalya',
                'quote' => 'Güvenlik ayarları sayesinde rahat ettik.',
                'body' => 'Antalya’da turizm temposunda ciddi ilişki aramak zordu. Engelleme ve moderasyon sayesinde spam’siz bir deneyim yaşadıklarını anlatıyorlar.',
            ],
            [
                'names' => 'Merve & Onur',
                'city' => 'Adana',
                'quote' => 'Ücretsiz kayıtla başladık, hikâyemiz devam ediyor.',
                'body' => 'Adana’dan ücretsiz üye olup profilini tamamlayan Merve, kısa sürede ortak değerlere sahip biriyle sohbet etmeye başladı. İlk buluşmayı gündüz ve kalabalık bir mekânda yaptılar.',
            ],
        ];
    }
}

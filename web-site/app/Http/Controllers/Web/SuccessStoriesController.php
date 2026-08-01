<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\InstagramUrl;
use App\Support\SeoHelper;
use App\Support\SeoSchema;
use App\Support\SuccessStoriesContent;
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

        $stories = SuccessStoriesContent::all();
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
            [
                'question' => 'Hikâyeler reklam mı?',
                'answer' => 'Hayır. Anlatımlar platformdaki tipik güvenli tanışma yolculuğunu yansıtan örneklerdir; abartılı vaat veya garanti içermez.',
            ],
            [
                'question' => 'Şehrimde üye var mı?',
                'answer' => 'İstanbul, Ankara, İzmir ve onlarca il için şehir sayfalarımız var. /sehir/istanbul gibi adreslerden üye yoğunluğunu görebilir, ücretsiz kayıt olabilirsin.',
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
            'lastUpdated' => '1 Ağustos 2026',
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
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminMarketingController extends Controller
{
    public function index(SiteSettingsService $settings): View
    {
        $frontend = rtrim((string) config('app.frontend_url', 'https://gonulkoprusu.com'), '/');
        $instagram = trim((string) $settings->get('instagram_url', 'https://www.instagram.com/gonulkoprusucom'));
        if ($instagram === '' || preg_match('#instagram\.com/gonulkoprusu/?$#i', rtrim($instagram, '/'))) {
            $instagram = 'https://www.instagram.com/gonulkoprusucom';
        }

        $campaign = (string) $settings->get('marketing_default_campaign', 'organic');

        return view('admin.marketing', [
            'metrics' => $this->growthMetrics(),
            'frontendUrl' => $frontend,
            'instagramUrl' => rtrim($instagram, '/'),
            'facebookUrl' => (string) $settings->get('facebook_url', ''),
            'marketingNotes' => (string) $settings->get('marketing_notes', ''),
            'defaultCampaign' => $campaign,
            'links' => $this->campaignLinks($frontend, $campaign),
            'instagramPack' => $this->instagramPack($frontend, $campaign),
            'adsTestPack' => $this->adsTestPack($frontend),
            'inviteSharePack' => $this->inviteSharePack($frontend),
            'weeklyPlan' => $this->weeklyContentPlan($frontend),
            'adVideos' => $this->adVideos($frontend),
        ]);
    }

    public function update(Request $request, SiteSettingsService $settings): RedirectResponse
    {
        $validated = $request->validate([
            'instagram_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'marketing_default_campaign' => 'nullable|string|max:80|regex:/^[a-z0-9_\-]+$/i',
            'marketing_notes' => 'nullable|string|max:2000',
        ], [
            'instagram_url.url' => 'Instagram adresi geçerli bir URL olmalıdır.',
            'facebook_url.url' => 'Facebook adresi geçerli bir URL olmalıdır.',
            'marketing_default_campaign.regex' => 'Kampanya adı yalnızca harf, rakam, _ ve - içerebilir.',
        ]);

        $settings->setMany([
            'instagram_url' => rtrim(trim((string) ($validated['instagram_url'] ?? '')), '/'),
            'facebook_url' => rtrim(trim((string) ($validated['facebook_url'] ?? '')), '/'),
            'marketing_default_campaign' => strtolower(trim((string) ($validated['marketing_default_campaign'] ?? 'organic'))) ?: 'organic',
            'marketing_notes' => trim((string) ($validated['marketing_notes'] ?? '')),
        ]);

        return redirect()
            ->route('admin.marketing')
            ->with('success', 'Pazarlama ayarları kaydedildi.');
    }

    /** @return array<string, int|string> */
    private function growthMetrics(): array
    {
        $empty = [
            'signups' => 0,
            'female' => 0,
            'male' => 0,
            'with_photo' => 0,
            'referred' => 0,
            'google' => 0,
            'seo_city' => 0,
            'instagram' => 0,
            'meta' => 0,
            'paid' => 0,
            'error' => null,
        ];

        try {
            if (! Schema::hasTable('users')) {
                return $empty;
            }

            $since = now()->subDays(7);
            $base = fn () => DB::table('users')->where('role', 'user')->where('created_at', '>=', $since);

            return [
                'signups' => $base()->count(),
                'female' => $base()->where('gender', 'female')->count(),
                'male' => $base()->where('gender', 'male')->count(),
                'with_photo' => $base()->whereNotNull('profile_photo_url')->count(),
                'referred' => $base()->whereNotNull('referred_by_user_id')->count(),
                'google' => $base()->where('registration_source', 'google')->count(),
                'seo_city' => $base()->where('utm_medium', 'city')->count(),
                'instagram' => $base()->where('utm_source', 'instagram')->count(),
                'meta' => $base()->where('utm_source', 'meta')->count(),
                'paid' => $base()->where('utm_medium', 'paid')->count(),
                'error' => null,
            ];
        } catch (\Throwable $e) {
            $empty['error'] = $e->getMessage();

            return $empty;
        }
    }

    /**
     * Reklam videoları — canlıda /marketing/ads altında.
     *
     * @return list<array{
     *   id: string,
     *   title: string,
     *   subtitle: string,
     *   format: string,
     *   channel: string,
     *   duration_hint: string,
     *   video_url: string,
     *   poster_url: string,
     *   download_url: string,
     *   cta_url: string,
     *   group: string
     * }>
     */
    private function adVideos(string $frontend): array
    {
        $base = $frontend.'/images/ads';
        $reelsCta = $frontend.'/register?utm_source=instagram&utm_medium=reels&utm_campaign=v3';
        $items = [
            [
                'id' => 'ig-01-dur',
                'title' => 'Flört yorduysa dur',
                'subtitle' => 'Reels v3 · farklı görseller',
                'format' => '9:16',
                'channel' => 'Instagram Reels',
                'duration_hint' => '~11s',
                'video' => 'ig-01-dur.mp4',
                'poster' => 'ig-01-dur.png',
                'cta_url' => $reelsCta,
                'group' => 'Instagram Reels v3 (9:16)',
            ],
            [
                'id' => 'ig-02-soru',
                'title' => 'Gerçekten ciddi misin?',
                'subtitle' => 'Reels v3 · soru hook',
                'format' => '9:16',
                'channel' => 'Instagram Reels',
                'duration_hint' => '~11s',
                'video' => 'ig-02-soru.mp4',
                'poster' => 'ig-02-soru.png',
                'cta_url' => $reelsCta,
                'group' => 'Instagram Reels v3 (9:16)',
            ],
            [
                'id' => 'ig-03-guven',
                'title' => 'Güven önce gelir',
                'subtitle' => 'Reels v3 · güven',
                'format' => '9:16',
                'channel' => 'Instagram Reels · Stories',
                'duration_hint' => '~11s',
                'video' => 'ig-03-guven.mp4',
                'poster' => 'ig-03-guven.png',
                'cta_url' => $reelsCta,
                'group' => 'Instagram Reels v3 (9:16)',
            ],
            [
                'id' => 'ig-04-sehir',
                'title' => 'Şehrinde ciddi tanışma',
                'subtitle' => 'Reels v3 · yerel',
                'format' => '9:16',
                'channel' => 'Instagram Reels',
                'duration_hint' => '~11s',
                'video' => 'ig-04-sehir.mp4',
                'poster' => 'ig-04-sehir.png',
                'cta_url' => $reelsCta,
                'group' => 'Instagram Reels v3 (9:16)',
            ],
            [
                'id' => 'ig-05-evlilik',
                'title' => 'Evlilik niyetiyle',
                'subtitle' => 'Reels v3 · CTA',
                'format' => '9:16',
                'channel' => 'Instagram Reels · Feed',
                'duration_hint' => '~11s',
                'video' => 'ig-05-evlilik.mp4',
                'poster' => 'ig-05-evlilik.png',
                'cta_url' => $reelsCta,
                'group' => 'Instagram Reels v3 (9:16)',
            ],
        ];

        return array_map(static function (array $item) use ($base): array {
            $videoUrl = $base.'/'.$item['video'];

            return [
                'id' => $item['id'],
                'title' => $item['title'],
                'subtitle' => $item['subtitle'],
                'format' => $item['format'],
                'channel' => $item['channel'],
                'duration_hint' => $item['duration_hint'],
                'video_url' => $videoUrl,
                'poster_url' => $base.'/'.$item['poster'],
                'download_url' => $videoUrl,
                'cta_url' => $item['cta_url'],
                'group' => $item['group'],
            ];
        }, $items);
    }

    /**
     * Instagram bio/story tek tık kopyala paketi.
     *
     * @return array{
     *   bio_url: string,
     *   story_url: string,
     *   kampanya_url: string,
     *   captions: list<array{label: string, text: string}>,
     *   pack_text: string
     * }
     */
    private function instagramPack(string $frontend, string $campaign): array
    {
        $campaign = $campaign !== '' ? $campaign : 'organic';
        $bioUrl = $frontend.'/register?'.http_build_query([
            'utm_source' => 'instagram',
            'utm_medium' => 'bio',
            'utm_campaign' => $campaign,
        ]);
        $storyUrl = $frontend.'/register?'.http_build_query([
            'utm_source' => 'instagram',
            'utm_medium' => 'story',
            'utm_campaign' => $campaign,
        ]);
        $kampanyaUrl = $frontend.'/kampanya?'.http_build_query([
            'utm_source' => 'instagram',
            'utm_medium' => 'story',
            'utm_campaign' => $campaign,
        ]);
        $reelsUrl = $frontend.'/register?'.http_build_query([
            'utm_source' => 'instagram',
            'utm_medium' => 'reels',
            'utm_campaign' => 'v3',
        ]);

        $adsBase = $frontend.'/images/ads';
        $reels = [
            [
                'id' => 'ig-01-dur',
                'title' => 'Flört yorduysa dur',
                'file' => 'ig-01-dur.mp4',
                'video_url' => $adsBase.'/ig-01-dur.mp4',
                'caption' => "Flört yorduysa… ciddi ilişki zamanı.\nGönül Köprüsü — güvenli tanışma, evlilik niyeti.\nKart yok · Ücretsiz 👇\n{$reelsUrl}\n\n#ciddiilişki #evlilik #güvenlitanışma #gonülköprüsü #reels",
            ],
            [
                'id' => 'ig-02-soru',
                'title' => 'Ciddi misin?',
                'file' => 'ig-02-soru.mp4',
                'video_url' => $adsBase.'/ig-02-soru.mp4',
                'caption' => "Ciddi misin? Gerçekten mi?\nDoğru yerdesin — Gönül Köprüsü.\nÜcretsiz başla 👇\n{$reelsUrl}\n\n#ciddiilişki #evlilik #gonülköprüsü #tanışma #reels",
            ],
            [
                'id' => 'ig-03-guven',
                'title' => 'Önce güven',
                'file' => 'ig-03-guven.mp4',
                'video_url' => $adsBase.'/ig-03-guven.mp4',
                'caption' => "Önce güven, sonra sohbet.\nGüvenli profiller · ciddi niyet.\nÜcretsiz üye ol 👇\n{$reelsUrl}\n\n#güvenlitanışma #ciddiilişki #gonülköprüsü #reels",
            ],
            [
                'id' => 'ig-04-sehir',
                'title' => 'Şehrinde misin?',
                'file' => 'ig-04-sehir.mp4',
                'video_url' => $adsBase.'/ig-04-sehir.mp4',
                'caption' => "Şehrinde ciddi ilişki arayanlar burada.\nİstanbul · Ankara · İzmir…\nÜcretsiz kayıt 👇\n{$reelsUrl}\n\n#İstanbultanışma #ciddiilişki #gonülköprüsü #reels",
            ],
            [
                'id' => 'ig-05-evlilik',
                'title' => 'Evlilik niyetiyle',
                'file' => 'ig-05-evlilik.mp4',
                'video_url' => $adsBase.'/ig-05-evlilik.mp4',
                'caption' => "Evlilik niyetiyle arıyorsan doğru yerdesin.\nGönül Köprüsü — flört değil, ciddi bağ.\nÜcretsiz üye ol 👇\n{$reelsUrl}\n\n#evlilik #ciddiilişki #gonülköprüsü #reels",
            ],
        ];

        $captions = [
            [
                'label' => 'Bio kısa',
                'text' => "Gönül Köprüsü — ciddi ilişki & güvenli tanışma\nÜcretsiz kayıt 👇\n{$bioUrl}",
            ],
            [
                'label' => 'Story CTA',
                'text' => "Ciddi ilişki arıyorsan buradayız 💛\nKart bilgisi yok · Ücretsiz kayıt\nLink sticker: {$storyUrl}",
            ],
            [
                'label' => 'Davet ödülü',
                'text' => "Arkadaşını davet et, ödül kazan:\n• Erkek: +3 gün premium / davet\n• Kadın: 24 saat öne çıkma\n• Haftanın 1.’si ekstra ödül\nKayıt: {$storyUrl}",
            ],
        ];

        $packLines = [
            '=== Gönül Köprüsü Instagram Paketi ===',
            'Kampanya: '.$campaign,
            '',
            'BIO LINK:',
            $bioUrl,
            '',
            'STORY / STICKER LINK:',
            $storyUrl,
            '',
            'KAMPANYA LANDING:',
            $kampanyaUrl,
            '',
            'REELS CAPTION PAKETİ (UTF-8):',
            $adsBase.'/instagram-reels-captions.html',
            '',
        ];
        foreach ($reels as $reel) {
            $packLines[] = '--- '.$reel['id'].' · '.$reel['title'].' ---';
            $packLines[] = 'Video: '.$reel['video_url'];
            $packLines[] = $reel['caption'];
            $packLines[] = '';
        }
        foreach ($captions as $cap) {
            $packLines[] = '--- '.$cap['label'].' ---';
            $packLines[] = $cap['text'];
            $packLines[] = '';
        }

        return [
            'bio_url' => $bioUrl,
            'story_url' => $storyUrl,
            'kampanya_url' => $kampanyaUrl,
            'reels_url' => $reelsUrl,
            'captions_html_url' => $adsBase.'/instagram-reels-captions.html',
            'captions_txt_url' => $adsBase.'/instagram-reels-captions.txt',
            'reels' => $reels,
            'captions' => $captions,
            'pack_text' => trim(implode("\n", $packLines)),
        ];
    }

    /**
     * 7 günlük düşük bütçeli Meta/Google Ads test paketi.
     *
     * @return array{links: list<array{label: string, url: string, hint: string}>, pack_text: string, checklist: list<string>}
     */
    private function adsTestPack(string $frontend): array
    {
        $links = [
            [
                'label' => 'Meta test1 (genel)',
                'url' => $frontend.'/kampanya?'.http_build_query([
                    'utm_source' => 'meta', 'utm_medium' => 'paid', 'utm_campaign' => 'test1',
                ]),
                'hint' => '7 gün · kayıt hedefi · genel kreatif',
            ],
            [
                'label' => 'Meta İstanbul',
                'url' => $frontend.'/kampanya?'.http_build_query([
                    'utm_source' => 'meta', 'utm_medium' => 'paid', 'utm_campaign' => 'istanbul', 'city' => 'istanbul',
                ]),
                'hint' => 'Şehir hedefli · İstanbul',
            ],
            [
                'label' => 'Meta Ankara',
                'url' => $frontend.'/kampanya?'.http_build_query([
                    'utm_source' => 'meta', 'utm_medium' => 'paid', 'utm_campaign' => 'ankara', 'city' => 'ankara',
                ]),
                'hint' => 'Şehir hedefli · Ankara',
            ],
            [
                'label' => 'Meta İzmir',
                'url' => $frontend.'/kampanya?'.http_build_query([
                    'utm_source' => 'meta', 'utm_medium' => 'paid', 'utm_campaign' => 'izmir', 'city' => 'izmir',
                ]),
                'hint' => 'Şehir hedefli · İzmir',
            ],
            [
                'label' => 'Google CPC test1',
                'url' => $frontend.'/kampanya?'.http_build_query([
                    'utm_source' => 'google', 'utm_medium' => 'cpc', 'utm_campaign' => 'test1',
                ]),
                'hint' => 'Search / Performance Max test',
            ],
        ];

        $checklist = [
            'Bütçe: düşük, 7 gün (günlük sabit)',
            'Hedef: kayıt (sign_up / google_complete)',
            'Landing: yukarıdaki /kampanya URL’leri (UTM’li)',
            'Kreatif: Admin → Reklam medya (story MP4 / PNG)',
            'Ölçüm: Pazarlama metrikleri + GA olayları',
            'Durdur: CPA yüksekse 72 saat sonra kapat',
        ];

        $lines = ['=== Ads Test Paketi (7 gün) ===', ''];
        foreach ($links as $link) {
            $lines[] = $link['label'].': '.$link['url'];
        }
        $lines[] = '';
        $lines[] = 'Kontrol listesi:';
        foreach ($checklist as $item) {
            $lines[] = '- '.$item;
        }

        return [
            'links' => $links,
            'checklist' => $checklist,
            'pack_text' => implode("\n", $lines),
        ];
    }

    /**
     * Davet WhatsApp / SMS kopyala metinleri (SMS gateway yok — manuel veya WhatsApp).
     *
     * @return array{messages: list<array{label: string, text: string}>, pack_text: string}
     */
    private function inviteSharePack(string $frontend): array
    {
        $davet = $frontend.'/davet';
        $messages = [
            [
                'label' => 'WhatsApp kısa',
                'text' => "Gönül Köprüsü'nde buluşalım — ücretsiz kayıt:\n{$davet}",
            ],
            [
                'label' => 'WhatsApp ödül (erkek)',
                'text' => "Arkadaşını davet et, +3 gün premium kazan.\nCiddi ilişki odaklı tanışma:\n{$davet}",
            ],
            [
                'label' => 'WhatsApp ödül (kadın)',
                'text' => "Güvendiğin birini davet et — profilin 24 saat öne çıksın.\n{$davet}",
            ],
            [
                'label' => 'SMS kısa (160 kr.)',
                'text' => 'Gonul Koprusu: ucretsiz kayit ve guvenli tanisma. '.$frontend.'/register?utm_source=sms&utm_medium=manual&utm_campaign=invite',
            ],
        ];

        $lines = ['=== Davet WhatsApp / SMS Paketi ===', ''];
        foreach ($messages as $msg) {
            $lines[] = '--- '.$msg['label'].' ---';
            $lines[] = $msg['text'];
            $lines[] = '';
        }
        $lines[] = 'Not: Otomatik SMS gateway yok; push davet hatırlatması cron ile gider.';

        return [
            'messages' => $messages,
            'pack_text' => trim(implode("\n", $lines)),
        ];
    }

    /**
     * @return list<array{day: string, task: string, link: string}>
     */
    private function weeklyContentPlan(string $frontend): array
    {
        return [
            ['day' => 'Pzt', 'task' => 'İstanbul / Kadıköy şehir postu', 'link' => $frontend.'/sehir/istanbul/kadikoy'],
            ['day' => 'Sal', 'task' => 'Güvenli tanışma / SSS story', 'link' => $frontend.'/guvenli-tanisma'],
            ['day' => 'Çar', 'task' => 'Ankara / Çankaya postu', 'link' => $frontend.'/sehir/ankara/cankaya'],
            ['day' => 'Per', 'task' => 'Davet ödülü WhatsApp story', 'link' => $frontend.'/davet'],
            ['day' => 'Cum', 'task' => 'İzmir / Karşıyaka postu', 'link' => $frontend.'/sehir/izmir/karsiyaka'],
            ['day' => 'Cmt', 'task' => 'Başarı hikâyesi paylaşımı', 'link' => $frontend.'/basari-hikayeleri'],
            ['day' => 'Paz', 'task' => 'Üye atmosfer / Reels + bio CTA', 'link' => $frontend.'/register?utm_source=instagram&utm_medium=bio&utm_campaign=weekly'],
        ];
    }

    /**
     * @return list<array{group: string, label: string, url: string, hint: string}>
     */
    private function campaignLinks(string $frontend, string $campaign): array
    {
        $campaign = $campaign !== '' ? $campaign : 'organic';
        $q = fn (array $params): string => $frontend.'/'.ltrim($params['path'] ?? 'register', '/').'?'.http_build_query([
            'utm_source' => $params['source'],
            'utm_medium' => $params['medium'],
            'utm_campaign' => $params['campaign'] ?? $campaign,
        ] + (isset($params['extra']) ? $params['extra'] : []));

        return [
            [
                'group' => 'Instagram',
                'label' => 'Bio link (tek CTA)',
                'url' => $q(['path' => 'register', 'source' => 'instagram', 'medium' => 'bio', 'campaign' => $campaign]),
                'hint' => 'Instagram profil bio’suna yapıştır',
            ],
            [
                'group' => 'Instagram',
                'label' => 'Story / post sticker',
                'url' => $q(['path' => 'register', 'source' => 'instagram', 'medium' => 'story', 'campaign' => $campaign]),
                'hint' => 'Haftalık story CTA',
            ],
            [
                'group' => 'Instagram',
                'label' => 'Kampanya landing',
                'url' => $q(['path' => 'kampanya', 'source' => 'instagram', 'medium' => 'story', 'campaign' => $campaign]),
                'hint' => '/kampanya — Google + e-posta kayıt',
            ],
            [
                'group' => 'Meta Ads',
                'label' => 'Meta test landing',
                'url' => $q(['path' => 'kampanya', 'source' => 'meta', 'medium' => 'paid', 'campaign' => 'test1']),
                'hint' => 'Düşük bütçeli Ads testi',
            ],
            [
                'group' => 'Meta Ads',
                'label' => 'İstanbul Ads',
                'url' => $frontend.'/kampanya?'.http_build_query([
                    'utm_source' => 'meta',
                    'utm_medium' => 'paid',
                    'utm_campaign' => 'istanbul',
                    'city' => 'istanbul',
                ]),
                'hint' => 'Şehir hedefli Meta kampanya',
            ],
            [
                'group' => 'Google Ads',
                'label' => 'Google CPC landing',
                'url' => $q(['path' => 'kampanya', 'source' => 'google', 'medium' => 'cpc', 'campaign' => 'test1']),
                'hint' => 'Search / Performance Max test',
            ],
            [
                'group' => 'SEO',
                'label' => 'İstanbul şehir sayfası',
                'url' => $frontend.'/sehir/istanbul',
                'hint' => 'Organik şehir SEO',
            ],
            [
                'group' => 'SEO',
                'label' => 'Ankara şehir sayfası',
                'url' => $frontend.'/sehir/ankara',
                'hint' => 'Organik şehir SEO',
            ],
            [
                'group' => 'SEO',
                'label' => 'İzmir şehir sayfası',
                'url' => $frontend.'/sehir/izmir',
                'hint' => 'Organik şehir SEO',
            ],
            [
                'group' => 'Davet',
                'label' => 'Üye davet sayfası',
                'url' => $frontend.'/davet',
                'hint' => 'Giriş yapmış üyeler için',
            ],
        ];
    }
}

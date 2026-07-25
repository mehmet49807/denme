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

        return view('admin.marketing', [
            'metrics' => $this->growthMetrics(),
            'frontendUrl' => $frontend,
            'instagramUrl' => rtrim($instagram, '/'),
            'facebookUrl' => (string) $settings->get('facebook_url', ''),
            'marketingNotes' => (string) $settings->get('marketing_notes', ''),
            'defaultCampaign' => (string) $settings->get('marketing_default_campaign', 'organic'),
            'links' => $this->campaignLinks($frontend, (string) $settings->get('marketing_default_campaign', 'organic')),
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
        $base = $frontend.'/marketing/ads';
        $items = [
            [
                'id' => 'web-01-ciddi-iliski',
                'title' => 'Ciddi ilişki arayanlar burada',
                'subtitle' => 'Güvenli tanışma · Evlilik odaklı',
                'format' => '16:9',
                'channel' => 'YouTube / Display / Web',
                'duration_hint' => '~14s',
                'video' => 'web-01-ciddi-iliski.mp4',
                'poster' => 'web-01-ciddi-iliski.png',
                'cta_url' => $frontend.'/kampanya?utm_source=ads&utm_medium=video&utm_campaign=web',
                'group' => 'Web (16:9)',
            ],
            [
                'id' => 'web-02-dogru-insan',
                'title' => 'Doğru insan, doğru yer',
                'subtitle' => 'Kalpten kalbe uzanan köprü',
                'format' => '16:9',
                'channel' => 'YouTube / Display / Web',
                'duration_hint' => '~11s',
                'video' => 'web-02-dogru-insan.mp4',
                'poster' => 'web-02-dogru-insan.png',
                'cta_url' => $frontend.'/kampanya?utm_source=ads&utm_medium=video&utm_campaign=web',
                'group' => 'Web (16:9)',
            ],
            [
                'id' => 'web-03-guvenli',
                'title' => 'Güvenli · Ciddi · Gerçek bağlar',
                'subtitle' => 'Moderasyonlu, ciddi üyelik',
                'format' => '16:9',
                'channel' => 'YouTube / Display / Web',
                'duration_hint' => '~10s',
                'video' => 'web-03-guvenli.mp4',
                'poster' => 'web-03-guvenli.png',
                'cta_url' => $frontend.'/kampanya?utm_source=ads&utm_medium=video&utm_campaign=web',
                'group' => 'Web (16:9)',
            ],
            [
                'id' => 'web-04-evlilik',
                'title' => 'Evlilik hayaline bir adım',
                'subtitle' => 'Gönülden gönüle',
                'format' => '16:9',
                'channel' => 'YouTube / Display / Web',
                'duration_hint' => '~10s',
                'video' => 'web-04-evlilik.mp4',
                'poster' => 'web-04-evlilik.png',
                'cta_url' => $frontend.'/kampanya?utm_source=ads&utm_medium=video&utm_campaign=web',
                'group' => 'Web (16:9)',
            ],
            [
                'id' => 'web-reel-full',
                'title' => 'Web reklam birleşik reel',
                'subtitle' => '4 reklam tek videoda',
                'format' => '16:9',
                'channel' => 'YouTube · Landing · Site içi',
                'duration_hint' => 'birleşik',
                'video' => 'web-reel-full.mp4',
                'poster' => 'web-reel-full.png',
                'cta_url' => $frontend.'/kampanya?utm_source=ads&utm_medium=video&utm_campaign=web',
                'group' => 'Web (16:9)',
            ],
            [
                'id' => 'story-01-ciddi-iliski',
                'title' => 'Ciddi ilişki arayanlar burada',
                'subtitle' => 'Instagram Story / Reels',
                'format' => '9:16',
                'channel' => 'Instagram Story · Reels · TikTok',
                'duration_hint' => 'story',
                'video' => 'story-01-ciddi-iliski.mp4',
                'poster' => 'story-01-ciddi-iliski.png',
                'cta_url' => $frontend.'/register?utm_source=instagram&utm_medium=story&utm_campaign=weekly',
                'group' => 'Story / Reels (9:16)',
            ],
            [
                'id' => 'story-02-dogru-insan',
                'title' => 'Doğru insan, doğru yer',
                'subtitle' => 'Instagram Story / Reels',
                'format' => '9:16',
                'channel' => 'Instagram Story · Reels · TikTok',
                'duration_hint' => 'story',
                'video' => 'story-02-dogru-insan.mp4',
                'poster' => 'story-02-dogru-insan.png',
                'cta_url' => $frontend.'/register?utm_source=instagram&utm_medium=story&utm_campaign=weekly',
                'group' => 'Story / Reels (9:16)',
            ],
            [
                'id' => 'story-03-guvenli',
                'title' => 'Güvenli · Ciddi · Gerçek bağlar',
                'subtitle' => 'Instagram Story / Reels',
                'format' => '9:16',
                'channel' => 'Instagram Story · Reels · TikTok',
                'duration_hint' => 'story',
                'video' => 'story-03-guvenli.mp4',
                'poster' => 'story-03-guvenli.png',
                'cta_url' => $frontend.'/register?utm_source=instagram&utm_medium=story&utm_campaign=weekly',
                'group' => 'Story / Reels (9:16)',
            ],
            [
                'id' => 'story-04-evlilik',
                'title' => 'Evlilik hayaline bir adım',
                'subtitle' => 'Instagram Story / Reels',
                'format' => '9:16',
                'channel' => 'Instagram Story · Reels · TikTok',
                'duration_hint' => 'story',
                'video' => 'story-04-evlilik.mp4',
                'poster' => 'story-04-evlilik.png',
                'cta_url' => $frontend.'/register?utm_source=instagram&utm_medium=story&utm_campaign=weekly',
                'group' => 'Story / Reels (9:16)',
            ],
            [
                'id' => 'story-reel-full',
                'title' => '4 hikâye birleşik reel',
                'subtitle' => 'Instagram Reels uzun versiyon',
                'format' => '9:16',
                'channel' => 'Instagram Reels · TikTok',
                'duration_hint' => 'birleşik',
                'video' => 'story-reel-full.mp4',
                'poster' => 'story-reel-full.png',
                'cta_url' => $frontend.'/register?utm_source=instagram&utm_medium=story&utm_campaign=weekly',
                'group' => 'Story / Reels (9:16)',
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
                'url' => $q(['path' => 'register', 'source' => 'instagram', 'medium' => 'bio', 'campaign' => 'organic']),
                'hint' => 'Instagram profil bio’suna yapıştır',
            ],
            [
                'group' => 'Instagram',
                'label' => 'Story / post sticker',
                'url' => $q(['path' => 'register', 'source' => 'instagram', 'medium' => 'story', 'campaign' => 'weekly']),
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

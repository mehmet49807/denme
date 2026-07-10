<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\SeoHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('feed');
        }

        SeoHelper::setPage('home');
        SeoHelper::set('pageType', 'home');
        SeoHelper::set('canonical', 'https://gonulkoprusu.com/');
        SeoHelper::set('ogImage', 'https://gonulkoprusu.com/images/og-default.jpg');

        $siteUrl = 'https://gonulkoprusu.com';

        return view('web.home', [
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'WebSite',
                        'name' => 'Gönül Köprüsü',
                        'url' => $siteUrl,
                        'description' => SeoHelper::get('description'),
                        'inLanguage' => 'tr-TR',
                        'potentialAction' => [
                            '@type' => 'SearchAction',
                            'target' => $siteUrl.'/ara?q={search_term_string}',
                            'query-input' => 'required name=search_term_string',
                        ],
                    ],
                    [
                        '@type' => 'Organization',
                        'name' => 'Gönül Köprüsü',
                        'url' => $siteUrl,
                        'logo' => $siteUrl.'/images/gonul-koprusu-logo.png',
                        'sameAs' => [
                            'https://www.instagram.com/gonulkoprusucom',
                        ],
                    ],
                ],
            ],
        ]);
    }
}

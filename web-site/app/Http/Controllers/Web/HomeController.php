<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\SeoHelper;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        SeoHelper::setPage('home');
        SeoHelper::set('pageType', 'home');
        SeoHelper::set('canonical', 'https://www.gonulkoprusu.com/');
        SeoHelper::set('ogImage', 'https://www.gonulkoprusu.com/images/og-default.jpg');

        $siteUrl = 'https://www.gonulkoprusu.com';

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
                        'logo' => $siteUrl.'/images/og-default.jpg',
                        'sameAs' => [
                            'https://www.instagram.com/gonulkoprusucom',
                        ],
                    ],
                ],
            ],
        ]);
    }
}

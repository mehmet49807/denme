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

        return view('web.home');
    }
}

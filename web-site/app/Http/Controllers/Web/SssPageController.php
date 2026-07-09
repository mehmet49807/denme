<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PublishedBlogFaqService;
use Illuminate\View\View;

class SssPageController extends Controller
{
  public function __construct(private PublishedBlogFaqService $content) {}

  public function index(): View
  {
    $data = $this->content->all();

    return view('web.sss', [
      'faqItems' => $data['faq_items'],
      'blogPosts' => $data['blog_posts'],
      'publishedAt' => $data['published_at'] ?? null,
    ]);
  }
}

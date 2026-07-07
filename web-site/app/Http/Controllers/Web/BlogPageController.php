<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PublishedBlogFaqService;
use Illuminate\View\View;

class BlogPageController extends Controller
{
  public function __construct(private PublishedBlogFaqService $content) {}

  public function index(): View
  {
    $posts = $this->content->blogPosts();

    return view('web.blog-index', [
      'posts' => $posts,
      'publishedAt' => $this->content->all()['published_at'] ?? null,
    ]);
  }

  public function show(string $slug): View
  {
    $post = $this->content->findBlogPost($slug);

    if (! $post) {
      abort(404);
    }

    return view('web.blog-show', [
      'post' => $post,
      'slug' => $slug,
    ]);
  }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PublishedBlogFaqService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeoPublishSyncController extends Controller
{
  public function __invoke(Request $request, PublishedBlogFaqService $content): JsonResponse
  {
    $expectedKey = (string) config('services.seo.sync_key', env('SEO_SYNC_KEY', 'gk-seo-sync-2026'));

    if (! hash_equals($expectedKey, (string) $request->input('key', ''))) {
      return response()->json(['ok' => false, 'message' => 'Yetkisiz istek.'], 403);
    }

    $payload = $request->input('payload');

    if (! is_array($payload)) {
      return response()->json(['ok' => false, 'message' => 'Geçersiz payload.'], 422);
    }

    if (empty($payload['blog_posts']) && empty($payload['faq_items'])) {
      return response()->json(['ok' => false, 'message' => 'Yayınlanacak içerik bulunamadı.'], 422);
    }

    try {
      $content->save($payload);
    } catch (\Throwable $e) {
      return response()->json(['ok' => false, 'message' => 'Kayıt hatası: '.$e->getMessage()], 500);
    }

    return response()->json([
      'ok' => true,
      'message' => 'Blog / SSS içeriği web sitesine kaydedildi.',
      'blog_count' => count($payload['blog_posts'] ?? []),
      'faq_count' => count($payload['faq_items'] ?? []),
    ]);
  }
}

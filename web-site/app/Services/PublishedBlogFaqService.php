<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PublishedBlogFaqService
{
  private const STORAGE_PATH = 'seo/openrouter-published-blog-faq.json';

  /** @return array{blog_posts: array<int, array<string, mixed>>, faq_items: array<int, array<string, string>>, published_at?: string} */
  public function all(): array
  {
    $payload = $this->readPayload();

    return [
      'published_at' => is_string($payload['published_at'] ?? null) ? $payload['published_at'] : null,
      'blog_posts' => is_array($payload['blog_posts'] ?? null) ? $payload['blog_posts'] : [],
      'faq_items' => is_array($payload['faq_items'] ?? null) ? $payload['faq_items'] : [],
    ];
  }

  /** @return array<int, array<string, mixed>> */
  public function blogPosts(): array
  {
    return $this->all()['blog_posts'];
  }

  /** @return array<int, array<string, string>> */
  public function faqItems(): array
  {
    return $this->all()['faq_items'];
  }

  public function findBlogPost(string $slug): ?array
  {
    $slug = trim($slug);

    foreach ($this->blogPosts() as $post) {
      if (! is_array($post)) {
        continue;
      }

      if ((string) ($post['slug'] ?? '') === $slug) {
        return $post;
      }
    }

    return null;
  }

  /** @param array<string, mixed> $payload */
  public function save(array $payload): void
  {
    $normalized = [
      'published_at' => is_string($payload['published_at'] ?? null) ? $payload['published_at'] : now()->toIso8601String(),
      'blog_posts' => is_array($payload['blog_posts'] ?? null) ? $payload['blog_posts'] : [],
      'faq_items' => is_array($payload['faq_items'] ?? null) ? $payload['faq_items'] : [],
      'internal_links' => is_array($payload['internal_links'] ?? null) ? $payload['internal_links'] : [],
    ];

    $json = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

    Storage::put(self::STORAGE_PATH, $json);

    foreach ($this->candidatePaths() as $path) {
      if ($path === storage_path('app/'.self::STORAGE_PATH)) {
        continue;
      }

      File::ensureDirectoryExists(dirname($path));
      File::put($path, $json);
    }
  }

  /** @return array<string, mixed> */
  private function readPayload(): array
  {
    foreach ($this->candidatePaths() as $path) {
      if (! is_file($path)) {
        continue;
      }

      $decoded = json_decode((string) file_get_contents($path), true);

      if (is_array($decoded)) {
        return $decoded;
      }
    }

    return ['blog_posts' => [], 'faq_items' => []];
  }

  /** @return list<string> */
  private function candidatePaths(): array
  {
    return array_values(array_unique(array_filter([
      storage_path('app/'.self::STORAGE_PATH),
      base_path('storage/app/'.self::STORAGE_PATH),
      base_path('../public_html/storage/app/'.self::STORAGE_PATH),
    ])));
  }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\SeoHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    private function legalData(): array
    {
        return [
            'lastUpdated' => '5 Haziran 2026',
            'contactEmail' => 'destek@gonulkoprusu.com',
        ];
    }

    public function privacy(): View
    {
        return view('web.privacy', $this->legalData());
    }

    public function terms(): View
    {
        return view('web.terms', $this->legalData());
    }

    public function complaints(): View
    {
        return view('web.complaints', $this->legalData());
    }

    public function safeMeeting(): View
    {
        return view('web.safe-meeting', $this->legalData());
    }

    public function about(): View
    {
        SeoHelper::setPage('about');
        SeoHelper::set('canonical', url('/hakkimizda'));

        return view('web.about', array_merge($this->legalData(), [
            'jsonLd' => $this->breadcrumbSchema('Hakkımızda', url('/hakkimizda')),
        ]));
    }

    public function kvkk(): View
    {
        return view('web.kvkk', $this->legalData());
    }

    public function blog(): View
    {
        $published = $this->publishedBlogFaq();
        SeoHelper::setPage('blog');
        SeoHelper::set('canonical', url('/blog'));

        return view('web.blog', array_merge($this->legalData(), [
            'posts' => $published['blog_posts'],
            'publishedAt' => $published['published_at'] ?? null,
            'jsonLd' => $this->blogIndexSchema($published['blog_posts']),
        ]));
    }

    public function blogShow(string $slug): View
    {
        $published = $this->publishedBlogFaq();
        $post = null;

        foreach ($published['blog_posts'] as $candidate) {
            if (is_array($candidate) && (string) ($candidate['slug'] ?? '') === $slug) {
                $post = $candidate;
                break;
            }
        }

        if (! $post) {
            abort(404);
        }

        SeoHelper::setBlogPost($post, $slug);

        return view('web.blog-show', array_merge($this->legalData(), [
            'post' => $post,
            'slug' => $slug,
            'jsonLd' => $this->blogPostSchema($post, $slug),
        ]));
    }

    public function sss(): View
    {
        $published = $this->publishedBlogFaq();
        SeoHelper::setPage('sss');
        SeoHelper::set('canonical', url('/sss'));

        return view('web.sss', array_merge($this->legalData(), [
            'faqItems' => $published['faq_items'],
            'posts' => $published['blog_posts'],
            'publishedAt' => $published['published_at'] ?? null,
            'jsonLd' => $this->faqPageSchema($published['faq_items']),
        ]));
    }

    /** @return array{blog_posts: array<int, array<string, mixed>>, faq_items: array<int, array<string, string>>, published_at?: string|null} */
    private function publishedBlogFaq(): array
    {
        foreach ($this->publishedBlogFaqPaths() as $path) {
            if (! is_file($path)) {
                continue;
            }

            $decoded = json_decode((string) file_get_contents($path), true);

            if (is_array($decoded)) {
                return [
                    'published_at' => $decoded['published_at'] ?? null,
                    'blog_posts' => is_array($decoded['blog_posts'] ?? null) ? $decoded['blog_posts'] : [],
                    'faq_items' => is_array($decoded['faq_items'] ?? null) ? $decoded['faq_items'] : [],
                ];
            }
        }

        return ['blog_posts' => [], 'faq_items' => [], 'published_at' => null];
    }

    /** @return list<string> */
    private function publishedBlogFaqPaths(): array
    {
        return array_values(array_unique(array_filter([
            storage_path('app/seo/openrouter-published-blog-faq.json'),
            base_path('storage/app/seo/openrouter-published-blog-faq.json'),
            base_path('../public_html/storage/app/seo/openrouter-published-blog-faq.json'),
        ])));
    }

    /** @param array<string, mixed> $payload */
    public static function storePublishedBlogFaq(array $payload): void
    {
        $normalized = [
            'published_at' => is_string($payload['published_at'] ?? null) ? $payload['published_at'] : now()->toIso8601String(),
            'blog_posts' => is_array($payload['blog_posts'] ?? null) ? $payload['blog_posts'] : [],
            'faq_items' => is_array($payload['faq_items'] ?? null) ? $payload['faq_items'] : [],
            'internal_links' => is_array($payload['internal_links'] ?? null) ? $payload['internal_links'] : [],
        ];

        $json = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        Storage::put('seo/openrouter-published-blog-faq.json', $json);

        foreach ([
            storage_path('app/seo/openrouter-published-blog-faq.json'),
            base_path('../public_html/storage/app/seo/openrouter-published-blog-faq.json'),
        ] as $path) {
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $json);
        }

        Cache::forget('sitemap.xml.body');
    }

    /** @param array<int, array<string, mixed>> $posts */
    private function blogIndexSchema(array $posts): array
    {
        $siteUrl = rtrim(config('app.url', 'https://gonulkoprusu.com'), '/');
        $items = [];
        foreach (array_slice($posts, 0, 20) as $post) {
            $slug = (string) ($post['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $items[] = [
                '@type' => 'BlogPosting',
                'headline' => (string) ($post['title'] ?? 'Blog'),
                'description' => (string) ($post['description'] ?? ''),
                'url' => $siteUrl.'/blog/'.$slug,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Blog',
                    'name' => 'Gönül Köprüsü Blog',
                    'url' => $siteUrl.'/blog',
                    'description' => 'Ciddi ilişki, güvenli tanışma ve evlilik odaklı rehber yazıları.',
                    'blogPost' => $items,
                ],
                $this->breadcrumbSchema('Blog', $siteUrl.'/blog'),
            ],
        ];
    }

    /** @param array<string, mixed> $post */
    private function blogPostSchema(array $post, string $slug): array
    {
        $siteUrl = rtrim(config('app.url', 'https://gonulkoprusu.com'), '/');
        $url = $siteUrl.'/blog/'.$slug;

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'BlogPosting',
                    'headline' => (string) ($post['title'] ?? 'Blog'),
                    'description' => (string) ($post['description'] ?? ''),
                    'url' => $url,
                    'mainEntityOfPage' => $url,
                    'author' => ['@type' => 'Organization', 'name' => 'Gönül Köprüsü'],
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => 'Gönül Köprüsü',
                        'url' => $siteUrl,
                    ],
                    'dateModified' => (string) ($post['updated_at'] ?? now()->toDateString()),
                ],
                $this->breadcrumbSchema((string) ($post['title'] ?? 'Blog'), $url, 'Blog', $siteUrl.'/blog'),
            ],
        ];
    }

    /** @param array<int, array<string, string>> $faqItems */
    private function faqPageSchema(array $faqItems): array
    {
        $entities = [];
        foreach ($faqItems as $item) {
            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));
            if ($question === '' || $answer === '') {
                continue;
            }
            $entities[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $answer,
                ],
            ];
        }

        $graph = [$this->breadcrumbSchema('Sıkça Sorulan Sorular', url('/sss'))];
        if ($entities !== []) {
            $graph[] = [
                '@type' => 'FAQPage',
                'mainEntity' => $entities,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }

    private function breadcrumbSchema(string $name, string $url, ?string $parentName = null, ?string $parentUrl = null): array
    {
        $items = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana Sayfa', 'item' => url('/')],
        ];
        if ($parentName && $parentUrl) {
            $items[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $parentName, 'item' => $parentUrl];
            $items[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $url];
        } else {
            $items[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $name, 'item' => $url];
        }

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }
}

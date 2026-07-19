<?php

namespace App\Support;

/**
 * Ortak JSON-LD parçaları (FAQ, breadcrumb, WebApplication).
 */
final class SeoSchema
{
    /** @param array<int, array{question?: string, answer?: string}> $faqItems */
    public static function faqPage(array $faqItems, ?array $breadcrumb = null): array
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

        $graph = [];
        if ($breadcrumb) {
            $graph[] = $breadcrumb;
        }
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

    public static function breadcrumb(string $name, string $url, ?string $parentName = null, ?string $parentUrl = null): array
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

    public static function webApplication(string $siteUrl, string $description): array
    {
        return [
            '@type' => 'WebApplication',
            'name' => 'Gönül Köprüsü',
            'url' => $siteUrl,
            'applicationCategory' => 'LifestyleApplication',
            'operatingSystem' => 'Web',
            'inLanguage' => 'tr-TR',
            'description' => $description,
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'TRY',
                'description' => 'Ücretsiz üyelik — kadınlarda mesajlaşma ücretsiz',
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Section\SectionItemsTrait;

/**
 * Rendu HTML spécifique aux variantes blog (conversion des blocs React).
 */
final class BlogVariantRenderer
{
    use SectionItemsTrait;
    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'blog7' => 6,
        'blog8' => 4,
    ];

    /** @var list<string> */
    private const DEFAULT_IMAGES = [
        'images/1-1x1.jpg',
        'lummi/bw12.jpeg',
        'lummi/bw15.jpeg',
        'lummi/bw20.jpeg',
        'lummi/bw21.jpeg',
        'placeholder-1.svg',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $readMore = trim((string) ($content['read_more_label'] ?? ''));
        if ($readMore === '') {
            $readMore = 'Lire l\'article';
        }
        $safeReadMore = htmlspecialchars($readMore, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $items = self::items($content, $variant);

        $data['blog_posts_html'] = match ($variant) {
            'blog8' => self::postsBlog8Html($items, $safeReadMore),
            default => self::postsBlog7Html($items, $safeReadMore),
        };

        return $data;
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private static function postsBlog7Html(array $items, string $readMore): string
    {
        $html = '';
        foreach ($items as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $summary = trim((string) ($item['text'] ?? ''));
            $author = trim((string) ($item['author'] ?? ''));
            $published = trim((string) ($item['published'] ?? $item['date'] ?? ''));
            $href = self::href((string) ($item['href'] ?? '#'));
            $imageUrl = self::imageUrl((string) ($item['url'] ?? ''), $index);
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeSummary = htmlspecialchars($summary, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeImageUrl = htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeAlt = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $metaParts = [];
            if ($author !== '') {
                $metaParts[] = htmlspecialchars($author, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
            if ($published !== '') {
                $metaParts[] = htmlspecialchars($published, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
            $metaHtml = $metaParts !== []
                ? '<p class="section-blog__card-meta--blog7">' . implode(' · ', $metaParts) . '</p>'
                : '';

            $html .= '<article class="section-blog__card--blog7">'
                . '<a class="section-blog__media--blog7" href="' . $href . '">'
                . '<img class="section-blog__img--blog7" src="' . $safeImageUrl . '" alt="' . $safeAlt
                . '" width="640" height="360" loading="lazy" decoding="async" />'
                . '</a>'
                . '<div class="section-blog__card-head--blog7">'
                . '<h3 class="section-blog__card-title--blog7"><a href="' . $href . '">' . $safeTitle . '</a></h3>'
                . $metaHtml
                . '</div>'
                . ($summary !== ''
                    ? '<div class="section-blog__card-body--blog7"><p class="section-blog__card-summary--blog7">'
                        . $safeSummary . '</p></div>'
                    : '')
                . '<div class="section-blog__card-foot--blog7">'
                . '<a class="section-blog__read-more--blog7" href="' . $href . '">'
                . $readMore . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>'
                . '</div>'
                . '</article>';
        }

        return $html;
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private static function postsBlog8Html(array $items, string $readMore): string
    {
        $html = '';
        foreach ($items as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $summary = trim((string) ($item['text'] ?? ''));
            $author = trim((string) ($item['author'] ?? ''));
            $published = trim((string) ($item['published'] ?? $item['date'] ?? ''));
            $tagsRaw = trim((string) ($item['label'] ?? ''));
            $href = self::href((string) ($item['href'] ?? '#'));
            $imageUrl = self::imageUrl((string) ($item['url'] ?? ''), $index);
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeSummary = htmlspecialchars($summary, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeImageUrl = htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeAlt = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $tagsHtml = '';
            if ($tagsRaw !== '') {
                $tags = array_values(array_filter(array_map('trim', explode(',', $tagsRaw))));
                if ($tags !== []) {
                    $tagSpans = '';
                    foreach ($tags as $tag) {
                        $tagSpans .= '<span>' . htmlspecialchars($tag, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>';
                    }
                    $tagsHtml = '<div class="section-blog__tags--blog8">' . $tagSpans . '</div>';
                }
            }

            $metaHtml = '';
            if ($author !== '' || $published !== '') {
                $metaHtml = '<div class="section-blog__row-meta--blog8">';
                if ($author !== '') {
                    $metaHtml .= '<span>' . htmlspecialchars($author, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>';
                }
                if ($author !== '' && $published !== '') {
                    $metaHtml .= '<span class="section-blog__row-meta-sep--blog8" aria-hidden="true">•</span>';
                }
                if ($published !== '') {
                    $metaHtml .= '<span>' . htmlspecialchars($published, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>';
                }
                $metaHtml .= '</div>';
            }

            $html .= '<article class="section-blog__row--blog8">'
                . '<div class="section-blog__row-inner--blog8">'
                . '<div class="section-blog__row-content--blog8">'
                . $tagsHtml
                . '<h3 class="section-blog__row-title--blog8"><a href="' . $href . '">' . $safeTitle . '</a></h3>'
                . ($summary !== ''
                    ? '<p class="section-blog__row-summary--blog8">' . $safeSummary . '</p>'
                    : '')
                . $metaHtml
                . '<a class="section-blog__read-more--blog8" href="' . $href . '">'
                . '<span>' . $readMore . '</span>'
                . '<i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>'
                . '</div>'
                . '<div class="section-blog__row-media--blog8">'
                . '<a href="' . $href . '">'
                . '<div class="section-blog__media-frame--blog8">'
                . '<img class="section-blog__img--blog8" src="' . $safeImageUrl . '" alt="' . $safeAlt
                . '" width="640" height="360" loading="lazy" decoding="async" />'
                . '</div>'
                . '</a>'
                . '</div>'
                . '</div>'
                . '</article>';
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    private static function items(array $content, string $variant): array
    {
        return self::itemsFromContent($content, self::MAX_ITEMS[$variant] ?? 6);
    }

    private static function imageUrl(string $url, int $index): string
    {
        $fallbackFile = self::DEFAULT_IMAGES[$index % count(self::DEFAULT_IMAGES)];

        return self::imageUrlFromItem($url, $index, 'features', $fallbackFile);
    }

    private static function href(string $href): string
    {
        return self::hrefFromItem($href);
    }
}

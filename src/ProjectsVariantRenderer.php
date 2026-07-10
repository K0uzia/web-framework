<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Rendu HTML spécifique aux variantes projects (conversion des blocs React).
 */
final class ProjectsVariantRenderer
{
    private const MAX_ITEMS = 12;

    /** @var list<string> */
    private const DEFAULT_IMAGES = [
        'lummi/bw12.jpeg',
        'lummi/bw15.jpeg',
        'lummi/bw20.jpeg',
        'lummi/bw21.jpeg',
        'images/1-1x1.jpg',
        'saas-detail-1-1x1.png',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['projects_items_html'] = match ($variant) {
            'projects5' => self::itemsProjects5Html($content),
            default => '',
        };

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function itemsProjects5Html(array $content): string
    {
        $html = '';
        foreach (self::items($content) as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $type = trim((string) ($item['label'] ?? ''));
            $year = trim((string) ($item['date'] ?? $item['year'] ?? ''));
            $href = self::href((string) ($item['href'] ?? '#'));
            $imageUrl = self::imageUrl((string) ($item['url'] ?? ''), $index);
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeType = htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeYear = htmlspecialchars($year, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeImageUrl = htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeAlt = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<article class="section-projects__card--projects5">'
                . '<a class="section-projects__media--projects5" href="' . $href . '">'
                . '<img class="section-projects__img--projects5" src="' . $safeImageUrl . '" alt="' . $safeAlt
                . '" width="640" height="384" loading="lazy" decoding="async" />'
                . '</a>'
                . '<div class="section-projects__foot--projects5">'
                . '<div class="section-projects__meta--projects5">'
                . '<h3 class="section-projects__card-title--projects5">' . $safeTitle . '</h3>'
                . ($type !== '' ? '<p class="section-projects__card-type--projects5">' . $safeType . '</p>' : '')
                . '</div>'
                . ($year !== '' ? '<div class="section-projects__year--projects5">' . $safeYear . '</div>' : '')
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
    private static function items(array $content): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $items = [];
        foreach (array_slice($raw, 0, self::MAX_ITEMS) as $item) {
            if (is_array($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    private static function imageUrl(string $url, int $index): string
    {
        $fallbackFile = self::DEFAULT_IMAGES[$index % count(self::DEFAULT_IMAGES)];

        return SectionAssets::resolve(
            $url,
            SectionAssets::shared('features', $fallbackFile),
        );
    }

    private static function href(string $href): string
    {
        $trimmed = trim($href);

        return htmlspecialchars($trimmed !== '' ? $trimmed : '#', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

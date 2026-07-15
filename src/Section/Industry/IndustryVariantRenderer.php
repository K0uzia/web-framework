<?php

declare(strict_types=1);

namespace Capsule\Section\Industry;

use Capsule\SectionAssets;

use Capsule\Section\SectionItemsTrait;

/**
 * Rendu HTML spécifique aux variantes industry (conversion des blocs React).
 */
final class IndustryVariantRenderer
{
    use SectionItemsTrait;

    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'industries1' => 8,
        'industries2' => 8,
    ];

    /** @var list<string> */
    private const INDUSTRIES1_IMAGES = [
        'placeholder-1.svg',
        'placeholder-2.svg',
        'placeholder-3.svg',
        'placeholder-4.svg',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        return match ($variant) {
            'industries2' => self::enrichIndustries2($data, $content),
            default => self::enrichIndustries1($data, $content),
        };
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichIndustries1(array $data, array $content): array
    {
        $overviewLabel = trim((string) ($content['overview_label'] ?? ''));
        if ($overviewLabel === '') {
            $overviewLabel = 'Aperçu';
        }
        $safeOverview = htmlspecialchars($overviewLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = '';
        $index = 0;
        foreach (self::itemsFromContent($content, self::MAX_ITEMS['industries1']) as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $description = trim((string) ($item['text'] ?? ''));
            $href = self::hrefFromItem((string) ($item['href'] ?? '#'));
            $imageUrl = self::imageUrl((string) ($item['url'] ?? ''), $index);
            $altRaw = trim((string) ($item['image_alt'] ?? ''));
            $alt = $altRaw !== '' ? $altRaw : $title;

            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeImageUrl = htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeAlt = htmlspecialchars($alt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<a class="section-industry__card-link--industries1" href="' . $href . '">'
                . '<article class="section-industry__card--industries1">'
                . '<div class="section-industry__front--industries1">'
                . '<img class="section-industry__img--industries1" src="' . $safeImageUrl . '" alt="' . $safeAlt
                . '" width="480" height="640" loading="lazy" decoding="async" />'
                . '<h3 class="section-industry__card-title--industries1">' . $safeTitle . '</h3>'
                . '</div>'
                . '<div class="section-industry__overlay--industries1" aria-hidden="true"></div>'
                . '<div class="section-industry__hover--industries1">'
                . '<div class="section-industry__hover-inner--industries1">'
                . '<p class="section-industry__hover-label--industries1">' . $safeOverview . ' :</p>'
                . ($description !== ''
                    ? '<p class="section-industry__hover-text--industries1">' . $safeDescription . '</p>'
                    : '')
                . '</div>'
                . '</div>'
                . '<span class="section-industry__plus--industries1" aria-hidden="true">'
                . '<i class="fa-solid fa-plus"></i>'
                . '</span>'
                . '</article>'
                . '</a>';

            $index++;
        }

        $data['industry_cards_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichIndustries2(array $data, array $content): array
    {
        $badge = trim((string) ($content['badge'] ?? ''));
        $data['industry_badge_html'] = $badge !== ''
            ? '<span class="section-industry__badge--industries2">' . htmlspecialchars($badge, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
            : '';

        $html = '';
        $index = 0;
        foreach (self::itemsFromContent($content, self::MAX_ITEMS['industries2']) as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $description = trim((string) ($item['text'] ?? ''));
            $imageUrl = self::imageUrl((string) ($item['url'] ?? ''), $index);
            $altRaw = trim((string) ($item['image_alt'] ?? ''));
            $alt = $altRaw !== '' ? $altRaw : $title;

            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeImageUrl = htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeAlt = htmlspecialchars($alt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<div class="section-industry__row--industries2">'
                . '<div class="section-industry__row-grid--industries2">'
                . '<div class="section-industry__row-title-wrap--industries2">'
                . '<h3 class="section-industry__row-title--industries2">' . $safeTitle . '</h3>'
                . '</div>'
                . '<div class="section-industry__row-media-wrap--industries2">'
                . '<img class="section-industry__row-img--industries2" src="' . $safeImageUrl . '" alt="' . $safeAlt
                . '" width="64" height="64" loading="lazy" decoding="async" />'
                . '</div>'
                . '<div class="section-industry__row-text-wrap--industries2">'
                . ($description !== ''
                    ? '<p class="section-industry__row-text--industries2">' . $safeDescription . '</p>'
                    : '')
                . '</div>'
                . '</div>'
                . '</div>';

            $index++;
        }

        $data['industry_rows_html'] = $html;

        return $data;
    }

    private static function imageUrl(string $url, int $index): string
    {
        $fallbackFile = self::INDUSTRIES1_IMAGES[$index % count(self::INDUSTRIES1_IMAGES)];

        return SectionAssets::resolve(
            $url,
            SectionAssets::shared('features', $fallbackFile),
        );
    }
}

<?php

declare(strict_types=1);

namespace Capsule\Section\List;

use Capsule\FontAwesomeIcon;

use Capsule\Section\SectionItemsTrait;

/**
 * Rendu HTML spécifique aux variantes list (conversion des blocs React).
 */
final class ListVariantRenderer
{
    use SectionItemsTrait;

    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'list2' => 12,
    ];

    /** @var list<string> */
    private const LIST2_ICONS = [
        'fa-star',
        'fa-circle-check',
        'fa-lightbulb',
        'fa-handshake',
        'fa-briefcase',
        'fa-leaf',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['list_rows_html'] = match ($variant) {
            default => self::rowsList2Html($content),
        };

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function rowsList2Html(array $content): string
    {
        $readMore = trim((string) ($content['read_more_label'] ?? ''));
        if ($readMore === '') {
            $readMore = 'Voir le projet';
        }
        $safeReadMore = htmlspecialchars($readMore, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = '<div class="section-list__separator--list2" role="separator" aria-hidden="true"></div>';
        $index = 0;
        foreach (self::itemsFromContent($content, self::MAX_ITEMS['list2']) as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $category = trim((string) ($item['label'] ?? ''));
            $description = trim((string) ($item['text'] ?? ''));
            $href = self::hrefFromItem((string) ($item['href'] ?? '#'));
            $ctaLabel = trim((string) ($item['cta_label'] ?? ''));
            if ($ctaLabel === '') {
                $ctaLabel = $readMore;
            }
            $safeCtaLabel = htmlspecialchars($ctaLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $iconClass = self::iconClass((string) ($item['icon'] ?? ''), $index);
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeCategory = htmlspecialchars($category, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<div class="section-list__row--list2">'
                . '<div class="section-list__lead--list2">'
                . '<span class="section-list__icon-wrap--list2">'
                . '<i class="' . $iconClass . '" aria-hidden="true"></i>'
                . '</span>'
                . '<div class="section-list__meta--list2">'
                . '<h3 class="section-list__item-title--list2">' . $safeTitle . '</h3>'
                . ($category !== ''
                    ? '<p class="section-list__item-category--list2">' . $safeCategory . '</p>'
                    : '')
                . '</div>'
                . '</div>'
                . ($description !== ''
                    ? '<p class="section-list__item-text--list2">' . $safeDescription . '</p>'
                    : '<p class="section-list__item-text--list2"></p>')
                . '<a class="section-list__item-link--list2" href="' . $href . '">'
                . '<span>' . $safeCtaLabel . '</span>'
                . '<i class="fa-solid fa-arrow-right" aria-hidden="true"></i>'
                . '</a>'
                . '</div>'
                . '<div class="section-list__separator--list2" role="separator" aria-hidden="true"></div>';

            $index++;
        }

        return $html;
    }

    private static function iconClass(string $rawIcon, int $index): string
    {
        $glyph = FontAwesomeIcon::glyph(
            $rawIcon,
            self::LIST2_ICONS[$index % count(self::LIST2_ICONS)],
        );

        return FontAwesomeIcon::solidClass($glyph);
    }
}

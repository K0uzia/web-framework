<?php

declare(strict_types=1);

namespace Capsule\Section\Stats;

/**
 * Rendu HTML spécifique aux variantes stats (conversion des blocs React).
 */
final class StatsVariantRenderer
{
    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'stats6' => 4,
        'stats8' => 8,
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['stats_items_html'] = match ($variant) {
            'stats8' => self::itemsStats8Html($content),
            default => self::itemsStats6Html($content),
        };
        $data['stats_link_html'] = $variant === 'stats8' ? self::linkStats8Html($content) : '';

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function itemsStats6Html(array $content): string
    {
        $html = '';
        foreach (self::items($content, 'stats6') as $item) {
            $value = trim((string) ($item['title'] ?? ''));
            $label = trim((string) ($item['label'] ?? ''));
            if ($value === '' && $label === '') {
                continue;
            }
            $safeValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<div class="section-stats__item--stats6">'
                . '<div class="section-stats__value--stats6">' . $safeValue . '</div>'
                . '<div class="section-stats__label--stats6">' . $safeLabel . '</div>'
                . '</div>';
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function itemsStats8Html(array $content): string
    {
        $html = '';
        foreach (self::items($content, 'stats8') as $item) {
            $value = trim((string) ($item['title'] ?? ''));
            $label = trim((string) ($item['label'] ?? ''));
            if ($value === '' && $label === '') {
                continue;
            }
            $safeValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<div class="section-stats__item--stats8">'
                . '<div class="section-stats__value--stats8">' . $safeValue . '</div>'
                . '<p class="section-stats__label--stats8">' . $safeLabel . '</p>'
                . '</div>';
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function linkStats8Html(array $content): string
    {
        $label = trim((string) ($content['link_label'] ?? $content['cta_label'] ?? ''));
        $href = trim((string) ($content['href'] ?? $content['cta_href'] ?? ''));
        if ($label === '' || $href === '') {
            return '';
        }
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeHref = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<a class="section-stats__link--stats8" href="' . $safeHref . '">'
            . $safeLabel
            . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>';
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    private static function items(array $content, string $variant): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $max = self::MAX_ITEMS[$variant] ?? 4;

        return array_slice(array_values(array_filter($raw, 'is_array')), 0, $max);
    }
}

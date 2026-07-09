<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Rendu HTML spécifique aux variantes integrations (conversion des blocs React).
 */
final class IntegrationVariantRenderer
{
    private const SHARED = 'integrations';

    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'integration3' => 6,
        'integration9' => 8,
    ];

    /** @var list<string> */
    private const DEFAULT_LOGOS = [
        'google-icon.svg',
        'slack-icon.svg',
        'sketch-icon.svg',
        'gatsby-icon.svg',
        'spotify-icon.svg',
        'github-icon.svg',
        'figma-icon.svg',
        'dropbox-icon.svg',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['integration_items_html'] = '';

        return match ($variant) {
            'integration3' => self::enrichIntegration3($data, $content),
            'integration9' => self::enrichIntegration9($data, $content),
            default => $data,
        };
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichIntegration3(array $data, array $content): array
    {
        $items = self::items($content, 'integration3');
        $html = '';
        foreach ($items as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            $text = trim((string) ($item['text'] ?? ''));
            $icon = self::itemIcon($item, $index, $title, 48);
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeText = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<article class="section-integrations__row">'
                . '<div class="section-integrations__row-icon">' . $icon . '</div>'
                . '<div class="section-integrations__row-copy">'
                . '<h3 class="section-integrations__row-title">' . $safeTitle . '</h3>'
                . '<p class="section-integrations__row-text">' . $safeText . '</p>'
                . '</div>'
                . '</article>';
        }
        $data['integration_items_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichIntegration9(array $data, array $content): array
    {
        $items = self::items($content, 'integration9');
        $html = '';
        foreach ($items as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            $text = trim((string) ($item['text'] ?? ''));
            $icon = self::itemIcon($item, $index, $title, 32);
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeText = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<article class="section-integrations__card">'
                . '<div class="section-integrations__card-icon">' . $icon . '</div>'
                . '<h3 class="section-integrations__card-title">' . $safeTitle . '</h3>'
                . '<p class="section-integrations__card-text">' . $safeText . '</p>'
                . '</article>';
        }
        $data['integration_items_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    private static function items(array $content, string $variant): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $max = self::MAX_ITEMS[$variant] ?? 6;
        $items = [];
        foreach (array_slice($raw, 0, $max) as $item) {
            if (is_array($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function itemIcon(array $item, int $index, string $alt, int $size): string
    {
        $fallback = self::DEFAULT_LOGOS[$index % count(self::DEFAULT_LOGOS)];
        $url = SectionAssets::resolve(
            (string) ($item['url'] ?? ''),
            SectionAssets::shared(self::SHARED, 'logos/' . $fallback),
        );
        if ($url === '') {
            return '';
        }
        $safeAlt = htmlspecialchars($alt !== '' ? $alt : 'Logo', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<img class="section-integrations__logo" src="' . $safeUrl . '" alt="' . $safeAlt
            . '" width="' . $size . '" height="' . $size . '" loading="lazy" decoding="async" />';
    }
}

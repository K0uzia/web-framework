<?php

declare(strict_types=1);

namespace Capsule\Section\Logos;

use Capsule\SectionAssets;
use Capsule\ThemeColor;

/**
 * Rendu HTML spécifique aux variantes logos (conversion des blocs React).
 */
final class LogosVariantRenderer
{
    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'logos3' => 12,
        'logos8' => 6,
        'logos18' => 6,
        'logos19' => 12,
    ];

    /** @var list<string> */
    private const DEFAULT_LOGOS = [
        'logos/fictional-company-logo-1.svg',
        'logos/fictional-company-logo-2.svg',
        'logos/fictional-company-logo-3.svg',
        'logos/fictional-company-logo-4.svg',
        'logos/fictional-company-logo-5.svg',
        'logos/fictional-company-logo-6.svg',
        'logos/fictional-company-logo-7.svg',
        'logos/fictional-company-logo-8.svg',
        'logos/fictional-company-logo-9.svg',
        'logos/fictional-company-logo-10.svg',
        'logos/fictional-company-logo-11.svg',
        'logos/fictional-company-logo-12.svg',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $logoColor = trim((string) ($data['style_logo_color'] ?? ''));
        $data['logos_tint_color'] = $logoColor !== ''
            ? ThemeColor::normalize($logoColor, '#0f172a')
            : '';

        $itemsHtml = self::itemsHtml($content, $variant, $data['logos_tint_color']);
        $data['logos_grid_html'] = in_array($variant, ['logos8', 'logos18'], true) ? $itemsHtml : '';
        $data['logos_marquee_html'] = in_array($variant, ['logos3', 'logos19'], true)
            ? self::marqueeHtml($itemsHtml, $variant)
            : '';

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function itemsHtml(array $content, string $variant, string $tintColor): string
    {
        $html = '';
        foreach (self::items($content, $variant) as $index => $item) {
            $html .= self::logoCellHtml($item, $index, $variant, $tintColor);
        }

        return $html;
    }

    private static function marqueeHtml(string $itemsHtml, string $variant): string
    {
        if ($itemsHtml === '') {
            return '';
        }

        return '<div class="section-logos__marquee--' . $variant . '" aria-label="Logos partenaires">'
            . '<div class="section-logos__marquee-track--' . $variant . '">'
            . $itemsHtml . $itemsHtml
            . '</div></div>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function logoCellHtml(array $item, int $index, string $variant, string $tintColor): string
    {
        $alt = trim((string) ($item['title'] ?? ''));
        if ($alt === '') {
            $alt = 'Logo partenaire ' . ($index + 1);
        }
        $imageUrl = self::logoUrl((string) ($item['url'] ?? ''), $index);
        $href = trim((string) ($item['href'] ?? ''));
        $safeAlt = htmlspecialchars($alt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeUrl = htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $imgClass = 'section-logos__img--' . $variant
            . ($index === 4 ? ' section-logos__img--compact--' . $variant : '');

        if ($tintColor !== '' && preg_match('/^#[0-9a-f]{6}$/', $tintColor) === 1) {
            $safeTint = htmlspecialchars($tintColor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $compactClass = $index === 4 ? ' section-logos__logo-tint--compact--' . $variant : '';
            $logoMarkup = '<span class="section-logos__logo-tint section-logos__logo-tint--' . $variant . $compactClass . '" role="img" aria-label="'
                . $safeAlt . '" style="--logo-mask:url(\'' . $safeUrl . '\');--section-logos-color:' . $safeTint . '"></span>';
        } else {
            $logoMarkup = '<img class="' . $imgClass . '" src="' . $safeUrl . '" alt="' . $safeAlt
                . '" width="128" height="48" loading="lazy" decoding="async" />';
        }

        $inner = '<div class="section-logos__cell--' . $variant . '">' . $logoMarkup . '</div>';

        if ($href !== '') {
            $safeHref = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return '<a class="section-logos__link--' . $variant . '" href="' . $safeHref
                . '" rel="noopener noreferrer">' . $inner . '</a>';
        }

        return $inner;
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    private static function items(array $content, string $variant): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $max = self::MAX_ITEMS[$variant] ?? 12;
        $items = [];
        foreach (array_slice($raw, 0, $max) as $item) {
            if (is_array($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    private static function logoUrl(string $url, int $index): string
    {
        $fallbackFile = self::DEFAULT_LOGOS[$index % count(self::DEFAULT_LOGOS)];

        return SectionAssets::resolve(
            $url,
            SectionAssets::shared('hero', $fallbackFile),
        );
    }
}

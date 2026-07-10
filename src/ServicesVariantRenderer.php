<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Rendu HTML spécifique aux variantes services (conversion des blocs React).
 */
final class ServicesVariantRenderer
{
    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'services4' => 8,
        'services12' => 5,
    ];

    /** @var list<string> */
    private const DEFAULT_ICONS = [
        'fa-gear',
        'fa-palette',
        'fa-code',
        'fa-leaf',
    ];

    /** @var list<string> */
    private const DEFAULT_IMAGES = [
        'lummi/bw12.jpeg',
        'lummi/bw15.jpeg',
        'lummi/bw20.jpeg',
        'lummi/bw21.jpeg',
        'images/1-1x1.jpg',
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
            'services12' => self::enrichServices12($data, $content),
            default => self::enrichServices4($data, $content),
        };
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichServices4(array $data, array $content): array
    {
        $html = '';
        foreach (self::items($content, 'services4') as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $description = trim((string) ($item['text'] ?? ''));
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $icon = self::iconHtml($item, $index);
            $bullets = self::bulletsHtml((string) ($item['label'] ?? ''));

            $html .= '<article class="section-services__card--services4">'
                . '<div class="section-services__card-head--services4">'
                . '<div class="section-services__icon-wrap--services4">' . $icon . '</div>'
                . '<h3 class="section-services__card-title--services4">' . $safeTitle . '</h3>'
                . '</div>'
                . ($description !== ''
                    ? '<p class="section-services__card-text--services4">' . $safeDescription . '</p>'
                    : '')
                . $bullets
                . '</article>';
        }
        $data['services_cards_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichServices12(array $data, array $content): array
    {
        $items = self::items($content, 'services12');
        $featured = '';
        $secondary = '';
        foreach ($items as $index => $item) {
            $card = self::imageCardHtml($item, $index, $index < 2 ? 'featured' : 'compact');
            if ($index < 2) {
                $featured .= $card;
            } else {
                $secondary .= $card;
            }
        }
        $data['services_featured_html'] = $featured;
        $data['services_secondary_html'] = $secondary;
        $data['buttons_html'] = self::ctaButtonHtml($content);

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function ctaButtonHtml(array $content): string
    {
        $buttons = $content['buttons'] ?? null;
        if (!is_array($buttons) || $buttons === []) {
            return '';
        }
        $button = $buttons[0] ?? null;
        if (!is_array($button)) {
            return '';
        }
        $label = trim((string) ($button['label'] ?? ''));
        $href = trim((string) ($button['href'] ?? ''));
        if ($label === '' || $href === '') {
            return '';
        }
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeHref = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<a class="section-button section-button--secondary section-services__cta--services12" href="'
            . $safeHref . '">' . $safeLabel
            . ' <i class="fa-solid fa-arrow-up-right" aria-hidden="true"></i></a>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function imageCardHtml(array $item, int $index, string $size): string
    {
        $title = trim((string) ($item['title'] ?? ''));
        if ($title === '') {
            return '';
        }
        $href = trim((string) ($item['href'] ?? ''));
        if ($href === '') {
            $href = '#';
        }
        $imageUrl = self::imageUrl((string) ($item['url'] ?? ''), $index);
        $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeHref = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeImageUrl = htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeAlt = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $sizeClass = $size === 'featured'
            ? 'section-services__card--featured--services12'
            : 'section-services__card--compact--services12';

        return '<a class="section-services__card--services12 ' . $sizeClass . '" href="' . $safeHref . '">'
            . '<img class="section-services__card-img--services12" src="' . $safeImageUrl . '" alt="' . $safeAlt
            . '" width="640" height="853" loading="lazy" decoding="async" />'
            . '<div class="section-services__card-overlay--services12">'
            . '<h3 class="section-services__card-title--services12">' . $safeTitle . '</h3>'
            . '<i class="fa-solid fa-arrow-up-right section-services__card-arrow--services12" aria-hidden="true"></i>'
            . '</div>'
            . '</a>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function iconHtml(array $item, int $index): string
    {
        $raw = trim((string) ($item['icon'] ?? ''));
        if ($raw === '') {
            $raw = self::DEFAULT_ICONS[$index % count(self::DEFAULT_ICONS)];
        }
        $class = FontAwesomeIcon::solidClass(FontAwesomeIcon::glyph($raw));

        return '<i class="' . htmlspecialchars($class, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" aria-hidden="true"></i>';
    }

    private static function bulletsHtml(string $raw): string
    {
        $lines = preg_split('/\r\n|\n|\r|,/', $raw) ?: [];
        $items = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $items[] = $line;
            }
        }
        if ($items === []) {
            return '';
        }
        $html = '<ul class="section-services__bullets--services4">';
        foreach ($items as $item) {
            $html .= '<li class="section-services__bullet--services4">'
                . '<span class="section-services__bullet-dot--services4" aria-hidden="true"></span>'
                . '<span>' . htmlspecialchars($item, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
                . '</li>';
        }

        return $html . '</ul>';
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    private static function items(array $content, string $variant): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $max = self::MAX_ITEMS[$variant] ?? 8;
        $items = [];
        foreach (array_slice($raw, 0, $max) as $item) {
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
}

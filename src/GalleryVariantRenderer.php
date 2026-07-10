<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Section\SectionItemsTrait;

/**
 * Rendu HTML spécifique aux variantes gallery (conversion des blocs React).
 */
final class GalleryVariantRenderer
{
    use SectionItemsTrait;
    private const SHARED = 'gallery';

    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'gallery4' => 8,
        'gallery6' => 8,
    ];

    /** @var list<string> */
    private const GALLERY4_IMAGES = [
        'saas-hero-1-16x9.png',
        'saas-hero-2-16x9.png',
        'saas-hero-3-16x9.png',
        'saas-hero-4-16x9.png',
        'saas-hero-5-16x9.png',
        'saas-hero-1-16x9-dark.png',
        'saas-hero-2-16x9-dark.png',
        'saas-hero-3-16x9-dark.png',
    ];

    /** @var list<string> */
    private const DEFAULT_IMAGES = [
        'images/1-1x1.jpg',
        'lummi/bw12.jpeg',
        'lummi/bw15.jpeg',
        'lummi/bw20.jpeg',
        'lummi/bw21.jpeg',
        'placeholder-1.svg',
        'placeholder-2.svg',
        'placeholder-3.svg',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $sectionId = (string) ($data['section_id'] ?? 'gallery');
        $readMore = trim((string) ($content['read_more_label'] ?? ''));
        if ($readMore === '') {
            $readMore = 'En savoir plus';
        }
        $safeReadMore = htmlspecialchars($readMore, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $items = self::items($content, $variant);

        $data['gallery_nav_html'] = self::navHtml($sectionId, $variant);
        $data['gallery_slides_html'] = match ($variant) {
            'gallery6' => self::slidesGallery6Html($items, $safeReadMore),
            default => self::slidesGallery4Html($items, $safeReadMore),
        };
        $data['gallery_dots_html'] = $variant === 'gallery4'
            ? self::dotsHtml($items, $sectionId)
            : '';

        return $data;
    }

    private static function navHtml(string $sectionId, string $variant = 'gallery4'): string
    {
        $safeId = htmlspecialchars($sectionId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $isGallery6 = $variant === 'gallery6';
        $navClass = $isGallery6 ? 'section-gallery__nav--gallery6' : 'section-gallery__nav';
        $btnClass = $isGallery6 ? 'section-gallery__nav-btn--gallery6' : 'section-gallery__nav-btn';

        return '<div class="' . $navClass . '" data-gallery-nav data-gallery-id="' . $safeId . '">'
            . '<button type="button" class="' . $btnClass . '" data-gallery-prev aria-label="Diapositive précédente">'
            . '<i class="fa-solid fa-arrow-left" aria-hidden="true"></i></button>'
            . '<button type="button" class="' . $btnClass . '" data-gallery-next aria-label="Diapositive suivante">'
            . '<i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>'
            . '</div>';
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private static function slidesGallery4Html(array $items, string $readMore): string
    {
        $html = '';
        foreach ($items as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $description = trim((string) ($item['text'] ?? ''));
            $href = self::href((string) ($item['href'] ?? '#'));
            $imageUrl = self::imageUrl((string) ($item['url'] ?? ''), $index, 'gallery4');
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeImageUrl = htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeAlt = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<article class="section-gallery__slide section-gallery__slide--overlay" role="group" aria-roledescription="slide">'
                . '<a class="section-gallery__card section-gallery__card--overlay" href="' . $href . '">'
                . '<div class="section-gallery__media">'
                . '<img class="section-gallery__img" src="' . $safeImageUrl . '" alt="' . $safeAlt
                . '" width="360" height="270" loading="lazy" decoding="async" />'
                . '<div class="section-gallery__overlay" aria-hidden="true"></div>'
                . '<div class="section-gallery__overlay-content">'
                . '<div class="section-gallery__card-title">' . $safeTitle . '</div>'
                . ($description !== '' ? '<div class="section-gallery__card-text">' . $safeDescription . '</div>' : '')
                . '<div class="section-gallery__card-cta">' . $readMore
                . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></div>'
                . '</div></div></a></article>';
        }

        return $html;
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private static function slidesGallery6Html(array $items, string $readMore): string
    {
        $html = '';
        foreach ($items as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $summary = trim((string) ($item['text'] ?? ''));
            $href = self::href((string) ($item['href'] ?? '#'));
            $imageUrl = self::imageUrl((string) ($item['url'] ?? ''), $index);
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeSummary = htmlspecialchars($summary, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeImageUrl = htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeAlt = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<article class="section-gallery__slide section-gallery__slide--stacked" role="group" aria-roledescription="slide">'
                . '<a class="section-gallery__card section-gallery__card--stacked" href="' . $href . '">'
                . '<div class="section-gallery__media section-gallery__media--ratio">'
                . '<div class="section-gallery__media-inner">'
                . '<img class="section-gallery__img" src="' . $safeImageUrl . '" alt="' . $safeAlt
                . '" width="452" height="301" loading="lazy" decoding="async" />'
                . '</div>'
                . '</div>'
                . '<h3 class="section-gallery__card-title">' . $safeTitle . '</h3>'
                . ($summary !== '' ? '<p class="section-gallery__card-text">' . $safeSummary . '</p>' : '')
                . '<span class="section-gallery__card-cta">' . $readMore
                . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>'
                . '</a></article>';
        }

        return $html;
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private static function dotsHtml(array $items, string $sectionId): string
    {
        $safeId = htmlspecialchars($sectionId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = '';
        $count = 0;
        foreach ($items as $item) {
            if (trim((string) ($item['title'] ?? '')) === '') {
                continue;
            }
            $html .= '<button type="button" class="section-gallery__dot' . ($count === 0 ? ' is-active' : '') . '"'
                . ' data-gallery-dot="' . $count . '" data-gallery-id="' . $safeId . '"'
                . ' aria-label="Aller à la diapositive ' . ($count + 1) . '"></button>';
            $count++;
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
        return self::itemsFromContent($content, self::MAX_ITEMS[$variant] ?? 8);
    }

    private static function imageUrl(string $url, int $index, string $variant = 'gallery6'): string
    {
        $pool = $variant === 'gallery4' ? self::GALLERY4_IMAGES : self::DEFAULT_IMAGES;
        $sharedType = $variant === 'gallery4' ? 'hero' : 'features';
        $fallbackFile = $pool[$index % count($pool)];

        return SectionAssets::resolve(
            $url,
            SectionAssets::shared($sharedType, $fallbackFile),
        );
    }

    private static function href(string $href): string
    {
        return self::hrefFromItem($href);
    }
}

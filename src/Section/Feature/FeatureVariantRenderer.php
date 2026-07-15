<?php

declare(strict_types=1);

namespace Capsule\Section\Feature;

use Capsule\Section\Support\SectionButtonStyle;
use Capsule\SectionAssets;

/**
 * Rendu HTML spécifique aux variantes features (conversion des blocs React).
 */
final class FeatureVariantRenderer
{
    private const SHARED = 'features';

    /** @var list<string> */
    private const TAB_ICONS = [
        'fa-solid fa-lightbulb',
        'fa-solid fa-list-check',
        'fa-solid fa-gear',
    ];

    /** @var list<string> */
    private const ICONS = [
        'fa-solid fa-bolt',
        'fa-solid fa-palette',
        'fa-solid fa-shield-halved',
        'fa-solid fa-gear',
        'fa-solid fa-layer-group',
        'fa-solid fa-rocket',
        'fa-solid fa-cubes',
        'fa-solid fa-globe',
        'fa-solid fa-chart-line',
        'fa-solid fa-wand-magic-sparkles',
        'fa-solid fa-diagram-project',
        'fa-solid fa-lock',
    ];

    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'feature3' => 6,
        'feature13' => 4,
        'feature15' => 4,
        'feature16' => 3,
        'feature17' => 6,
        'feature42' => 4,
        'feature43' => 6,
        'feature51' => 6,
        'feature72' => 4,
        'feature73' => 3,
        'feature74' => 2,
        'feature166' => 4,
        'feature197' => 6,
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $sectionId = (string) ($data['section_id'] ?? 'features');
        $data['feature_badge_html'] = self::renderBadge($content, $variant);
        $data['feature_label_html'] = self::renderLabel($content, $variant);
        $data['feature_buttons_html'] = self::renderButtons($content, $variant);
        $data['feature_visual_html'] = self::renderMainVisual($content);
        $data['feature_items_html'] = '';
        $data['feature_tabs_html'] = '';
        $data['feature_accordion_html'] = '';
        $data['feature_bento_html'] = '';
        $data['feature_rows_html'] = '';
        $data['feature_overlay_html'] = '';

        return match ($variant) {
            'feature3' => self::enrichCards($data, $content, 3),
            'feature13' => self::enrichHorizontalCards($data, $content),
            'feature15' => self::enrichIconCards($data, $content, 'accent-tall'),
            'feature16' => self::enrichIconCards($data, $content, 'accent-compact'),
            'feature17' => self::enrichIconList($data, $content),
            'feature42' => self::enrichTextGrid($data, $content),
            'feature43' => self::enrichIconGrid($data, $content),
            'feature51' => self::enrichTabs($data, $content, $sectionId),
            'feature72' => self::enrichImageCards($data, $content, 4, 'wide'),
            'feature73' => self::enrichImageCards($data, $content, 3, 'compact'),
            'feature74' => self::enrichAlternatingRows($data, $content),
            'feature166' => self::enrichBento($data, $content),
            'feature197' => self::enrichAccordion($data, $content, $sectionId),
            'feature239' => self::enrichFeature239($data, $content),
            default => $data,
        };
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function renderBadge(array $content, string $variant = ''): string
    {
        $badge = trim((string) ($content['badge'] ?? ''));
        if ($badge === '') {
            return '';
        }
        $safe = htmlspecialchars($badge, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if ($variant === 'feature17') {
            return '<span class="section-features__badge section-features__badge--secondary">' . $safe . '</span>';
        }

        return '<span class="section-features__badge section-features__badge--outline">'
            . $safe
            . ' <i class="fa-solid fa-chevron-right" aria-hidden="true"></i></span>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function renderLabel(array $content, string $variant): string
    {
        $label = trim((string) ($content['label'] ?? ''));
        if ($label === '') {
            return '';
        }
        if ($variant === 'feature17') {
            return '<span class="section-features__label section-features__label--badge">'
                . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>';
        }

        return '<p class="section-features__label">'
            . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function renderMainVisual(array $content): string
    {
        $light = SectionAssets::resolve(
            (string) ($content['image_url'] ?? ''),
            SectionAssets::shared(self::SHARED, 'saas-detail-1-1x1.png'),
        );
        if ($light === '') {
            return '';
        }
        $dark = SectionAssets::resolve((string) ($content['image_url_dark'] ?? ''), '');
        $alt = htmlspecialchars(
            trim((string) ($content['image_alt'] ?? $content['title'] ?? 'Illustration')),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );

        return self::dualImage($light, $dark, $alt, 'section-features__img');
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function renderOverlay(array $content): string
    {
        $date = self::renderOverlayDate(trim((string) ($content['overlay_date'] ?? '2025 | March')));
        $title = trim((string) ($content['overlay_title'] ?? 'New Collection'));
        $text = trim((string) ($content['overlay_text'] ?? 'Discover our latest release of beautifully crafted components.'));
        $link = trim((string) ($content['overlay_link'] ?? '#'));
        $linkLabel = trim((string) ($content['overlay_link_label'] ?? 'Tout voir'));
        $linkHtml = '';
        if ($linkLabel !== '') {
            $safeHref = htmlspecialchars($link !== '' ? $link : '#', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeLabel = htmlspecialchars($linkLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $linkHtml = '<a class="section-features__overlay-link" href="' . $safeHref . '">'
                . '<i class="fa-solid fa-chevron-up" aria-hidden="true"></i>'
                . '<span>' . $safeLabel . '</span></a>';
        }

        return '<div class="section-features__overlay">'
            . '<p class="section-features__overlay-date">' . $date . '</p>'
            . '<div class="section-features__overlay-center">'
            . '<h2 class="section-features__overlay-title">' . nl2br(htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), false) . '</h2>'
            . '<span class="section-features__overlay-dot"></span>'
            . '<p class="section-features__overlay-text">' . htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>'
            . '</div>'
            . $linkHtml
            . '</div>';
    }

    private static function renderOverlayDate(string $raw): string
    {
        if ($raw === '') {
            return '';
        }

        $parts = array_map('trim', explode('|', $raw, 2));
        if (count($parts) === 2 && $parts[0] !== '' && $parts[1] !== '') {
            return htmlspecialchars($parts[0], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '<span class="section-features__overlay-date-sep" aria-hidden="true"></span>'
                . htmlspecialchars($parts[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        return htmlspecialchars($raw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichCards(array $data, array $content, int $columns): array
    {
        $items = self::items($content, 'feature3');
        $html = '';
        foreach ($items as $index => $item) {
            $icon = self::iconAt($index);
            $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $text = htmlspecialchars(trim((string) ($item['text'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $img = self::itemImage($item, 'saas-card-detail-' . (($index % 6) + 1) . '-4x3.svg', $title);
            $html .= '<article class="section-features__card section-features__card--grid">'
                . '<div class="section-features__card-header"><i class="' . $icon . '" aria-hidden="true"></i></div>'
                . '<div class="section-features__card-content"><h3>' . $title . '</h3><p>' . $text . '</p></div>'
                . '<div class="section-features__card-footer">' . $img . '</div>'
                . '</article>';
        }
        $data['feature_items_html'] = $html;
        $data['feature_grid_columns'] = (string) $columns;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichHorizontalCards(array $data, array $content): array
    {
        $items = self::items($content, 'feature13');
        $html = '';
        foreach ($items as $index => $item) {
            $label = htmlspecialchars(trim((string) ($item['label'] ?? sprintf('%02d', $index + 1))), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $text = htmlspecialchars(trim((string) ($item['text'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $href = htmlspecialchars(trim((string) ($item['href'] ?? '#')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $img = self::itemImage($item, 'saas-card-detail-' . (($index % 6) + 1) . '-4x3.svg', $title);
            $html .= '<article class="section-features__card section-features__card--horizontal">'
                . '<div class="section-features__card-top">'
                . '<div class="section-features__card-copy">'
                . '<span class="section-features__card-label">' . $label . '</span>'
                . '<a class="section-features__card-title-link" href="' . $href . '"><h3>' . $title . '</h3></a>'
                . '</div>'
                . '<div class="section-features__card-thumb"><a href="' . $href . '">' . $img . '</a></div>'
                . '</div>'
                . '<p class="section-features__card-desc">' . $text . '</p>'
                . '</article>';
        }
        $data['feature_items_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichIconCards(array $data, array $content, string $modifier): array
    {
        $variant = $modifier === 'accent-tall' ? 'feature15' : 'feature16';
        $items = self::items($content, $variant);
        $html = '';
        foreach ($items as $index => $item) {
            $icon = self::iconAt($index);
            $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $text = htmlspecialchars(trim((string) ($item['text'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<article class="section-features__card section-features__card--icon section-features__card--' . $modifier . '">'
                . '<span class="section-features__icon-circle"><i class="' . $icon . '" aria-hidden="true"></i></span>'
                . '<div><h3>' . $title . '</h3><p>' . $text . '</p></div>'
                . '</article>';
        }
        $data['feature_items_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichIconList(array $data, array $content): array
    {
        $items = self::items($content, 'feature17');
        $html = '';
        foreach ($items as $index => $item) {
            $icon = self::iconAt($index);
            $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $text = htmlspecialchars(trim((string) ($item['text'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<article class="section-features__item section-features__item--row">'
                . '<span class="section-features__icon-circle section-features__icon-circle--sm"><i class="' . $icon . '" aria-hidden="true"></i></span>'
                . '<div><h3>' . $title . '</h3><p>' . $text . '</p></div>'
                . '</article>';
        }
        $data['feature_items_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichTextGrid(array $data, array $content): array
    {
        $items = self::items($content, 'feature42');
        $html = '';
        foreach ($items as $item) {
            $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $text = htmlspecialchars(trim((string) ($item['text'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<div class="section-features__text-cell"><h3>' . $title . '</h3><p>' . $text . '</p></div>';
        }
        $data['feature_items_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichIconGrid(array $data, array $content): array
    {
        $items = self::items($content, 'feature43');
        $html = '';
        foreach ($items as $index => $item) {
            $icon = self::iconAt($index);
            $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $text = htmlspecialchars(trim((string) ($item['text'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<article class="section-features__item section-features__item--icon">'
                . '<div class="section-features__icon-circle section-features__icon-circle--lg"><i class="' . $icon . '" aria-hidden="true"></i></div>'
                . '<h3>' . $title . '</h3><p>' . $text . '</p>'
                . '</article>';
        }
        $data['feature_items_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichImageCards(array $data, array $content, int $max, string $size): array
    {
        $variant = $max === 4 ? 'feature72' : 'feature73';
        $items = self::items($content, $variant);
        $html = '';
        foreach ($items as $index => $item) {
            $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $text = htmlspecialchars(trim((string) ($item['text'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $href = htmlspecialchars(trim((string) ($item['href'] ?? '#')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $img = self::itemImage($item, 'saas-card-detail-' . (($index % 6) + 1) . '-4x3.svg', $title);
            $sizeClass = $size === 'compact' ? ' section-features__card--compact' : '';
            $html .= '<article class="section-features__card section-features__card--image' . $sizeClass . '">'
                . '<a class="section-features__card-media-link" href="' . $href . '">' . $img . '</a>'
                . '<div class="section-features__card-body"><h3>' . $title . '</h3><p>' . $text . '</p></div>'
                . '</article>';
        }
        $data['feature_items_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichAlternatingRows(array $data, array $content): array
    {
        $items = self::items($content, 'feature74');
        $html = '';
        foreach ($items as $index => $item) {
            $reverse = $index % 2 === 1;
            $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $text = htmlspecialchars(trim((string) ($item['text'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $img = self::itemImage($item, 'placeholder-' . (($index % 4) + 1) . '.svg', $title);
            $rowClass = 'section-features__row' . ($reverse ? ' section-features__row--reverse' : '');
            $html .= '<article class="' . $rowClass . '">'
                . '<div class="section-features__row-media">' . $img . '</div>'
                . '<div class="section-features__row-copy"><h3>' . $title . '</h3><p>' . $text . '</p></div>'
                . '</article>';
        }
        $data['feature_rows_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichBento(array $data, array $content): array
    {
        $items = self::items($content, 'feature166');
        $item0 = $items[0] ?? [];
        $item1 = $items[1] ?? [];
        $item2 = $items[2] ?? [];
        $item3 = $items[3] ?? [];
        $data['feature_bento_html'] = '<div class="section-features__bento-wrap">'
            . '<div class="section-features__bento">'
            . '<div class="section-features__bento-row section-features__bento-row--top">'
            . self::bentoCell($item0, 'wide', 1)
            . self::bentoCell($item1, 'narrow', 2)
            . '</div>'
            . '<div class="section-features__bento-row section-features__bento-row--bottom">'
            . self::bentoCell($item2, 'narrow', 3)
            . self::bentoCell($item3, 'wide', 4)
            . '</div>'
            . '</div></div>';

        return $data;
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function bentoCell(array $item, string $slot, int $fallbackIndex): string
    {
        $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $text = htmlspecialchars(trim((string) ($item['text'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $img = self::bentoImage($item, 'placeholder-' . $fallbackIndex . '.svg', $title);

        return '<div class="section-features__bento-cell section-features__bento-cell--' . $slot . '">'
            . '<h2 class="section-features__bento-title">' . $title . '</h2>'
            . '<p class="section-features__bento-text">' . $text . '</p>'
            . $img
            . '</div>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function bentoImage(array $item, string $fallbackFile, string $alt): string
    {
        $url = SectionAssets::resolve(
            (string) ($item['url'] ?? ''),
            SectionAssets::shared(self::SHARED, $fallbackFile),
        );
        if ($url === '') {
            return '';
        }
        $safeAlt = htmlspecialchars($alt !== '' ? $alt : 'Illustration', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<img class="section-features__bento-img" src="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" alt="' . $safeAlt . '" loading="lazy" decoding="async" />';
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichTabs(array $data, array $content, string $sectionId): array
    {
        $items = self::items($content, 'feature51');
        $tabs = '';
        $panels = '';
        $activeIndex = 0;
        foreach ($items as $index => $item) {
            if (($item['is_default'] ?? false) === true) {
                $activeIndex = $index;
            }
        }
        foreach ($items as $index => $item) {
            $icon = self::TAB_ICONS[$index % count(self::TAB_ICONS)];
            $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $text = htmlspecialchars(trim((string) ($item['text'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $img = self::itemImage($item, 'placeholder-' . (($index % 3) + 1) . '.svg', $title);
            $active = $index === $activeIndex;
            $tabs .= '<button type="button" class="section-features__tab' . ($active ? ' is-active' : '') . '" role="tab"'
                . ' aria-selected="' . ($active ? 'true' : 'false') . '"'
                . ' aria-controls="' . htmlspecialchars($sectionId, ENT_QUOTES) . '-panel-' . $index . '"'
                . ' id="' . htmlspecialchars($sectionId, ENT_QUOTES) . '-tab-' . $index . '"'
                . ' data-feature-tab="' . $index . '">'
                . '<span class="section-features__tab-head">'
                . '<span class="section-features__icon-circle section-features__icon-circle--sm"><i class="' . $icon . '" aria-hidden="true"></i></span>'
                . '<span class="section-features__tab-title">' . $title . '</span></span>'
                . '<span class="section-features__tab-desc">' . $text . '</span>'
                . '</button>';
            $panels .= '<div class="section-features__tab-panel' . ($active ? ' is-active' : '') . '" role="tabpanel"'
                . ' id="' . htmlspecialchars($sectionId, ENT_QUOTES) . '-panel-' . $index . '"'
                . ' aria-labelledby="' . htmlspecialchars($sectionId, ENT_QUOTES) . '-tab-' . $index . '"'
                . ' data-feature-tab-panel="' . $index . '"' . ($active ? '' : ' hidden') . '>'
                . $img . '</div>';
        }
        $data['feature_tabs_html'] = '<div class="section-features__tabs" data-feature-tabs id="'
            . htmlspecialchars($sectionId, ENT_QUOTES) . '-tabs">'
            . '<div class="section-features__tablist" role="tablist">' . $tabs . '</div>'
            . '<div class="section-features__tab-panels">' . $panels . '</div>'
            . '</div>';

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichAccordion(array $data, array $content, string $sectionId): array
    {
        $items = self::items($content, 'feature197');
        $lummi = ['lummi/bw12.jpeg', 'lummi/bw15.jpeg', 'lummi/bw20.jpeg', 'lummi/bw21.jpeg'];
        $accordion = '';
        $visual = '';
        foreach ($items as $index => $item) {
            $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $text = htmlspecialchars(trim((string) ($item['text'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $fallback = SectionAssets::shared(self::SHARED, $lummi[$index % count($lummi)]);
            $imgUrl = SectionAssets::resolve((string) ($item['url'] ?? ''), $fallback);
            $active = $index === 0;
            $accordion .= '<div class="section-features__accordion-item' . ($active ? ' is-active' : '') . '" data-feature-accordion-item="' . $index . '">'
                . '<button type="button" class="section-features__accordion-trigger" aria-expanded="' . ($active ? 'true' : 'false') . '"'
                . ' aria-controls="' . htmlspecialchars($sectionId, ENT_QUOTES) . '-acc-' . $index . '"'
                . ' data-feature-accordion-trigger="' . $index . '">'
                . '<h4>' . $title . '</h4></button>'
                . '<div class="section-features__accordion-panel" id="' . htmlspecialchars($sectionId, ENT_QUOTES) . '-acc-' . $index . '"'
                . ($active ? '' : ' hidden') . ' data-feature-accordion-panel="' . $index . '">'
                . '<p>' . $text . '</p>'
                . '<img class="section-features__accordion-mobile-img" src="' . htmlspecialchars($imgUrl, ENT_QUOTES) . '" alt="' . $title . '" loading="lazy" decoding="async" />'
                . '</div></div>';
            $visual .= '<img class="section-features__accordion-visual' . ($active ? ' is-active' : '') . '" src="'
                . htmlspecialchars($imgUrl, ENT_QUOTES) . '" alt="' . $title . '" data-feature-accordion-image="' . $index . '" loading="lazy" decoding="async" />';
        }
        $data['feature_accordion_html'] = '<div class="section-features__accordion-wrap" data-feature-accordion id="'
            . htmlspecialchars($sectionId, ENT_QUOTES) . '-accordion">'
            . '<div class="section-features__accordion">' . $accordion . '</div>'
            . '<div class="section-features__accordion-visuals">' . $visual . '</div>'
            . '</div>';

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichFeature239(array $data, array $content): array
    {
        $title = trim((string) ($content['title'] ?? ''));
        $data['feature_title_html'] = $title !== ''
            ? nl2br(htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), false)
            : '';

        $light = SectionAssets::resolve(
            (string) ($content['image_url'] ?? ''),
            SectionAssets::shared(self::SHARED, 'images/1-1x1.jpg'),
        );
        if ($light !== '') {
            $alt = htmlspecialchars(
                trim((string) ($content['image_alt'] ?? $content['title'] ?? 'Aperçu')),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8',
            );
            $data['feature_visual_html'] = '<img class="section-features__img" src="'
                . htmlspecialchars($light, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" alt="' . $alt
                . '" width="800" height="800" loading="lazy" decoding="async" />';
        }

        $data['feature_overlay_html'] = self::renderOverlay($content);

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function renderButtons(array $content, string $variant): string
    {
        $buttons = is_array($content['buttons'] ?? null) ? $content['buttons'] : [];
        $primary = null;
        $secondary = null;
        $outline = null;
        foreach ($buttons as $button) {
            if (!is_array($button)) {
                continue;
            }
            $style = SectionButtonStyle::normalize((string) ($button['style'] ?? 'primary'));
            if ($style === 'primary' && $primary === null) {
                $primary = $button;
            } elseif ($style === 'secondary' && $secondary === null) {
                $secondary = $button;
            } elseif ($style === 'outline' && $outline === null) {
                $outline = $button;
            }
        }

        return match ($variant) {
            'feature1', 'feature2' => self::outlineButton($outline ?? $secondary),
            'feature17', 'feature43' => self::primaryButton($primary, 'lg'),
            'feature72', 'feature73', 'feature74' => self::linkButton($primary),
            'feature239' => self::pillOutlineButton($outline ?? $secondary),
            default => self::primaryButton($primary, 'md'),
        };
    }

    /**
     * @param array<string, mixed>|null $button
     */
    private static function primaryButton(?array $button, string $size): string
    {
        if (!is_array($button)) {
            return '';
        }
        $label = trim((string) ($button['label'] ?? ''));
        $href = trim((string) ($button['href'] ?? ''));
        if ($label === '' || $href === '') {
            return '';
        }

        return '<a class="section-features__btn section-features__btn--primary section-features__btn--primary-' . $size . '" href="'
            . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
            . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a>';
    }

    /**
     * @param array<string, mixed>|null $button
     */
    private static function outlineButton(?array $button): string
    {
        if (!is_array($button)) {
            return '';
        }
        $label = trim((string) ($button['label'] ?? ''));
        $href = trim((string) ($button['href'] ?? ''));
        if ($label === '' || $href === '') {
            return '';
        }

        return '<a class="section-features__btn section-features__btn--outline" href="'
            . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" rel="noopener noreferrer" target="_blank">'
            . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a>';
    }

    /**
     * @param array<string, mixed>|null $button
     */
    private static function pillOutlineButton(?array $button): string
    {
        if (!is_array($button)) {
            return '';
        }
        $label = trim((string) ($button['label'] ?? ''));
        $href = trim((string) ($button['href'] ?? ''));
        if ($label === '' || $href === '') {
            return '';
        }

        return '<a class="section-features__btn section-features__btn--pill" href="'
            . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
            . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . ' <i class="fa-solid fa-arrow-up-right" aria-hidden="true"></i></a>';
    }

    /**
     * @param array<string, mixed>|null $button
     */
    private static function linkButton(?array $button): string
    {
        if (!is_array($button)) {
            return '';
        }
        $label = trim((string) ($button['label'] ?? ''));
        $href = trim((string) ($button['href'] ?? ''));
        if ($label === '' || $href === '') {
            return '';
        }

        return '<a class="section-features__btn section-features__btn--link" href="'
            . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
            . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
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
    private static function itemImage(array $item, string $fallbackFile, string $alt): string
    {
        $url = SectionAssets::resolve(
            (string) ($item['url'] ?? ''),
            SectionAssets::shared(self::SHARED, $fallbackFile),
        );
        if ($url === '') {
            return '';
        }
        $safeAlt = htmlspecialchars($alt !== '' ? $alt : 'Illustration', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<img class="section-features__item-img" src="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" alt="' . $safeAlt . '" loading="lazy" decoding="async" />';
    }

    private static function iconAt(int $index): string
    {
        return self::ICONS[$index % count(self::ICONS)];
    }

    private static function dualImage(string $light, string $dark, string $alt, string $class): string
    {
        return '<img class="' . $class . '" src="' . htmlspecialchars($light, ENT_QUOTES) . '" alt="' . $alt . '" width="800" height="800" loading="lazy" decoding="async" />';
    }
}

<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Rendu HTML des en-têtes shadcnblocks (navbar1, navbar5).
 */
final class HeaderVariantRenderer
{
    /** @var array<string, string> */
    private const ICONS = [
        'book' => 'fa-solid fa-book',
        'tree' => 'fa-solid fa-tree',
        'trees' => 'fa-solid fa-tree',
        'sun' => 'fa-solid fa-sun',
        'sunset' => 'fa-solid fa-sun',
        'zap' => 'fa-solid fa-bolt',
        'bolt' => 'fa-solid fa-bolt',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $variant
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $variant, string $siteName, string $logoUrl, string $homeUrl = '/'): array
    {
        $template = HeaderStyle::normalizeTemplate((string) ($variant['template'] ?? HeaderStyle::TEMPLATE_DEFAULT));
        $variantId = preg_replace('/[^a-z0-9_-]/i', '', (string) ($variant['id'] ?? 'header')) ?: 'header';
        $brandHtml = self::brandHtml($siteName, $logoUrl, $homeUrl);
        $actionsHtml = self::actionsHtml($variant);

        $data['header_blocks_html'] = match ($template) {
            'navbar5' => self::navbar5Html($variant, $variantId, $brandHtml, $actionsHtml),
            default => self::navbar1Html($variant, $variantId, $brandHtml, $actionsHtml),
        };

        return $data;
    }

    private static function brandHtml(string $siteName, string $logoUrl, string $homeUrl): string
    {
        $safeName = htmlspecialchars($siteName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeHome = htmlspecialchars($homeUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $inner = $logoUrl !== ''
            ? '<img class="site-header__blocks-logo" src="'
                . htmlspecialchars($logoUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '" alt="' . $safeName . '" height="32" />'
            : '';
        $inner .= '<span class="site-header__blocks-name">' . $safeName . '</span>';

        return '<a class="site-header__blocks-brand" href="' . $safeHome . '">' . $inner . '</a>';
    }

    /**
     * @param array<string, mixed> $variant
     */
    private static function actionsHtml(array $variant): string
    {
        $login = ChromeButtonRenderer::render(is_array($variant['login'] ?? null) ? $variant['login'] : [], 'outline');
        $cta = ChromeButtonRenderer::render(is_array($variant['cta'] ?? null) ? $variant['cta'] : [], 'primary');
        if ($login === '' && $cta === '') {
            return '';
        }

        return '<div class="site-header__blocks-actions">' . $login . $cta . '</div>';
    }

    /**
     * @param array<string, mixed> $variant
     */
    private static function navbar1Html(array $variant, string $variantId, string $brandHtml, string $actionsHtml): string
    {
        $menuItems = is_array($variant['menu_items'] ?? null) ? $variant['menu_items'] : [];
        $panelId = 'site-header-blocks-panel-' . $variantId;

        return '<div class="site-header__blocks-container">'
            . '<nav class="site-header__blocks-bar site-header__blocks-bar--desktop" aria-label="Navigation principale">'
            . '<div class="site-header__blocks-start">'
            . $brandHtml
            . '<div class="site-header__blocks-menu">' . self::navbar1DesktopMenuHtml($menuItems) . '</div>'
            . '</div>'
            . $actionsHtml
            . '</nav>'
            . '<div class="site-header__blocks-bar site-header__blocks-bar--mobile">'
            . $brandHtml
            . '<button type="button" class="site-header__blocks-toggle" aria-controls="' . $panelId
            . '" aria-expanded="false" aria-label="Ouvrir le menu">'
            . '<i class="fa-solid fa-bars" aria-hidden="true"></i></button>'
            . '</div>'
            . '<div class="site-header__blocks-panel" id="' . $panelId . '" hidden>'
            . '<div class="site-header__blocks-panel-inner">'
            . self::navbar1MobileMenuHtml($menuItems)
            . self::mobileActionsHtml($variant)
            . '</div></div>'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $variant
     */
    private static function navbar5Html(array $variant, string $variantId, string $brandHtml, string $actionsHtml): string
    {
        $features = is_array($variant['features'] ?? null) ? $variant['features'] : [];
        $navLinks = is_array($variant['nav_links'] ?? null) ? $variant['nav_links'] : [];
        $mobileLinks = is_array($variant['mobile_links'] ?? null) ? $variant['mobile_links'] : [];
        $featuresLabel = trim((string) ($variant['features_label'] ?? 'Fonctionnalités'));
        if ($featuresLabel === '') {
            $featuresLabel = 'Fonctionnalités';
        }
        $panelId = 'site-header-blocks-panel-' . $variantId;
        $safeFeaturesLabel = htmlspecialchars($featuresLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $megaTrigger = '<summary class="site-header__blocks-mega-trigger">'
            . $safeFeaturesLabel
            . '<i class="fa-solid fa-chevron-down site-header__blocks-chevron" aria-hidden="true"></i>'
            . '</summary>';
        $mobileLinksHtml = self::simpleLinksHtml(
            $mobileLinks !== [] ? $mobileLinks : $navLinks,
            'site-header__blocks-mobile-link',
        );

        return '<div class="site-header__blocks-container">'
            . '<nav class="site-header__blocks-bar site-header__blocks-bar--desktop site-header__blocks-bar--navbar5" aria-label="Navigation principale">'
            . '<div class="site-header__blocks-brand-wrap">' . $brandHtml . '</div>'
            . '<div class="site-header__blocks-nav site-header__blocks-nav--navbar5">'
            . '<details class="site-header__blocks-mega-details">'
            . $megaTrigger
            . '<div class="site-header__blocks-mega-panel">' . self::navbar5FeaturesGridHtml($features) . '</div>'
            . '</details>'
            . self::simpleLinksHtml($navLinks, 'site-header__blocks-link')
            . '</div>'
            . '<div class="site-header__blocks-actions site-header__blocks-actions--navbar5">' . self::actionsButtonsHtml($variant) . '</div>'
            . '</nav>'
            . '<div class="site-header__blocks-bar site-header__blocks-bar--mobile site-header__blocks-bar--navbar5-mobile">'
            . $brandHtml
            . '<button type="button" class="site-header__blocks-toggle" aria-controls="' . $panelId
            . '" aria-expanded="false" aria-label="Ouvrir le menu">'
            . '<i class="fa-solid fa-bars" aria-hidden="true"></i></button>'
            . '</div>'
            . '<div class="site-header__blocks-sheet site-header__blocks-panel site-header__blocks-panel--top" id="' . $panelId . '" hidden>'
            . '<div class="site-header__blocks-sheet-head">' . $brandHtml . '</div>'
            . '<div class="site-header__blocks-sheet-body">'
            . '<details class="site-header__blocks-accordion site-header__blocks-accordion--navbar5">'
            . '<summary class="site-header__blocks-accordion-trigger">' . $safeFeaturesLabel
            . '<i class="fa-solid fa-chevron-down site-header__blocks-chevron" aria-hidden="true"></i></summary>'
            . '<div class="site-header__blocks-accordion-panel">' . self::navbar5FeaturesGridHtml($features) . '</div>'
            . '</details>'
            . '<div class="site-header__blocks-mobile-links">' . $mobileLinksHtml . '</div>'
            . self::mobileActionsHtml($variant)
            . '</div></div>'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $variant
     */
    private static function actionsButtonsHtml(array $variant): string
    {
        $login = ChromeButtonRenderer::render(is_array($variant['login'] ?? null) ? $variant['login'] : [], 'outline');
        $cta = ChromeButtonRenderer::render(is_array($variant['cta'] ?? null) ? $variant['cta'] : [], 'primary');

        return $login . $cta;
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private static function navbar1DesktopMenuHtml(array $items): string
    {
        $html = '';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $label = trim((string) ($item['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $children = is_array($item['children'] ?? null) ? $item['children'] : [];
            $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if ($children !== []) {
                $html .= '<details class="site-header__blocks-dropdown">'
                    . '<summary class="site-header__blocks-dropdown-trigger">' . $safeLabel . '</summary>'
                    . '<div class="site-header__blocks-dropdown-panel">' . self::navbar1SubmenuHtml($children) . '</div>'
                    . '</details>';
                continue;
            }
            $href = self::href((string) ($item['href'] ?? '#'));
            $html .= '<a class="site-header__blocks-link" href="' . $href . '">' . $safeLabel . '</a>';
        }

        return $html;
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private static function navbar1MobileMenuHtml(array $items): string
    {
        $html = '<div class="site-header__blocks-mobile-menu">';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $label = trim((string) ($item['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $children = is_array($item['children'] ?? null) ? $item['children'] : [];
            $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if ($children !== []) {
                $html .= '<details class="site-header__blocks-accordion">'
                    . '<summary class="site-header__blocks-accordion-trigger">' . $safeLabel . '</summary>'
                    . '<div class="site-header__blocks-accordion-panel">' . self::navbar1SubmenuHtml($children) . '</div>'
                    . '</details>';
                continue;
            }
            $href = self::href((string) ($item['href'] ?? '#'));
            $html .= '<a class="site-header__blocks-mobile-link" href="' . $href . '">' . $safeLabel . '</a>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private static function navbar1SubmenuHtml(array $items): string
    {
        $html = '';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $label = trim((string) ($item['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $description = trim((string) ($item['description'] ?? ''));
            $href = self::href((string) ($item['href'] ?? '#'));
            $icon = self::iconClass((string) ($item['icon'] ?? ''));
            $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $iconHtml = $icon !== ''
                ? '<span class="site-header__blocks-submenu-icon" aria-hidden="true"><i class="' . $icon . '"></i></span>'
                : '';
            $html .= '<a class="site-header__blocks-submenu-link" href="' . $href . '">'
                . $iconHtml
                . '<span class="site-header__blocks-submenu-text">'
                . '<span class="site-header__blocks-submenu-title">' . $safeLabel . '</span>'
                . ($description !== '' ? '<span class="site-header__blocks-submenu-desc">' . $safeDescription . '</span>' : '')
                . '</span></a>';
        }

        return $html;
    }

    /**
     * @param list<array<string, mixed>> $features
     */
    private static function navbar5FeaturesGridHtml(array $features): string
    {
        $html = '<div class="site-header__blocks-features-grid">';
        foreach (array_slice($features, 0, 6) as $feature) {
            if (!is_array($feature)) {
                continue;
            }
            $title = trim((string) ($feature['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $description = trim((string) ($feature['description'] ?? ''));
            $href = self::href((string) ($feature['href'] ?? '#'));
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<a class="site-header__blocks-feature-link" href="' . $href . '">'
                . '<span class="site-header__blocks-feature-title">' . $safeTitle . '</span>'
                . ($description !== '' ? '<span class="site-header__blocks-feature-desc">' . $safeDescription . '</span>' : '')
                . '</a>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param list<array<string, mixed>> $links
     */
    private static function simpleLinksHtml(array $links, string $class): string
    {
        $html = '';
        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }
            $label = trim((string) ($link['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $href = self::href((string) ($link['href'] ?? '#'));
            $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<a class="' . $class . '" href="' . $href . '">' . $safeLabel . '</a>';
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $variant
     */
    private static function mobileActionsHtml(array $variant): string
    {
        $login = ChromeButtonRenderer::render(is_array($variant['login'] ?? null) ? $variant['login'] : [], 'outline');
        $cta = ChromeButtonRenderer::render(is_array($variant['cta'] ?? null) ? $variant['cta'] : [], 'primary');
        if ($login === '' && $cta === '') {
            return '';
        }

        return '<div class="site-header__blocks-mobile-actions">' . $login . $cta . '</div>';
    }

    private static function href(string $href): string
    {
        $trimmed = trim($href);

        return htmlspecialchars($trimmed !== '' ? $trimmed : '#', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function iconClass(string $icon): string
    {
        $key = strtolower(trim($icon));

        return self::ICONS[$key] ?? ($key !== '' ? 'fa-solid fa-' . preg_replace('/[^a-z0-9-]/', '', $key) : '');
    }
}

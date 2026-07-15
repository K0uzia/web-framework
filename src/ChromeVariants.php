<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Variantes d'en-tête et de pied de page (partials du chrome public).
 *
 * Chaque variante regroupe la configuration complète d'un partial (éléments,
 * emplacements). Une variante est marquée active pour tout le site. Si aucune
 * variante n'est stockée, une variante par défaut est reconstruite depuis les
 * anciennes clés plates du site (compatibilité ascendante).
 */
final class ChromeVariants
{
    public const HEADER_ZONES = ['left', 'center', 'right'];
    public const FOOTER_ZONES = ['left', 'right'];

    /**
     * @param array<string, mixed> $site
     *
     * @return list<array<string, mixed>>
     */
    public static function headerVariants(array $site): array
    {
        $raw = is_array($site['header_variants'] ?? null) ? $site['header_variants'] : [];
        $variants = [];
        foreach ($raw as $variant) {
            if (is_array($variant)) {
                $variants[] = self::normalizeHeader($variant);
            }
        }

        return $variants !== [] ? $variants : [self::legacyHeaderVariant($site)];
    }

    /**
     * @param array<string, mixed> $site
     *
     * @return list<array<string, mixed>>
     */
    public static function footerVariants(array $site): array
    {
        $raw = is_array($site['footer_variants'] ?? null) ? $site['footer_variants'] : [];
        $variants = [];
        foreach ($raw as $variant) {
            if (is_array($variant)) {
                $variants[] = self::normalizeFooter($variant);
            }
        }

        return $variants !== [] ? $variants : [self::legacyFooterVariant($site)];
    }

    /**
     * @param array<string, mixed> $site
     */
    public static function activeHeaderId(array $site): string
    {
        $id = (string) ($site['active_header_variant'] ?? '');
        $variants = self::headerVariants($site);
        if (self::find($variants, $id) !== null) {
            return $id;
        }

        return (string) ($variants[0]['id'] ?? 'default');
    }

    /**
     * @param array<string, mixed> $site
     */
    public static function activeFooterId(array $site): string
    {
        $id = (string) ($site['active_footer_variant'] ?? '');
        $variants = self::footerVariants($site);
        if (self::find($variants, $id) !== null) {
            return $id;
        }

        return (string) ($variants[0]['id'] ?? 'default');
    }

    /**
     * @param array<string, mixed> $site
     *
     * @return array<string, mixed>
     */
    public static function resolveHeader(array $site, string $preferredId = ''): array
    {
        $variants = self::headerVariants($site);
        if ($preferredId !== '') {
            $found = self::find($variants, $preferredId);
            if ($found !== null) {
                return $found;
            }
        }

        return self::find($variants, self::activeHeaderId($site)) ?? $variants[0];
    }

    /**
     * @param array<string, mixed> $site
     *
     * @return array<string, mixed>
     */
    public static function resolveFooter(array $site, string $preferredId = ''): array
    {
        $variants = self::footerVariants($site);
        if ($preferredId !== '') {
            $found = self::find($variants, $preferredId);
            if ($found !== null) {
                return $found;
            }
        }

        return self::find($variants, self::activeFooterId($site)) ?? $variants[0];
    }

    /**
     * @param list<array<string, mixed>> $variants
     *
     * @return array<string, mixed>|null
     */
    public static function find(array $variants, string $id): ?array
    {
        foreach ($variants as $variant) {
            if (($variant['id'] ?? '') === $id) {
                return $variant;
            }
        }

        return null;
    }

    public static function newId(): string
    {
        return 'variant-' . bin2hex(random_bytes(4));
    }

    /**
     * @param array<string, mixed> $variant
     *
     * @return array<string, mixed>
     */
    public static function normalizeHeader(array $variant): array
    {
        $brand = is_array($variant['brand'] ?? null) ? $variant['brand'] : [];
        $nav = is_array($variant['nav'] ?? null) ? $variant['nav'] : [];
        $cta = is_array($variant['cta'] ?? null) ? $variant['cta'] : [];
        $login = is_array($variant['login'] ?? null) ? $variant['login'] : [];
        $layout = is_array($variant['layout'] ?? null) ? $variant['layout'] : [];
        $appearance = is_array($variant['appearance'] ?? null) ? $variant['appearance'] : [];

        return [
            'id' => (string) ($variant['id'] ?? 'default'),
            'name' => trim((string) ($variant['name'] ?? '')) !== '' ? trim((string) ($variant['name'] ?? '')) : 'Sans titre',
            'template' => HeaderStyle::normalizeTemplate((string) ($variant['template'] ?? HeaderStyle::TEMPLATE_DEFAULT)),
            'menu_items' => self::normalizeMenuItems($variant['menu_items'] ?? []),
            'features' => self::normalizeFeatureItems($variant['features'] ?? []),
            'nav_links' => self::normalizeLinks($variant['nav_links'] ?? []),
            'mobile_links' => self::normalizeLinks($variant['mobile_links'] ?? []),
            'features_label' => trim((string) ($variant['features_label'] ?? '')),
            'brand' => [
                'show_logo' => ($brand['show_logo'] ?? true) !== false,
                'show_name' => ($brand['show_name'] ?? true) !== false,
                'show_tagline' => ($brand['show_tagline'] ?? false) === true,
            ],
            'nav' => ['visible' => ($nav['visible'] ?? true) !== false],
            'cta' => self::normalizeLink($cta, '', 'primary'),
            'login' => self::normalizeLink($login, '/login', 'outline'),
            'appearance' => [
                'show_border' => ($appearance['show_border'] ?? true) !== false,
                'bg' => ChromeAppearance::normalizeHeaderBg((string) ($appearance['bg'] ?? 'theme')),
            ],
            'layout' => [
                'brand' => self::zone($layout, 'brand', 'left', self::HEADER_ZONES),
                'nav' => self::zone($layout, 'nav', 'right', self::HEADER_ZONES),
                'cta' => self::zone($layout, 'cta', 'right', self::HEADER_ZONES),
                'login' => self::zone($layout, 'login', 'right', self::HEADER_ZONES),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $variant
     *
     * @return array<string, mixed>
     */
    public static function normalizeFooter(array $variant): array
    {
        $brand = is_array($variant['brand'] ?? null) ? $variant['brand'] : [];
        $nav = is_array($variant['nav'] ?? null) ? $variant['nav'] : [];
        $login = is_array($variant['login'] ?? null) ? $variant['login'] : [];
        $layout = is_array($variant['layout'] ?? null) ? $variant['layout'] : [];
        $appearance = is_array($variant['appearance'] ?? null) ? $variant['appearance'] : [];
        $template = FooterStyle::normalizeTemplate((string) ($variant['template'] ?? FooterStyle::TEMPLATE_DEFAULT));

        return [
            'id' => (string) ($variant['id'] ?? 'default'),
            'name' => trim((string) ($variant['name'] ?? '')) !== '' ? trim((string) $variant['name']) : 'Sans titre',
            'template' => $template,
            'description' => trim((string) ($variant['description'] ?? '')),
            'sections' => self::normalizeSections($variant['sections'] ?? []),
            'legal_links' => self::normalizeLinks($variant['legal_links'] ?? []),
            'social_links' => self::normalizeSocialLinks($variant['social_links'] ?? []),
            'brand' => [
                'visible' => ($brand['visible'] ?? true) !== false,
                'show_logo' => ($brand['show_logo'] ?? true) !== false,
                'show_name' => ($brand['show_name'] ?? true) !== false,
                'show_tagline' => ($brand['show_tagline'] ?? true) !== false,
            ],
            'nav' => ['visible' => ($nav['visible'] ?? true) !== false],
            'login' => self::normalizeLink($login, '/login', 'outline'),
            'appearance' => [
                'show_border' => ($appearance['show_border'] ?? true) !== false,
                'bg' => ChromeAppearance::normalizeFooterBg((string) ($appearance['bg'] ?? 'theme')),
            ],
            'layout' => [
                'brand' => self::zone($layout, 'brand', 'left', self::FOOTER_ZONES),
                'nav' => self::zone($layout, 'nav', 'right', self::FOOTER_ZONES),
                'login' => self::zone($layout, 'login', 'right', self::FOOTER_ZONES),
            ],
        ];
    }

    /**
     * Variante par défaut reconstruite depuis les anciennes clés plates du site.
     *
     * @param array<string, mixed> $site
     *
     * @return array<string, mixed>
     */
    public static function legacyHeaderVariant(array $site): array
    {
        $brand = is_array($site['header_brand'] ?? null) ? $site['header_brand'] : [];

        return self::normalizeHeader([
            'id' => 'default',
            'name' => 'Par défaut',
            'template' => HeaderStyle::TEMPLATE_DEFAULT,
            'brand' => [
                'show_logo' => ($brand['show_logo'] ?? true) !== false,
                'show_name' => ($brand['show_name'] ?? true) !== false,
                'show_tagline' => ($site['show_tagline_in_header'] ?? false) === true,
            ],
            'nav' => ['visible' => ($site['show_nav_in_header'] ?? true) !== false],
            'cta' => is_array($site['header_cta'] ?? null) ? $site['header_cta'] : [],
            'login' => is_array($site['header_login'] ?? null) ? $site['header_login'] : [],
            'layout' => is_array($site['header_layout'] ?? null) ? $site['header_layout'] : [],
        ]);
    }

    /**
     * @param array<string, mixed> $site
     *
     * @return array<string, mixed>
     */
    public static function legacyFooterVariant(array $site): array
    {
        return self::normalizeFooter([
            'id' => 'default',
            'name' => 'Par défaut',
            'template' => FooterStyle::TEMPLATE_DEFAULT,
            'brand' => [
                'visible' => ($site['show_brand_in_footer'] ?? true) !== false,
                'show_logo' => true,
                'show_name' => true,
                'show_tagline' => ($site['show_tagline_in_footer'] ?? true) === true,
            ],
            'nav' => ['visible' => ($site['show_nav_in_footer'] ?? true) !== false],
            'login' => is_array($site['footer_login'] ?? null) ? $site['footer_login'] : [],
            'layout' => is_array($site['footer_layout'] ?? null) ? $site['footer_layout'] : [],
        ]);
    }

    /**
     * Garantit que les listes de variantes sont stockées (pas seulement reconstruites
     * à la volée depuis les anciennes clés plates).
     *
     * @param array<string, mixed> $site
     *
     * @return array<string, mixed>
     */
    public static function materialize(array $site): array
    {
        if (!is_array($site['header_variants'] ?? null) || $site['header_variants'] === []) {
            $site['header_variants'] = self::headerVariants($site);
        }
        if (!is_array($site['footer_variants'] ?? null) || $site['footer_variants'] === []) {
            $site['footer_variants'] = self::footerVariants($site);
        }

        if (!is_string($site['active_header_variant'] ?? null) || $site['active_header_variant'] === '') {
            $site['active_header_variant'] = self::activeHeaderId($site);
        }
        if (!is_string($site['active_footer_variant'] ?? null) || $site['active_footer_variant'] === '') {
            $site['active_footer_variant'] = self::activeFooterId($site);
        }

        return $site;
    }

    /**
     * @param array<string, mixed> $link
     *
     * @return array{enabled: bool, label: string, href: string, style: string, display: string, block_ref: string}
     */
    private static function normalizeLink(array $link, string $defaultHref, string $defaultStyle = 'outline'): array
    {
        $style = (string) ($link['style'] ?? $defaultStyle);
        if (!isset(ChromeButtonRenderer::STYLES[$style])) {
            $style = $defaultStyle;
        }

        $display = (string) ($link['display'] ?? 'page');

        return [
            'enabled' => ($link['enabled'] ?? false) === true,
            'label' => (string) ($link['label'] ?? ''),
            'href' => trim((string) ($link['href'] ?? '')) !== '' ? trim((string) $link['href']) : $defaultHref,
            'style' => $style,
            'display' => in_array($display, ['page', 'modal'], true) ? $display : 'page',
            'block_ref' => trim((string) ($link['block_ref'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $layout
     * @param list<string>         $zones
     */
    private static function zone(array $layout, string $element, string $default, array $zones): string
    {
        $value = (string) ($layout[$element] ?? '');

        return in_array($value, $zones, true) ? $value : $default;
    }

    /**
     * @param mixed $raw
     *
     * @return list<array{title: string, links: list<array{label: string, href: string}>}>
     */
    private static function normalizeSections(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $sections = [];
        foreach ($raw as $section) {
            if (!is_array($section)) {
                continue;
            }
            $title = trim((string) ($section['title'] ?? ''));
            $links = self::normalizeLinks($section['links'] ?? []);
            if ($title !== '' || $links !== []) {
                $sections[] = ['title' => $title, 'links' => $links];
            }
        }

        return $sections;
    }

    /**
     * @param mixed $raw
     *
     * @return list<array{label: string, href: string}>
     */
    private static function normalizeLinks(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $links = [];
        foreach ($raw as $link) {
            if (!is_array($link)) {
                continue;
            }
            $label = trim((string) ($link['label'] ?? $link['name'] ?? ''));
            $href = trim((string) ($link['href'] ?? ''));
            if ($label !== '') {
                $links[] = ['label' => $label, 'href' => $href !== '' ? $href : '#'];
            }
        }

        return $links;
    }

    /**
     * @param mixed $raw
     *
     * @return list<array{network: string, href: string}>
     */
    private static function normalizeSocialLinks(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $links = [];
        foreach ($raw as $link) {
            if (!is_array($link)) {
                continue;
            }
            $network = strtolower(trim((string) ($link['network'] ?? $link['icon'] ?? '')));
            $href = trim((string) ($link['href'] ?? ''));
            if ($network !== '' && $href !== '') {
                $links[] = ['network' => $network, 'href' => $href];
            }
        }

        return $links;
    }

    /**
     * @param mixed $raw
     *
     * @return list<array{label: string, href: string, children: list<array{label: string, description: string, href: string, icon: string}>}>
     */
    private static function normalizeMenuItems(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $items = [];
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }
            $label = trim((string) ($item['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $children = [];
            foreach (is_array($item['children'] ?? null) ? $item['children'] : [] as $child) {
                if (!is_array($child)) {
                    continue;
                }
                $childLabel = trim((string) ($child['label'] ?? ''));
                if ($childLabel === '') {
                    continue;
                }
                $children[] = [
                    'label' => $childLabel,
                    'description' => trim((string) ($child['description'] ?? '')),
                    'href' => trim((string) ($child['href'] ?? '')) !== '' ? trim((string) ($child['href'] ?? '')) : '#',
                    'icon' => trim((string) ($child['icon'] ?? '')),
                ];
            }
            $items[] = [
                'label' => $label,
                'href' => trim((string) ($item['href'] ?? '')) !== '' ? trim((string) ($item['href'] ?? '')) : '#',
                'children' => $children,
            ];
        }

        return $items;
    }

    /**
     * @param mixed $raw
     *
     * @return list<array{title: string, description: string, href: string}>
     */
    private static function normalizeFeatureItems(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $features = [];
        foreach ($raw as $feature) {
            if (!is_array($feature)) {
                continue;
            }
            $title = trim((string) ($feature['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $features[] = [
                'title' => $title,
                'description' => trim((string) ($feature['description'] ?? '')),
                'href' => trim((string) ($feature['href'] ?? '')) !== '' ? trim((string) ($feature['href'] ?? '')) : '#',
            ];
        }

        return $features;
    }
}

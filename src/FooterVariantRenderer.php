<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Rendu HTML des pieds de page shadcnblocks (footer2, footer7).
 */
final class FooterVariantRenderer
{
    /** @var array<string, string> */
    private const SOCIAL_ICONS = [
        'instagram' => 'fa-brands fa-instagram',
        'facebook' => 'fa-brands fa-facebook-f',
        'linkedin' => 'fa-brands fa-linkedin',
        'github' => 'fa-brands fa-github',
        'x' => 'fa-brands fa-x-twitter',
        'twitter' => 'fa-brands fa-x-twitter',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $variant
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $variant, string $siteName, string $logoUrl, string $homeUrl = '/'): array
    {
        $template = FooterStyle::normalizeTemplate((string) ($variant['template'] ?? FooterStyle::TEMPLATE_DEFAULT));
        $description = trim((string) ($variant['description'] ?? ''));
        $sections = is_array($variant['sections'] ?? null) ? $variant['sections'] : [];
        $legalLinks = is_array($variant['legal_links'] ?? null) ? $variant['legal_links'] : [];
        $socialLinks = is_array($variant['social_links'] ?? null) ? $variant['social_links'] : [];

        $safeName = htmlspecialchars($siteName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeHome = htmlspecialchars($homeUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $brandInner = $logoUrl !== ''
            ? '<img class="site-footer__logo site-footer__logo--blocks" src="'
                . htmlspecialchars($logoUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '" alt="' . $safeName . '" height="28" />'
            : '<span class="site-footer__name site-footer__name--blocks">' . $safeName . '</span>';

        $data['footer_brand_column_html'] = '<a class="site-footer__brand-link" href="' . $safeHome . '">' . $brandInner . '</a>'
            . ($description !== ''
                ? '<p class="site-footer__description">' . htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>'
                : '');
        $data['footer_sections_html'] = self::sectionsHtml($sections, $template);
        $data['footer_legal_html'] = self::linksListHtml($legalLinks, 'site-footer__legal-list');
        $data['footer_social_html'] = self::socialHtml($socialLinks);

        return $data;
    }

    /**
     * @param list<array<string, mixed>> $sections
     */
    private static function sectionsHtml(array $sections, string $template): string
    {
        $max = $template === 'footer7' ? 3 : 4;
        $html = '';
        foreach (array_slice($sections, 0, $max) as $section) {
            if (!is_array($section)) {
                continue;
            }
            $title = trim((string) ($section['title'] ?? ''));
            $links = is_array($section['links'] ?? null) ? $section['links'] : [];
            if ($title === '' && $links === []) {
                continue;
            }
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<div class="site-footer__column"><h3 class="site-footer__column-title">' . $safeTitle . '</h3>'
                . self::linksListHtml($links, 'site-footer__column-list')
                . '</div>';
        }

        return $html;
    }

    /**
     * @param list<array<string, mixed>> $links
     */
    public static function linksListHtml(array $links, string $listClass, string $linkClass = 'site-footer__column-link'): string
    {
        $items = '';
        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }
            $label = trim((string) ($link['label'] ?? $link['name'] ?? ''));
            $href = trim((string) ($link['href'] ?? '#'));
            if ($label === '') {
                continue;
            }
            $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeHref = htmlspecialchars($href !== '' ? $href : '#', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $items .= '<li><a class="' . htmlspecialchars($linkClass, ENT_QUOTES) . '" href="' . $safeHref . '">' . $safeLabel . '</a></li>';
        }
        if ($items === '') {
            return '';
        }

        return '<ul class="' . $listClass . '">' . $items . '</ul>';
    }

    /**
     * @param list<array<string, mixed>> $links
     */
    private static function socialHtml(array $links): string
    {
        $items = '';
        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }
            $network = strtolower(trim((string) ($link['network'] ?? $link['icon'] ?? '')));
            $href = trim((string) ($link['href'] ?? '#'));
            $icon = self::SOCIAL_ICONS[$network] ?? '';
            if ($icon === '') {
                continue;
            }
            $label = ucfirst($network === 'x' ? 'X' : $network);
            $safeHref = htmlspecialchars($href !== '' ? $href : '#', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $items .= '<li><a class="site-footer__social-link" href="' . $safeHref
                . '" target="_blank" rel="noopener noreferrer" aria-label="' . $safeLabel . '">'
                . '<i class="' . $icon . '" aria-hidden="true"></i></a></li>';
        }
        if ($items === '') {
            return '';
        }

        return '<ul class="site-footer__social-list">' . $items . '</ul>';
    }
}

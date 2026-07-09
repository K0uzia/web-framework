<?php

declare(strict_types=1);

namespace Capsule;

final class SiteChrome
{
    public function __construct(
        private readonly PageRepository $pages,
        private readonly SiteRepository $site,
        private readonly View $view,
        private readonly string $appName,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function enrich(array $data, string $currentPath, bool $publishedOnly = true): array
    {
        $siteInfo = $this->site->getSite();
        $name = trim((string) ($siteInfo['name'] ?? ''));
        if ($name === '') {
            $name = $this->appName;
        }

        $tagline = (string) ($siteInfo['tagline'] ?? '');
        $homeLabel = (string) ($siteInfo['home_label'] ?? 'Accueil');
        $footerText = (string) ($siteInfo['footer_text'] ?? '');
        if ($footerText === '') {
            $footerText = '© {year} {name}. Tous droits réservés.';
        }
        $footerText = str_replace(
            ['{year}', '{name}'],
            [(string) date('Y'), $name],
            $footerText,
        );

        $navItems = $this->resolveNavItems($siteInfo, $homeLabel);
        $publicNav = SiteNavHelper::resolvePublicTree($navItems, $this->pages, $homeLabel);
        if ($publicNav === [] && $this->pages->allPublished() !== []) {
            $autoItems = SiteNavHelper::autoFromPages($this->pages, $homeLabel);
            $publicNav = SiteNavHelper::resolvePublicTree($autoItems, $this->pages, $homeLabel);
        }

        $headerVariant = ChromeVariants::resolveHeader($siteInfo, (string) ($data['preview_header_variant'] ?? ''));
        $footerVariant = ChromeVariants::resolveFooter($siteInfo, (string) ($data['preview_footer_variant'] ?? ''));
        unset($data['preview_header_variant'], $data['preview_footer_variant']);

        $showTagline = ($headerVariant['brand']['show_tagline'] ?? false) === true;
        $taglineHtml = ($showTagline && $tagline !== '')
            ? '<span class="site-header__tagline">' . htmlspecialchars($tagline, ENT_QUOTES) . '</span>'
            : '';

        $logoUrl = trim((string) ($siteInfo['logo_url'] ?? ''));
        $data['site_name'] = $name;
        $data['site_tagline'] = $tagline;
        $data['site_tagline_html'] = $taglineHtml;
        $data['site_brand_html'] = $logoUrl !== ''
            ? '<img class="site-header__logo" src="' . htmlspecialchars($logoUrl, ENT_QUOTES) . '" alt="' . htmlspecialchars($name, ENT_QUOTES) . '" height="28" />'
            : '<span class="site-header__name">' . htmlspecialchars($name, ENT_QUOTES) . '</span>';
        $data['footer_text'] = $footerText;
        $data['current_path'] = $currentPath;
        $data['nav_html'] = SiteNavHelper::renderNavHtml($publicNav, $currentPath);
        $data['header_cta_html'] = ChromeButtonRenderer::render($headerVariant['cta'], 'primary');
        $data['favicon_html'] = $this->faviconHtml((string) ($siteInfo['favicon_url'] ?? ''));
        $data['og_image_html'] = $this->ogImageHtml((string) ($siteInfo['og_image_url'] ?? ''));

        $data = $this->buildHeaderZones($data, $headerVariant, $name, $logoUrl, $taglineHtml);
        $data = $this->buildFooterZones($data, $footerVariant, $name, $logoUrl, $tagline);

        $partials = is_array($siteInfo['partials'] ?? null) ? $siteInfo['partials'] : [];
        $showHeader = ($partials['header'] ?? true) !== false;
        $showFooter = ($partials['footer'] ?? true) !== false;

        $showHeader = $this->applyVisibilityOverride($showHeader, $data['show_header'] ?? 'default');
        $showFooter = $this->applyVisibilityOverride($showFooter, $data['show_footer'] ?? 'default');
        unset($data['show_header'], $data['show_footer']);

        $data['header_html'] = $showHeader
            ? $this->renderHeaderHtml($data, $headerVariant, $name, $logoUrl)
            : '';
        $data['footer_html'] = $showFooter
            ? $this->renderFooterHtml($data, $footerVariant, $siteInfo, $name, $logoUrl)
            : '';

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $variant
     */
    private function renderHeaderHtml(array $data, array $variant, string $name, string $logoUrl): string
    {
        $template = HeaderStyle::normalizeTemplate((string) ($variant['template'] ?? HeaderStyle::TEMPLATE_DEFAULT));
        if (!HeaderStyle::isBlocksTemplate($template)) {
            return $this->view->render('site-header.html', $data);
        }

        $data = HeaderVariantRenderer::enrich($data, $variant, $name, $logoUrl);

        return $this->view->render('site-header-' . $template . '.html', $data);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $variant
     * @param array<string, mixed> $siteInfo
     */
    private function renderFooterHtml(array $data, array $variant, array $siteInfo, string $name, string $logoUrl): string
    {
        $template = FooterStyle::normalizeTemplate((string) ($variant['template'] ?? FooterStyle::TEMPLATE_DEFAULT));
        if (!FooterStyle::isBlocksTemplate($template)) {
            return $this->view->render('site-footer.html', $data);
        }

        $data = FooterVariantRenderer::enrich($data, $variant, $name, $logoUrl);

        return $this->view->render('site-footer-' . $template . '.html', $data);
    }

    /**
     * Construit les trois zones (gauche, centre, droite) de l'en-tête à partir de
     * la variante résolue (emplacement configuré pour brand, nav, cta, login).
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $variant
     *
     * @return array<string, mixed>
     */
    private function buildHeaderZones(array $data, array $variant, string $name, string $logoUrl, string $taglineHtml): array
    {
        $layout = $variant['layout'];
        $showNav = ($variant['nav']['visible'] ?? true) !== false;

        $ctaHtml = (string) $data['header_cta_html'];
        $loginHtml = ChromeButtonRenderer::render($variant['login'], 'outline');

        // Sur mobile, les boutons d'action rejoignent le menu déroulant : on en
        // duplique une copie dans le <nav> (masquée sur desktop via CSS).
        $menuActions = $ctaHtml . $loginHtml;
        $navHtml = '';
        if ($showNav) {
            $navHtml = '<nav class="site-header__nav site-nav" id="site-header-nav" aria-label="Navigation principale">'
                . $data['nav_html']
                . ($menuActions !== '' ? '<div class="site-header__menu-actions">' . $menuActions . '</div>' : '')
                . '</nav>';
        }

        $elements = [
            'brand' => $this->headerBrandHtml($name, $logoUrl, $taglineHtml, $variant['brand']),
            'nav' => $navHtml,
            'cta' => $ctaHtml,
            'login' => $loginHtml,
        ];

        foreach (ChromeVariants::HEADER_ZONES as $zone) {
            $html = '';
            foreach ($elements as $element => $elementHtml) {
                if (($layout[$element] ?? '') === $zone && $elementHtml !== '') {
                    $html .= $elementHtml;
                }
            }
            $data['header_zone_' . $zone] = $html;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $brandConfig
     */
    private function headerBrandHtml(string $name, string $logoUrl, string $taglineHtml, array $brandConfig): string
    {
        $showLogo = ($brandConfig['show_logo'] ?? true) !== false;
        $showName = ($brandConfig['show_name'] ?? true) !== false;

        $parts = '';
        if ($showLogo && $logoUrl !== '') {
            $parts .= '<img class="site-header__logo" src="' . htmlspecialchars($logoUrl, ENT_QUOTES)
                . '" alt="' . htmlspecialchars($name, ENT_QUOTES) . '" height="28" />';
        }
        if ($showName) {
            $parts .= '<span class="site-header__name">' . htmlspecialchars($name, ENT_QUOTES) . '</span>';
        }
        if ($parts === '') {
            return '';
        }

        return '<a class="site-header__brand" href="/">'
            . '<span class="site-header__brand-main">' . $parts . '</span>'
            . $taglineHtml . '</a>';
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $variant
     *
     * @return array<string, mixed>
     */
    private function buildFooterZones(array $data, array $variant, string $name, string $logoUrl, string $tagline): array
    {
        $layout = $variant['layout'];
        $showNav = ($variant['nav']['visible'] ?? true) !== false;

        $showBrandBlock = ($variant['brand']['visible'] ?? true) !== false;
        $showLogo = ($variant['brand']['show_logo'] ?? true) !== false;
        $showName = ($variant['brand']['show_name'] ?? true) !== false;
        $showTagline = ($variant['brand']['show_tagline'] ?? true) !== false;

        $brandHtml = '';
        if ($showBrandBlock) {
            $hasNameLink = ($showLogo && $logoUrl !== '') || ($showName && $name !== '');
            $hasTagline = $showTagline && $tagline !== '';
            if ($hasNameLink || $hasTagline) {
                $brandHtml = '<div class="site-footer__brand">';
                if ($hasNameLink) {
                    $brandHtml .= '<a class="site-footer__name" href="/">';
                    if ($showLogo && $logoUrl !== '') {
                        $brandHtml .= '<img class="site-footer__logo" src="' . htmlspecialchars($logoUrl, ENT_QUOTES)
                            . '" alt="' . htmlspecialchars($name, ENT_QUOTES) . '" height="24" />';
                    } elseif ($showName && $name !== '') {
                        $brandHtml .= htmlspecialchars($name, ENT_QUOTES);
                    }
                    $brandHtml .= '</a>';
                }
                if ($hasTagline) {
                    $brandHtml .= '<p class="site-footer__tagline">' . htmlspecialchars($tagline, ENT_QUOTES) . '</p>';
                }
                $brandHtml .= '</div>';
            }
        }

        $elements = [
            'brand' => $brandHtml,
            'nav' => $showNav
                ? '<nav class="site-footer__nav site-nav" aria-label="Navigation pied de page">' . $data['nav_html'] . '</nav>'
                : '',
            'login' => ChromeButtonRenderer::render($variant['login'], 'outline'),
        ];

        foreach (ChromeVariants::FOOTER_ZONES as $zone) {
            $html = '';
            foreach ($elements as $element => $elementHtml) {
                if (($layout[$element] ?? '') === $zone && $elementHtml !== '') {
                    $html .= $elementHtml;
                }
            }
            $data['footer_zone_' . $zone] = $html;
        }

        // Compatibilité avec les gabarits existants.
        $data['footer_brand_html'] = $brandHtml;
        $data['footer_nav_html'] = $elements['nav'];

        return $data;
    }

    private function applyVisibilityOverride(bool $siteDefault, mixed $override): bool
    {
        return match ($override) {
            'show' => true,
            'hide' => false,
            default => $siteDefault,
        };
    }

    private function faviconHtml(string $faviconUrl): string
    {
        if ($faviconUrl === '') {
            return '<link rel="icon" href="/favicon.svg" type="image/svg+xml" />';
        }

        $safe = htmlspecialchars($faviconUrl, ENT_QUOTES);
        $type = str_ends_with(strtolower($faviconUrl), '.svg') ? ' type="image/svg+xml"' : '';

        return '<link rel="icon" href="' . $safe . '"' . $type . ' />';
    }

    private function ogImageHtml(string $imageUrl): string
    {
        if ($imageUrl === '') {
            return '';
        }

        $safe = htmlspecialchars($imageUrl, ENT_QUOTES);

        return '<meta property="og:image" content="' . $safe . '" />'
            . '<meta name="twitter:card" content="summary_large_image" />'
            . '<meta name="twitter:image" content="' . $safe . '" />';
    }

    /**
     * @param array<string, mixed> $siteInfo
     *
     * @return list<array<string, mixed>>
     */
    private function resolveNavItems(array $siteInfo, string $homeLabel): array
    {
        $configured = is_array($siteInfo['nav_items'] ?? null) ? $siteInfo['nav_items'] : [];
        $navMode = (string) ($siteInfo['nav_mode'] ?? 'auto');

        if ($configured === [] || $navMode === 'auto') {
            return SiteNavHelper::autoFromPages($this->pages, $homeLabel);
        }

        return SiteNavHelper::normalize($configured);
    }
}

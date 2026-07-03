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
        $publicNav = SiteNavHelper::resolvePublicItems($navItems, $this->pages, $homeLabel);
        if ($publicNav === [] && $this->pages->allPublished() !== []) {
            $autoItems = SiteNavHelper::autoFromPages($this->pages, $homeLabel);
            $publicNav = SiteNavHelper::resolvePublicItems($autoItems, $this->pages, $homeLabel);
        }

        $headerCta = is_array($siteInfo['header_cta'] ?? null) ? $siteInfo['header_cta'] : [];
        $showTagline = ($siteInfo['show_tagline_in_header'] ?? false) === true;
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
        $data['header_cta_html'] = SiteNavHelper::renderHeaderCtaHtml($headerCta);
        $data['favicon_html'] = $this->faviconHtml((string) ($siteInfo['favicon_url'] ?? ''));
        $data['og_image_html'] = $this->ogImageHtml((string) ($siteInfo['og_image_url'] ?? ''));

        $partials = is_array($siteInfo['partials'] ?? null) ? $siteInfo['partials'] : [];
        $showHeader = ($partials['header'] ?? true) !== false;
        $showFooter = ($partials['footer'] ?? true) !== false;

        $showHeader = $this->applyVisibilityOverride($showHeader, $data['show_header'] ?? 'default');
        $showFooter = $this->applyVisibilityOverride($showFooter, $data['show_footer'] ?? 'default');
        unset($data['show_header'], $data['show_footer']);

        $data['header_html'] = $showHeader
            ? $this->view->render('site-header.html', $data)
            : '';
        $data['footer_html'] = $showFooter
            ? $this->view->render('site-footer.html', $data)
            : '';

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

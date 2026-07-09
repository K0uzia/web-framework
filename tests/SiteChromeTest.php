<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteChrome;
use Capsule\SiteRepository;
use Capsule\View;
use PHPUnit\Framework\TestCase;

final class SiteChromeTest extends TestCase
{
    public function testBuildsNavWithActiveLinkAndFooterTokens(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);

        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));
        $pages->save(new Page('about', 'About', 'default', '', [], [], true, ''));

        $site->setSite([
            'name' => 'Demo',
            'tagline' => 'Tag',
            'home_label' => 'Start',
            'footer_text' => '© {year} {name}',
        ]);

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');

        $chrome = new SiteChrome($pages, $site, $view, 'Fallback');
        $data = $chrome->enrich([], '/about');

        $this->assertSame('Demo', $data['site_name']);
        $this->assertStringContainsString('href="/about"', $data['nav_html']);
        $this->assertStringContainsString('is-active', $data['nav_html']);
        $this->assertStringContainsString('Start', $data['nav_html']);
        $this->assertStringContainsString((string) date('Y'), $data['footer_text']);
        $this->assertStringContainsString('Demo', $data['footer_text']);
        $this->assertStringContainsString('site-header', $data['header_html']);
        $this->assertStringContainsString('site-footer', $data['footer_html']);
        $this->assertStringContainsString('rel="icon" href="/favicon.svg"', $data['favicon_html']);
        $this->assertSame('', $data['og_image_html']);
    }

    public function testFaviconAndOgImageAreRenderedWhenConfigured(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));

        $site->setSite([
            'name' => 'Demo',
            'favicon_url' => '/assets/favicon.svg',
            'og_image_url' => 'https://example.com/og.jpg',
        ]);

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');
        $chrome = new SiteChrome($pages, $site, $view, 'Fallback');
        $data = $chrome->enrich([], '/');

        $this->assertStringContainsString('href="/assets/favicon.svg" type="image/svg+xml"', $data['favicon_html']);
        $this->assertStringContainsString('property="og:image" content="https://example.com/og.jpg"', $data['og_image_html']);
        $this->assertStringContainsString('name="twitter:card" content="summary_large_image"', $data['og_image_html']);
    }

    public function testLogoReplacesTextBrandWhenConfigured(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));
        $site->setSite(['name' => 'Demo', 'logo_url' => '/assets/logo.png']);

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');
        $chrome = new SiteChrome($pages, $site, $view, 'Fallback');
        $data = $chrome->enrich([], '/');

        $this->assertStringContainsString('<img class="site-header__logo" src="/assets/logo.png" alt="Demo"', $data['site_brand_html']);
        $this->assertStringContainsString('site-header__logo', $data['header_html']);
    }

    public function testHeaderZonesAreBuiltForZoneBasedTemplate(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));

        $site->setSite(['name' => 'Demo', 'logo_url' => '/assets/logo.png']);

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');
        $chrome = new SiteChrome($pages, $site, $view, 'Fallback');
        $data = $chrome->enrich([], '/');

        $this->assertStringContainsString('site-header__brand', $data['header_zone_left']);
        $this->assertStringContainsString('site-header__nav', $data['header_zone_right']);
        $this->assertStringContainsString('site-footer__brand', $data['footer_brand_html']);
    }

    public function testCustomNavRendersLinkButtonAndHeaderCta(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));

        $site->setSite([
            'name' => 'Demo',
            'tagline' => '',
            'home_label' => 'Accueil',
            'footer_text' => '',
            'partials' => ['header' => true, 'footer' => false],
            'nav_mode' => 'custom',
            'nav_items' => [
                ['id' => 'n1', 'type' => 'page', 'slug' => '', 'href' => '', 'label' => 'Start', 'visible' => true],
                ['id' => 'n2', 'type' => 'link', 'slug' => '', 'href' => 'https://example.com', 'label' => 'Docs', 'visible' => true],
                ['id' => 'n3', 'type' => 'button', 'slug' => '', 'href' => '/go', 'label' => 'Go', 'visible' => true],
            ],
            'header_cta' => ['enabled' => true, 'label' => 'Contact', 'href' => '/contact'],
        ]);

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');
        $chrome = new SiteChrome($pages, $site, $view, 'Fallback');
        $data = $chrome->enrich([], '/');

        $this->assertStringContainsString('href="https://example.com"', $data['nav_html']);
        $this->assertStringContainsString('site-nav__link--button', $data['nav_html']);
        $this->assertStringContainsString('site-chrome-btn--primary', $data['header_html']);
        $this->assertStringContainsString('Contact', $data['header_html']);
        $this->assertSame('', $data['footer_html']);
    }

    public function testHeaderLoginButtonAndZonePlacement(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));

        $site->setSite([
            'name' => 'Demo',
            'header_login' => ['enabled' => true, 'label' => 'Se connecter', 'href' => '/login'],
            'header_layout' => ['brand' => 'center', 'nav' => 'left', 'cta' => 'right', 'login' => 'right'],
        ]);

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');
        $chrome = new SiteChrome($pages, $site, $view, 'Fallback');
        $data = $chrome->enrich([], '/');

        $this->assertStringContainsString('site-chrome-btn', $data['header_zone_right']);
        $this->assertStringContainsString('Se connecter', $data['header_zone_right']);
        $this->assertStringContainsString('site-header__brand', $data['header_zone_center']);
        $this->assertStringContainsString('site-header__nav', $data['header_zone_left']);
        $this->assertStringContainsString('site-chrome-btn', $data['header_html']);
    }

    public function testHeaderBrandTogglesHideLogoAndName(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));

        $site->setSite([
            'name' => 'Demo',
            'logo_url' => '/assets/logo.png',
            'header_brand' => ['show_logo' => true, 'show_name' => false],
        ]);

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');
        $chrome = new SiteChrome($pages, $site, $view, 'Fallback');
        $data = $chrome->enrich([], '/');

        $this->assertStringContainsString('site-header__logo', $data['header_zone_left']);
        $this->assertStringNotContainsString('site-header__name', $data['header_zone_left']);
    }

    public function testFooterZonesPlaceNavAndLogin(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));

        $site->setSite([
            'name' => 'Demo',
            'footer_login' => ['enabled' => true, 'label' => 'Connexion', 'href' => '/login'],
            'footer_layout' => ['brand' => 'right', 'nav' => 'left', 'login' => 'left'],
        ]);

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');
        $chrome = new SiteChrome($pages, $site, $view, 'Fallback');
        $data = $chrome->enrich([], '/');

        $this->assertStringContainsString('site-footer__nav', $data['footer_zone_left']);
        $this->assertStringContainsString('site-chrome-btn', $data['footer_zone_left']);
        $this->assertStringContainsString('site-footer__brand', $data['footer_zone_right']);
    }

    public function testActiveHeaderVariantDrivesRendering(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));

        $site->setSite([
            'name' => 'Demo',
            'header_variants' => [
                [
                    'id' => 'default',
                    'name' => 'Par défaut',
                    'nav' => ['visible' => true],
                ],
                [
                    'id' => 'minimal',
                    'name' => 'Minimal',
                    'nav' => ['visible' => false],
                    'login' => ['enabled' => true, 'label' => 'Connexion', 'href' => '/login'],
                ],
            ],
            'active_header_variant' => 'minimal',
        ]);

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');
        $chrome = new SiteChrome($pages, $site, $view, 'Fallback');
        $data = $chrome->enrich([], '/');

        $this->assertStringNotContainsString('site-header__nav', $data['header_html']);
        $this->assertStringContainsString('site-chrome-btn', $data['header_html']);

        // La variante prévisualisée peut différer de la variante active.
        $preview = $chrome->enrich(['preview_header_variant' => 'default'], '/');
        $this->assertStringContainsString('site-header__nav', $preview['header_html']);
    }

    public function testPageLevelOverrideHidesHeaderEvenWhenSiteShowsItByDefault(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));
        $site->setSite(['name' => 'Demo', 'partials' => ['header' => true, 'footer' => true]]);

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');
        $chrome = new SiteChrome($pages, $site, $view, 'Fallback');

        $data = $chrome->enrich(['show_header' => 'hide'], '/');

        $this->assertSame('', $data['header_html']);
        $this->assertStringContainsString('site-footer', $data['footer_html']);
        $this->assertArrayNotHasKey('show_header', $data);
    }

    public function testPageLevelOverrideShowsFooterEvenWhenSiteHidesItByDefault(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));
        $site->setSite(['name' => 'Demo', 'partials' => ['header' => true, 'footer' => false]]);

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');
        $chrome = new SiteChrome($pages, $site, $view, 'Fallback');

        $data = $chrome->enrich(['show_footer' => 'show'], '/');

        $this->assertStringContainsString('site-footer', $data['footer_html']);
    }

    public function testFooter2TemplateRendersBlockLayout(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));

        $site->setSite([
            'name' => 'Demo',
            'tagline' => 'Tagline test',
            'footer_variants' => [
                [
                    'id' => 'footer-blocks',
                    'name' => 'Footer colonnes',
                    'template' => 'footer2',
                    'description' => 'Description pied de page.',
                    'sections' => [
                        [
                            'title' => 'Produit',
                            'links' => [['label' => 'Tarifs', 'href' => '/pricing']],
                        ],
                    ],
                    'legal_links' => [
                        ['label' => 'Mentions légales', 'href' => '/legal'],
                    ],
                ],
            ],
            'active_footer_variant' => 'footer-blocks',
        ]);

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');
        $chrome = new SiteChrome($pages, $site, $view, 'Fallback');
        $data = $chrome->enrich([], '/');

        $this->assertStringContainsString('site-footer--footer2', $data['footer_html']);
        $this->assertStringContainsString('site-footer__column-title', $data['footer_html']);
        $this->assertStringContainsString('Tarifs', $data['footer_html']);
        $this->assertStringContainsString('Mentions légales', $data['footer_html']);
    }

    public function testNavbar1TemplateRendersBlockLayout(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));

        $site->setSite([
            'name' => 'Demo',
            'header_variants' => [
                [
                    'id' => 'header-blocks',
                    'name' => 'Navbar principale',
                    'template' => 'navbar1',
                    'menu_items' => [
                        ['label' => 'Accueil', 'href' => '/'],
                        ['label' => 'Tarifs', 'href' => '/pricing'],
                    ],
                    'login' => ['enabled' => true, 'label' => 'Connexion', 'href' => '/login', 'style' => 'outline'],
                    'cta' => ['enabled' => true, 'label' => 'Commencer', 'href' => '#', 'style' => 'primary'],
                ],
            ],
            'active_header_variant' => 'header-blocks',
        ]);

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');
        $chrome = new SiteChrome($pages, $site, $view, 'Fallback');
        $data = $chrome->enrich([], '/');

        $this->assertStringContainsString('site-header--navbar1', $data['header_html']);
        $this->assertStringContainsString('site-header__blocks-brand', $data['header_html']);
        $this->assertStringContainsString('Accueil', $data['header_html']);
        $this->assertStringContainsString('Connexion', $data['header_html']);
    }

    public function testNavbar5TemplateRendersBlockLayout(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));

        $site->setSite([
            'name' => 'Demo',
            'header_variants' => [
                [
                    'id' => 'header-navbar5',
                    'name' => 'Navbar 5',
                    'template' => 'navbar5',
                    'features_label' => 'Fonctionnalités',
                    'features' => [
                        ['title' => 'Tableau de bord', 'description' => 'Vue d\'ensemble', 'href' => '#'],
                    ],
                    'nav_links' => [
                        ['label' => 'Produits', 'href' => '#'],
                    ],
                    'login' => ['enabled' => true, 'label' => 'Connexion', 'href' => '/login', 'style' => 'outline'],
                    'cta' => ['enabled' => true, 'label' => 'Essai gratuit', 'href' => '#', 'style' => 'primary'],
                ],
            ],
            'active_header_variant' => 'header-navbar5',
        ]);

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');
        $chrome = new SiteChrome($pages, $site, $view, 'Fallback');
        $data = $chrome->enrich([], '/');

        $this->assertStringContainsString('site-header--navbar5', $data['header_html']);
        $this->assertStringContainsString('site-header__blocks-nav--navbar5', $data['header_html']);
        $this->assertStringContainsString('site-header__blocks-sheet', $data['header_html']);
        $this->assertStringContainsString('Fonctionnalités', $data['header_html']);
        $this->assertStringContainsString('Produits', $data['header_html']);
    }
}

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
        $this->assertStringContainsString('site-header__cta', $data['header_html']);
        $this->assertStringContainsString('Contact', $data['header_html']);
        $this->assertSame('', $data['footer_html']);
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
}

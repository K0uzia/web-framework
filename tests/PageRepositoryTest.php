<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Database;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteRepository;
use PHPUnit\Framework\TestCase;

final class PageRepositoryTest extends TestCase
{
    private PageRepository $pages;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $sql = file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql');
        $pdo->exec($sql !== false ? $sql : '');
        $this->pages = new PageRepository($pdo);
    }

    public function testSaveAndFindBySlug(): void
    {
        $this->pages->save(new Page(
            slug: 'about',
            title: 'About',
            layout: 'default',
            description: 'Desc',
            sections: [['id' => 'h1', 'type' => 'hero', 'variant' => 'centered', 'content' => [], 'style' => []]],
            meta: ['schema_type' => 'WebPage'],
            published: true,
            updatedAt: '',
        ));

        $page = $this->pages->findBySlug('about');
        $this->assertNotNull($page);
        $this->assertSame('About', $page->title);
        $this->assertSame('hero', $page->sections[0]['type']);
    }

    public function testEmptySlugIsHome(): void
    {
        $this->pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));
        $page = $this->pages->findBySlug('');
        $this->assertNotNull($page);
        $this->assertSame('/', $page->routePath());
    }

    public function testSetHomePageSwapsWithCurrentHome(): void
    {
        $this->pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));
        $this->pages->save(new Page('about', 'About', 'default', '', [], [], true, ''));

        $this->pages->setHomePage('about');

        $home = $this->pages->findBySlug('', false);
        $former = $this->pages->findBySlug('about', false);
        $this->assertSame('About', $home?->title);
        $this->assertSame('Home', $former?->title);
    }
}

final class SiteRepositoryTest extends TestCase
{
    private function makeSite(): SiteRepository
    {
        $pdo = new \PDO('sqlite::memory:');
        $sql = file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql');
        $pdo->exec($sql !== false ? $sql : '');

        return new SiteRepository($pdo);
    }

    public function testThemeRoundTrip(): void
    {
        $site = $this->makeSite();

        $site->setTheme(['colors' => ['primary' => '#111111']]);
        $theme = $site->getTheme();

        $this->assertSame('#111111', $theme['colors']['primary']);
        $this->assertStringContainsString('--color-primary: #111111', $site->themeCss());
    }

    public function testThemeCssOmitsFontFaceByDefault(): void
    {
        $this->assertStringNotContainsString('@font-face', $this->makeSite()->themeCss());
    }

    public function testThemeCssGeneratesFontFaceForCustomFonts(): void
    {
        $site = $this->makeSite();
        $theme = $site->defaultTheme();
        $theme['custom_fonts'] = [
            ['id' => 'font-a', 'name' => 'Brand Sans', 'url' => '/uploads/fonts/brand.woff2', 'format' => 'woff2'],
        ];
        $site->setTheme($theme);

        $css = $site->themeCss();

        $this->assertStringContainsString('@font-face', $css);
        $this->assertStringContainsString('font-family: "Brand Sans";', $css);
        $this->assertStringContainsString('src: url("/uploads/fonts/brand.woff2") format(\'woff2\');', $css);
        $this->assertLessThan(strpos($css, ':root {'), strpos($css, '@font-face'));
    }

    public function testThemeCssSkipsIncompleteFontEntries(): void
    {
        $site = $this->makeSite();
        $theme = $site->defaultTheme();
        $theme['custom_fonts'] = [
            ['id' => 'font-a', 'name' => '', 'url' => '/uploads/fonts/broken.woff2'],
            ['id' => 'font-b', 'name' => 'No Url'],
        ];
        $site->setTheme($theme);

        $this->assertStringNotContainsString('@font-face', $site->themeCss());
    }
}

<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Page;
use Capsule\PageRenderer;
use Capsule\PageRepository;
use Capsule\SectionRenderer;
use Capsule\SiteChrome;
use Capsule\SiteRepository;
use Capsule\ScriptResolver;
use Capsule\StylesheetResolver;
use Capsule\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PageRenderer::class)]
final class PageRendererTest extends TestCase
{
    private PageRepository $pages;
    private PageRenderer $renderer;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');
        $this->pages = new PageRepository($pdo);
        $root = dirname(__DIR__);

        $site = new SiteRepository($pdo);

        $this->pages->save(new Page(
            slug: 'home',
            title: '<unsafe>',
            layout: 'default',
            description: 'Meta desc',
            sections: [[
                'id' => 'hero-1',
                'type' => 'hero',
                'variant' => 'hero3',
                'content' => [
                    'title' => 'Safe title',
                    'subtitle' => 'Sub',
                    'cta_label' => 'CTA',
                    'cta_href' => '#',
                ],
                'style' => ['bg' => 'primary', 'text_align' => 'center', 'padding' => 'lg'],
            ]],
            meta: ['schema_type' => 'WebPage'],
            published: true,
            updatedAt: '',
        ));

        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');
        $cssDir = sys_get_temp_dir() . '/capsule-page-css-' . uniqid('', true);
        mkdir($cssDir);
        copy(
            $root . '/public/assets/css/theme-bindings.css',
            $cssDir . '/theme-bindings.css',
        );

        $this->renderer = new PageRenderer(
            new ResponseFactory(),
            $view,
            $this->pages,
            $site,
            new SectionRenderer($view, $root . '/resources/sections'),
            new SiteChrome($this->pages, $site, $view, 'CapsulePHP'),
            'https://example.com',
            new StylesheetResolver($root . '/public/assets/css'),
            new ScriptResolver($root . '/public/assets/js'),
            $cssDir,
        );
    }

    public function testRendersPageFromDatabase(): void
    {
        $body = (string) $this->renderer->renderBySlug('home', [], '/home')->getBody();

        $this->assertStringContainsString('Safe title', $body);
        $this->assertStringContainsString('<style>', $body);
        $this->assertStringContainsString('--color-primary:', $body);
        $this->assertStringContainsString('href="/assets/css/theme-bindings.css?v=', $body);
        $this->assertStringContainsString('<meta name="description" content="Meta desc"', $body);
        $this->assertStringContainsString('sections/hero.js', $body);
        $this->assertStringNotContainsString('sections/gallery.js', $body);
    }

    public function testEscapesPageTitleInLayout(): void
    {
        $body = (string) $this->renderer->renderBySlug('home')->getBody();
        $this->assertStringContainsString('<title>&lt;unsafe&gt;</title>', $body);
    }
}

<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Http\Factory\ResponseFactory;
use PHPUnit\Framework\TestCase;

final class ThemePreviewRendererTest extends TestCase
{
    public function testRenderBuildsThemeShowcasePage(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $root = dirname(__DIR__);
        $view = new \Capsule\View($root . '/resources/layouts', $root . '/resources/partials');
        $site = new \Capsule\SiteRepository($pdo);
        $pages = new \Capsule\PageRepository($pdo);
        $chrome = new \Capsule\SiteChrome($pages, $site, $view, 'CapsulePHP');
        $stylesheets = new \Capsule\StylesheetResolver($root . '/public/assets/css');

        $renderer = new ThemePreviewRenderer(
            new ResponseFactory(),
            $view,
            $site,
            $chrome,
            $stylesheets,
            'https://example.com',
        );

        $body = (string) $renderer->render()->getBody();

        $this->assertStringContainsString('theme-preview', $body);
        $this->assertStringContainsString('theme-preview.css', $body);
        $this->assertStringContainsString('theme-bindings.css', $body);
        $this->assertStringContainsString('section-ui-alert--success', $body);
        $this->assertStringContainsString('section-hero--centered', $body);
        $this->assertStringContainsString('noindex, nofollow', $body);
        $this->assertStringNotContainsString('theme-states-preview', $body);
    }
}

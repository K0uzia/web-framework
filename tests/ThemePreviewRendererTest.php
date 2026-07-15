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
        $scripts = new \Capsule\ScriptResolver($root . '/public/assets/js');
        $cssDir = sys_get_temp_dir() . '/capsule-theme-preview-' . uniqid('', true);
        mkdir($cssDir);
        copy(
            $root . '/public/assets/css/theme-bindings.css',
            $cssDir . '/theme-bindings.css',
        );

        $renderer = new \Capsule\ThemePreviewRenderer(
            new ResponseFactory(),
            $view,
            $site,
            $chrome,
            $stylesheets,
            $scripts,
            $cssDir,
            'https://example.com',
        );

        $body = (string) $renderer->render()->getBody();

        $this->assertStringContainsString('theme-preview', $body);
        $this->assertStringContainsString('theme-preview.css', $body);
        $this->assertStringContainsString('<style>', $body);
        $this->assertStringContainsString('--color-primary:', $body);
        $this->assertStringContainsString('href="/assets/css/theme-bindings.css?v=', $body);
        $this->assertStringContainsString('section-hero--hero3', $body);
        $this->assertStringContainsString('theme-preview__alert--success', $body);
        $this->assertStringContainsString('noindex, nofollow', $body);
        $this->assertStringNotContainsString('theme-states-preview', $body);
    }
}

<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Http\Factory\ResponseFactory;
use Capsule\Page;
use Capsule\PageRenderer;
use Capsule\PageRepository;
use Capsule\SectionRenderer;
use Capsule\SiteChrome;
use Capsule\SiteRepository;
use Capsule\ScriptResolver;
use Capsule\StylesheetResolver;
use Capsule\View;
use PHPUnit\Framework\TestCase;

final class ThemeCascadeTest extends TestCase
{
    public function testThemeCssComesAfterStylesheets(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $root = dirname(__DIR__);
        $view = new View($root . '/resources/layouts', $root . '/resources/partials');

        $pages->save(new Page(
            slug: 'demo',
            title: 'Demo',
            layout: 'default',
            description: '',
            sections: [],
            meta: [],
            published: true,
            updatedAt: '',
        ));

        $theme = $site->getTheme();
        $theme['colors']['primary'] = '#aabbcc';
        $site->setTheme($theme);

        $cssDir = sys_get_temp_dir() . '/capsule-theme-css-' . uniqid('', true);
        mkdir($cssDir);

        $renderer = new PageRenderer(
            new ResponseFactory(),
            $view,
            $pages,
            $site,
            new SectionRenderer($view, $root . '/resources/sections'),
            new SiteChrome($pages, $site, $view, 'CapsulePHP'),
            'https://example.com',
            new StylesheetResolver($root . '/public/assets/css'),
            new ScriptResolver($root . '/public/assets/js'),
            $cssDir,
        );

        $body = (string) $renderer->renderBySlug('demo', [], '/demo')->getBody();
        $themeLinkPos = strpos($body, 'theme-generated.css');
        $bindingsPos = strpos($body, 'href="/assets/css/theme-bindings.css"');

        $this->assertNotFalse($themeLinkPos);
        $this->assertNotFalse($bindingsPos);
        $this->assertLessThan($bindingsPos, $themeLinkPos);
        $this->assertStringContainsString('--color-primary: #aabbcc', file_get_contents($cssDir . '/theme-generated.css') ?: '');
    }
}

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

        $renderer = new PageRenderer(
            new ResponseFactory(),
            $view,
            $pages,
            $site,
            new SectionRenderer($view, $root . '/resources/sections'),
            new SiteChrome($pages, $site, $view, 'CapsulePHP'),
            'https://example.com',
            new StylesheetResolver($root . '/public/assets/css'),
        );

        $body = (string) $renderer->renderBySlug('demo', [], '/demo')->getBody();
        $themePos = strpos($body, '--color-primary: #aabbcc');
        $bindingsPos = strpos($body, 'href="/assets/css/theme-bindings.css"');

        $this->assertNotFalse($themePos);
        $this->assertNotFalse($bindingsPos);
        $this->assertLessThan($bindingsPos, $themePos);
    }
}

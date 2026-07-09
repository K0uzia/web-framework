<?php

declare(strict_types=1);

namespace Tests;

use Capsule\MediaUsageScanner;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteRepository;
use PHPUnit\Framework\TestCase;

final class MediaUsageScannerTest extends TestCase
{
    public function testReportCountsPagesAndBlocksRecursively(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $site->setSite(array_merge($site->getSite(), [
            'logo_url' => '/uploads/site/logo.png',
        ]));

        $pages->save(new Page(
            slug: 'home',
            title: 'Home',
            layout: 'default',
            description: '',
            sections: [[
                'id' => 'hero-1',
                'type' => 'hero',
                'variant' => 'hero1',
                'visible' => true,
                'content' => [
                    'image_url' => '/uploads/media/hero.webp',
                    'image_url_dark' => '/uploads/media/hero-dark.webp',
                    'review_avatars' => [['url' => '/uploads/media/avatar.jpg', 'title' => 'A']],
                ],
                'style' => [],
            ]],
            meta: [],
            published: true,
            updatedAt: '',
        ));

        $pages->save(new Page(
            slug: 'about',
            title: 'About',
            layout: 'default',
            description: '',
            sections: [[
                'id' => 'gallery-1',
                'type' => 'gallery',
                'variant' => 'grid',
                'visible' => true,
                'content' => ['items' => [['url' => '/uploads/media/hero.webp', 'title' => 'A']]],
                'style' => [],
            ]],
            meta: [],
            published: true,
            updatedAt: '',
        ));

        $scanner = new MediaUsageScanner($pages, $site);
        $report = $scanner->report('/uploads/media/hero.webp');

        $this->assertSame(2, $report['page_count']);
        $this->assertSame(2, $report['block_count']);
        $this->assertCount(2, $report['entries']);

        $logoReport = $scanner->report('/uploads/site/logo.png');
        $this->assertSame(['Logo du site'], $logoReport['site_labels']);
        $this->assertSame(0, $logoReport['block_count']);
    }
}

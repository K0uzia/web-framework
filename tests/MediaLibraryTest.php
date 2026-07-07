<?php

declare(strict_types=1);

namespace Tests;

use Capsule\MediaLibrary;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteRepository;
use Capsule\StockImages;
use PHPUnit\Framework\TestCase;

final class MediaLibraryTest extends TestCase
{
    public function testCollectsUploadsStockAndSectionImages(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $site->setSite(array_merge($site->getSite(), ['logo_url' => '/uploads/site/logo-test.png']));

        $uploadsDir = sys_get_temp_dir() . '/capsule-library-' . bin2hex(random_bytes(4));
        mkdir($uploadsDir);
        file_put_contents($uploadsDir . '/section-abc.webp', 'x');

        $pages->save(new Page(
            slug: 'home',
            title: 'Home',
            layout: 'default',
            description: '',
            sections: [[
                'id' => 'hero-1',
                'type' => 'hero',
                'variant' => 'split',
                'visible' => true,
                'content' => ['image_url' => '/uploads/site/hero-custom.jpg'],
                'style' => [],
            ]],
            meta: [],
            published: true,
            updatedAt: '',
        ));

        $library = new MediaLibrary($uploadsDir, '/uploads/site', $pages, $site);
        $urls = $library->availableUrls();

        $this->assertContains('/uploads/site/logo-test.png', $urls);
        $this->assertContains('/uploads/site/section-abc.webp', $urls);
        $this->assertContains('/uploads/site/hero-custom.jpg', $urls);
        $this->assertContains(StockImages::hero(0), $urls);
        $this->assertTrue($library->isAllowedUrl(StockImages::hero(0)));
        $this->assertFalse($library->isAllowedUrl('https://example.com/x.jpg'));

        @unlink($uploadsDir . '/section-abc.webp');
        @rmdir($uploadsDir);
    }
}

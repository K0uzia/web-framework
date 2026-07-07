<?php

declare(strict_types=1);

namespace Tests;

use Capsule\MediaLibrary;
use Capsule\MediaRepository;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteRepository;
use Capsule\StockImages;
use PHPUnit\Framework\TestCase;

final class MediaLibraryTest extends TestCase
{
    public function testCollectsDatabaseUploadsStockAndSectionImages(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $mediaRepo = new MediaRepository($pdo);
        $mediaRepo->create('image', '/uploads/media/image-db.webp', 'image-db.webp', 'image/webp', 100);

        $uploadsDir = sys_get_temp_dir() . '/capsule-library-' . bin2hex(random_bytes(4));
        mkdir($uploadsDir);
        file_put_contents($uploadsDir . '/legacy.png', 'x');

        $pages->save(new Page(
            slug: 'home',
            title: 'Home',
            layout: 'default',
            description: '',
            sections: [[
                'id' => 'gallery-1',
                'type' => 'gallery',
                'variant' => 'grid',
                'visible' => true,
                'content' => ['items' => [['url' => '/uploads/site/hero-custom.jpg', 'title' => 'A']]],
                'style' => [],
            ]],
            meta: [],
            published: true,
            updatedAt: '',
        ));

        $library = new MediaLibrary($mediaRepo, $uploadsDir, '/uploads/site', $pages, $site);
        $urls = $library->availableImageUrls();

        $this->assertContains('/uploads/media/image-db.webp', $urls);
        $this->assertContains('/uploads/site/legacy.png', $urls);
        $this->assertContains('/uploads/site/hero-custom.jpg', $urls);
        $this->assertContains(StockImages::hero(0), $urls);
        $this->assertTrue($library->isAllowedUrl('/uploads/media/image-db.webp'));
        $this->assertFalse($library->isAllowedUrl('https://example.com/x.jpg'));

        @unlink($uploadsDir . '/legacy.png');
        @rmdir($uploadsDir);
    }
}

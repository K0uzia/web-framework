<?php

declare(strict_types=1);

namespace Tests;

use Capsule\MediaLibrary;
use Capsule\MediaRepository;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteRepository;
use PHPUnit\Framework\TestCase;

final class MediaLibraryTest extends TestCase
{
    public function testCollectsDatabaseUploadsAndSectionImages(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $mediaRepo = new MediaRepository($pdo);
        $mediaRepo->create('image', '/uploads/media/image-db.webp', 'image-db.webp', 'image/webp', 100);

        $publicRoot = sys_get_temp_dir() . '/capsule-public-' . bin2hex(random_bytes(4));
        $siteDir = $publicRoot . '/uploads/site';
        mkdir($siteDir, 0775, true);
        file_put_contents($siteDir . '/legacy.png', 'x');
        file_put_contents($siteDir . '/hero-custom.jpg', 'jpg');

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

        $library = new MediaLibrary($mediaRepo, $siteDir, '/uploads/site', $pages, $site, '', $publicRoot);
        $urls = $library->availableImageUrls();

        $this->assertContains('/uploads/media/image-db.webp', $urls);
        $this->assertContains('/uploads/site/legacy.png', $urls);
        $this->assertContains('/uploads/site/hero-custom.jpg', $urls);
        $this->assertTrue($library->isAllowedUrl('/uploads/media/image-db.webp'));
        $this->assertTrue($library->isAllowedUrl('/uploads/library/client.webp'));
        $this->assertFalse($library->isAllowedUrl('https://example.com/x.jpg'));
        $this->assertFalse($library->isAllowedUrl('/assets/stock/exemple.jpg'));
        $this->assertTrue($library->isAllowedUrl('/assets/sections/hero/_shared/saas-hero-1-16x9.png'));

        $library->syncDiscoveredRecords('image');
        $records = $mediaRepo->all('image', MediaRepository::OWNER_DEV);

        $this->assertCount(3, $records);
        $this->assertNotNull($mediaRepo->findByUrl('/uploads/site/hero-custom.jpg'));
        $this->assertNotNull($mediaRepo->findByUrl('/uploads/site/legacy.png'));
        $this->assertSame(MediaRepository::OWNER_DEV, $mediaRepo->findByUrl('/uploads/site/legacy.png')['owner'] ?? null);

        @unlink($siteDir . '/legacy.png');
        @unlink($siteDir . '/hero-custom.jpg');
        @rmdir($siteDir);
        @rmdir(dirname($siteDir));
        @rmdir($publicRoot);
    }

    public function testClientLibraryIsSeparateFromDevGallery(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');
        $mediaRepo = new MediaRepository($pdo);
        $mediaRepo->create('image', '/uploads/media/dev-only.webp', 'dev-only.webp', 'image/webp', 10);
        $mediaRepo->create(
            'image',
            '/uploads/library/client-only.webp',
            'client-only.webp',
            'image/webp',
            10,
            '',
            MediaRepository::OWNER_CLIENT,
        );

        $publicRoot = sys_get_temp_dir() . '/capsule-public-' . bin2hex(random_bytes(4));
        $clientDir = $publicRoot . '/uploads/library';
        mkdir($clientDir, 0775, true);
        file_put_contents($clientDir . '/disk-client.png', 'x');

        $library = new MediaLibrary(
            $mediaRepo,
            $publicRoot . '/uploads/site',
            '/uploads/site',
            null,
            null,
            $publicRoot . '/uploads/media',
            $publicRoot,
            $clientDir,
        );

        $devUrls = $library->availableImageUrls();
        $clientUrls = $library->availableClientImageUrls();

        $this->assertContains('/uploads/media/dev-only.webp', $devUrls);
        $this->assertNotContains('/uploads/library/client-only.webp', $devUrls);
        $this->assertContains('/uploads/library/client-only.webp', $clientUrls);
        $this->assertContains('/uploads/library/disk-client.png', $clientUrls);
        $this->assertNotContains('/uploads/media/dev-only.webp', $clientUrls);

        @unlink($clientDir . '/disk-client.png');
        @rmdir($clientDir);
        @rmdir(dirname($clientDir));
        @rmdir($publicRoot);
    }
}

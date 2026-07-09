<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\LibraryMediaUploader;
use App\Http\Dev\MediasController;
use App\Http\Dev\SectionFormRenderer;
use App\Http\Dev\SectionsController;
use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\MediaLibrary;
use Capsule\MediaRepository;
use Capsule\MediaUsageScanner;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SectionRegistry;
use Capsule\SiteRepository;
use PHPUnit\Framework\TestCase;
use App\Http\Dev\MediaUploader;

final class SectionsControllerTest extends TestCase
{
    private PageRepository $pages;
    private SectionsController $controller;
    private MediaRepository $mediaRepo;
    private string $uploadsDir;
    private string $libraryDir;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__, 3) . '/migrations/sqlite_init.sql') ?: '');
        $this->pages = new PageRepository($pdo);

        $root = dirname(__DIR__, 3);
        $ui = new DevDashboard($root . '/resources/dev', new ResponseFactory());
        $registry = new SectionRegistry($root . '/resources/sections/registry.yaml');
        $site = new SiteRepository($pdo);
        $this->mediaRepo = new MediaRepository($pdo);
        $this->uploadsDir = sys_get_temp_dir() . '/capsule-section-media-' . bin2hex(random_bytes(4));
        $this->libraryDir = sys_get_temp_dir() . '/capsule-library-media-' . bin2hex(random_bytes(4));
        mkdir($this->libraryDir);
        $uploader = new MediaUploader($this->uploadsDir);
        $libraryUploader = new LibraryMediaUploader($this->libraryDir);
        $library = new MediaLibrary($this->mediaRepo, $this->uploadsDir, '/uploads/site', $this->pages, $site);
        $forms = new SectionFormRenderer($registry, $this->pages, $library, $libraryUploader);

        $this->controller = new SectionsController($ui, $this->pages, $registry, $forms, $uploader, $libraryUploader, $library, $this->mediaRepo);

        $this->pages->save(new Page(
            slug: 'about',
            title: 'About',
            layout: 'default',
            description: '',
            sections: [[
                'id' => 'hero-1',
                'type' => 'hero',
                'variant' => 'hero3',
                'visible' => true,
                'content' => ['title' => 'Hello'],
                'style' => [],
            ]],
            meta: [],
            published: true,
            updatedAt: '',
        ));
    }

    protected function tearDown(): void
    {
        foreach ([$this->uploadsDir, $this->libraryDir] as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            foreach (glob($dir . '/*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($dir);
        }
    }

    public function testSelectSectionImageFromLibrary(): void
    {
        $this->mediaRepo->create('image', '/uploads/media/hero.webp', 'hero.webp', 'image/webp', 100);

        $response = $this->controller->selectMedia(
            $this->hxPost('/dev/pages/about/sections/hero-1/media/image_url/select', 'url=' . rawurlencode('/uploads/media/hero.webp')),
            'about',
            'hero-1',
            'image_url',
        );

        $this->assertSame(200, $response->getStatus());
        $this->assertStringContainsString('dev-section-media', (string) $response->getBody());

        $page = $this->pages->findBySlug('about', false);
        $this->assertSame('/uploads/media/hero.webp', $page->sections[0]['content']['image_url'] ?? '');
    }

    public function testAddSectionUsesFirstVariant(): void
    {
        $response = $this->controller->add($this->hxPost(
            '/dev/pages/about/sections',
            'type=hero',
        ), 'about');

        $page = $this->pages->findBySlug('about', false);
        $this->assertNotNull($page);
        $last = $page->sections[array_key_last($page->sections)];
        $this->assertSame('hero', $last['type']);
        $this->assertSame('hero3', $last['variant']);
        $this->assertStringContainsString('dev-section-card', (string) $response->getBody());
    }

    public function testAddHeroUsesRequestedVariant(): void
    {
        $response = $this->controller->add($this->hxPost(
            '/dev/pages/about/sections',
            'type=hero&variant=hero3',
        ), 'about');

        $page = $this->pages->findBySlug('about', false);
        $this->assertNotNull($page);
        $last = $page->sections[array_key_last($page->sections)];
        $this->assertSame('hero3', $last['variant']);
        $this->assertStringContainsString('dev-section-card', (string) $response->getBody());
    }

    public function testUpdateSectionContent(): void
    {
        $response = $this->controller->update($this->hxPost(
            '/dev/pages/about/sections/hero-1',
            'content_title=Updated',
        ), 'about', 'hero-1');

        $this->assertSame(200, $response->getStatus());
        $page = $this->pages->findBySlug('about', false);
        $this->assertSame('Updated', $page->sections[0]['content']['title'] ?? '');
    }

    public function testMoveAndDestroySections(): void
    {
        $this->controller->add($this->hxPost('/dev/pages/about/sections', 'type=hero'), 'about');
        $page = $this->pages->findBySlug('about', false);
        $this->assertCount(2, $page->sections);

        $secondId = $page->sections[1]['id'];
        $this->controller->move($this->hxPost('/dev/pages/about/sections/' . $secondId . '/move', 'direction=up'), 'about', (string) $secondId);
        $page = $this->pages->findBySlug('about', false);
        $this->assertSame('hero', $page->sections[0]['type']);

        $this->controller->destroy($this->hxPost('/dev/pages/about/sections/' . $secondId . '/delete', ''), 'about', (string) $secondId);
        $page = $this->pages->findBySlug('about', false);
        $this->assertCount(1, $page->sections);
    }

    public function testRestoreReinsertsDeletedSectionAtRequestedIndex(): void
    {
        $section = $this->pages->findBySlug('about', false)->sections[0];
        $this->controller->destroy($this->hxPost('/dev/pages/about/sections/hero-1/delete', ''), 'about', 'hero-1');

        $payload = 'section=' . rawurlencode((string) json_encode($section));
        $this->controller->restore($this->hxPost('/dev/pages/about/sections/restore', $payload . '&index=0'), 'about');

        $page = $this->pages->findBySlug('about', false);
        $this->assertCount(1, $page->sections);
        $this->assertSame('hero-1', $page->sections[0]['id']);
    }

    public function testReorderAppliesRequestedOrderAndKeepsUnknownIdsAtEnd(): void
    {
        $this->controller->add($this->hxPost('/dev/pages/about/sections', 'type=hero'), 'about');
        $this->controller->add($this->hxPost('/dev/pages/about/sections', 'type=hero'), 'about');
        $page = $this->pages->findBySlug('about', false);
        $ids = array_map(static fn ($s) => $s['id'], $page->sections);

        $this->controller->reorder($this->hxPost(
            '/dev/pages/about/sections/reorder',
            'order=' . rawurlencode(implode(',', array_reverse($ids))),
        ), 'about');

        $page = $this->pages->findBySlug('about', false);
        $this->assertSame(array_reverse($ids), array_map(static fn ($s) => $s['id'], $page->sections));
    }

    public function testReorderIgnoresEmptyOrder(): void
    {
        $before = $this->pages->findBySlug('about', false)->sections;
        $this->controller->reorder($this->hxPost('/dev/pages/about/sections/reorder', 'order='), 'about');
        $after = $this->pages->findBySlug('about', false)->sections;
        $this->assertSame($before, $after);
    }

    private function hxPost(string $path, string $body): Request
    {
        return new Request('POST', $path, [], ['HX-Request' => 'true'], [], [], 'http', null, null, $body);
    }
}

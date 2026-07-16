<?php

declare(strict_types=1);

namespace Tests\Http\Admin;

use App\Http\Admin\PageEditContentApplier;
use App\Http\Admin\PageEditFormRenderer;
use App\Http\Admin\PagesController;
use Capsule\AdminDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\MediaLibrary;
use Capsule\MediaRepository;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\Section\SectionFieldSchema;
use Capsule\SectionRegistry;
use Capsule\SiteRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PagesController::class)]
#[CoversClass(PageEditFormRenderer::class)]
#[CoversClass(PageEditContentApplier::class)]
final class PagesEditTest extends TestCase
{
    private SiteRepository $site;
    private PageRepository $pages;
    private MediaRepository $media;
    private PagesController $controller;
    private string $cssDir;
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 3);
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents($this->root . '/migrations/sqlite_init.sql') ?: '');

        $this->site = new SiteRepository($pdo);
        $this->pages = new PageRepository($pdo);
        $this->media = new MediaRepository($pdo);

        $this->pages->save(new Page(
            '',
            'Accueil',
            'default',
            '',
            [[
                'id' => 'hero-1',
                'type' => 'hero',
                'variant' => 'hero1',
                'visible' => true,
                'content' => [
                    'title' => 'Ancien titre',
                    'subtitle' => 'Ancienne description',
                    'badge' => 'Ne pas toucher',
                    'image_url' => '',
                ],
                'style' => [],
            ]],
            [],
            true,
            '',
        ));

        $this->site->setClientDashboard([
            'medias_enabled' => true,
            'pages' => [
                '' => [
                    'sections' => [
                        'hero-1' => ['fields' => ['title', 'subtitle', 'image_url']],
                    ],
                ],
            ],
        ]);

        $this->media->create('image', '/uploads/library/client-logo.png', 'client-logo.png', 'image/png', 100, 'Logo client');
        $this->media->create('image', '/assets/sections/hero/demo.png', 'demo.png', 'image/png', 50, 'Modèle');

        $registry = new SectionRegistry(
            $this->root . '/resources/sections/registry.yaml',
            $this->root . '/resources/sections/_shared/style-fields.yaml',
        );
        $schema = new SectionFieldSchema($registry);
        $mediaLibrary = new MediaLibrary($this->media, sys_get_temp_dir());

        $this->cssDir = sys_get_temp_dir() . '/capsule-admin-edit-' . bin2hex(random_bytes(4));
        mkdir($this->cssDir);
        $ui = new AdminDashboard(
            $this->root . '/resources/admin',
            new ResponseFactory(),
            $this->site,
            null,
            $this->cssDir,
        );

        $this->controller = new PagesController(
            $ui,
            $this->site,
            $this->pages,
            new PageEditFormRenderer($registry, $schema, $mediaLibrary, $this->media),
            new PageEditContentApplier($schema),
        );
    }

    protected function tearDown(): void
    {
        if (is_dir($this->cssDir)) {
            @rmdir($this->cssDir);
        }
    }

    public function testEditFormShowsOnlyAllowedFieldsAndClientMedias(): void
    {
        $body = (string) $this->controller->edit(new Request('GET', '/admin/pages/_', [], [], [], []), '_')->getBody();

        $this->assertStringContainsString('name="s_hero-1__content_title"', $body);
        $this->assertStringContainsString('name="s_hero-1__content_subtitle"', $body);
        $this->assertStringContainsString('name="s_hero-1__content_image_url"', $body);
        $this->assertStringContainsString('/uploads/library/client-logo.png', $body);
        $this->assertStringNotContainsString('/assets/sections/hero/demo.png', $body);
        $this->assertStringNotContainsString('name="s_hero-1__content_badge"', $body);
        $this->assertStringContainsString('action="/admin/pages/_"', $body);
        $this->assertStringContainsString('Enregistrer', $body);
    }

    public function testUpdatePersistsAllowedFieldsOnly(): void
    {
        $body = http_build_query([
            's_hero-1__content_title' => 'Nouveau titre',
            's_hero-1__content_subtitle' => 'Nouvelle description',
            's_hero-1__content_image_url' => '/uploads/library/client-logo.png',
            's_hero-1__content_badge' => 'Injection',
        ]);

        $response = $this->controller->update(
            new Request(
                'POST',
                '/admin/pages/_',
                [],
                ['Content-Type' => 'application/x-www-form-urlencoded'],
                [],
                [],
                'http',
                null,
                null,
                $body,
            ),
            '_',
        );

        $this->assertSame(302, $response->getStatus());
        $this->assertStringContainsString('/admin/pages/_', $response->getHeader('Location')[0] ?? '');

        $page = $this->pages->findBySlug('', false);
        $this->assertNotNull($page);
        $content = $page->sections[0]['content'] ?? [];
        $this->assertIsArray($content);
        $this->assertSame('Nouveau titre', $content['title'] ?? null);
        $this->assertSame('Nouvelle description', $content['subtitle'] ?? null);
        $this->assertSame('/uploads/library/client-logo.png', $content['image_url'] ?? null);
        $this->assertSame('Ne pas toucher', $content['badge'] ?? null);
    }

    public function testUpdateRejectsNonEditablePage(): void
    {
        $response = $this->controller->update(
            new Request('POST', '/admin/pages/contact', [], [], [], [], 'http', null, null, ''),
            'contact',
        );

        $this->assertSame(302, $response->getStatus());
        $this->assertStringContainsString('/admin/pages', $response->getHeader('Location')[0] ?? '');
    }
}

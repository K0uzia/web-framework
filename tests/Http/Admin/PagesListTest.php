<?php

declare(strict_types=1);

namespace Tests\Http\Admin;

use App\Http\Admin\PageEditContentApplier;
use App\Http\Admin\PageEditFormRenderer;
use App\Http\Admin\PagesController;
use App\Http\Admin\PagesListRenderer;
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

#[CoversClass(PagesListRenderer::class)]
final class PagesListTest extends TestCase
{
    private \PDO $pdo;
    private SiteRepository $site;
    private PageRepository $pages;
    private PagesController $controller;
    private string $cssDir;
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 3);
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->exec(file_get_contents($this->root . '/migrations/sqlite_init.sql') ?: '');

        $this->site = new SiteRepository($this->pdo);
        $this->pages = new PageRepository($this->pdo);

        $site = $this->site->getSite();
        $site['name'] = 'Studio Nord';
        $this->site->setSite($site);

        $registry = new SectionRegistry(
            $this->root . '/resources/sections/registry.yaml',
            $this->root . '/resources/sections/_shared/style-fields.yaml',
        );
        $schema = new SectionFieldSchema($registry);
        $media = new MediaRepository($this->pdo);
        $mediaLibrary = new MediaLibrary($media, sys_get_temp_dir());

        $this->cssDir = sys_get_temp_dir() . '/capsule-admin-list-' . bin2hex(random_bytes(4));
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
            new PageEditFormRenderer($registry, $schema, $mediaLibrary, $media, $this->pages),
            new PageEditContentApplier($schema),
            new PagesListRenderer(),
        );
    }

    protected function tearDown(): void
    {
        if (is_dir($this->cssDir)) {
            @rmdir($this->cssDir);
        }
    }

    public function testIndexShowsEmptyStateWhenNoEditablePages(): void
    {
        $this->site->setClientDashboard(['medias_enabled' => false, 'pages' => []]);

        $body = (string) $this->controller->index(new Request('GET', '/admin/pages', [], [], [], []))->getBody();

        $this->assertStringContainsString('Aucune page disponible', $body);
        $this->assertStringContainsString('Espace client', $body);
        $this->assertStringContainsString('admin-empty', $body);
        $this->assertStringNotContainsString('data-demo="1"', $body);
    }

    public function testIndexShowsRelativeUpdatedAtForEditablePage(): void
    {
        $this->pages->save(new Page(
            '',
            'Accueil',
            'default',
            '',
            [['id' => 'hero-1', 'type' => 'hero', 'variant' => 'hero1', 'visible' => true, 'content' => [], 'style' => []]],
            [],
            true,
            '',
        ));

        $updatedAt = gmdate('Y-m-d H:i:s', time() - 2 * 86400);
        $stmt = $this->pdo->prepare('UPDATE pages SET updated_at = :updated_at WHERE slug = :slug');
        $stmt->execute(['updated_at' => $updatedAt, 'slug' => '']);

        $this->site->setClientDashboard([
            'medias_enabled' => false,
            'pages' => [
                '' => [
                    'sections' => [
                        'hero-1' => ['fields' => ['title']],
                    ],
                ],
            ],
        ]);

        $body = (string) $this->controller->index(new Request('GET', '/admin/pages', [], [], [], []))->getBody();

        $this->assertStringContainsString('il y a 2 jours', $body);
        $this->assertStringContainsString('Studio Nord', $body);
        $this->assertStringContainsString('admin-page-card', $body);
        $this->assertStringContainsString('/admin/pages/_', $body);
        $this->assertStringContainsString('admin-pagination', $body);
    }
}

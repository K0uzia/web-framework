<?php

declare(strict_types=1);

namespace Tests\Http\Admin;

use App\Http\Admin\SiteContactSync;
use App\Http\Admin\SiteController;
use App\Http\Dev\MediaUploader;
use Capsule\AdminDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\MediaLibrary;
use Capsule\MediaRepository;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SiteController::class)]
#[CoversClass(SiteContactSync::class)]
final class SiteEditTest extends TestCase
{
    private SiteRepository $site;
    private PageRepository $pages;
    private SiteController $controller;
    private string $cssDir;
    private string $uploadDir;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 3);
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents($root . '/migrations/sqlite_init.sql') ?: '');

        $this->site = new SiteRepository($pdo);
        $this->pages = new PageRepository($pdo);
        $this->cssDir = sys_get_temp_dir() . '/capsule-site-css-' . bin2hex(random_bytes(4));
        $this->uploadDir = sys_get_temp_dir() . '/capsule-site-up-' . bin2hex(random_bytes(4));
        mkdir($this->cssDir);
        mkdir($this->uploadDir);

        $this->pages->save(new Page(
            'contact',
            'Contact',
            'default',
            '',
            [[
                'id' => 'contact-1',
                'type' => 'contact',
                'variant' => 'contact2',
                'visible' => true,
                'content' => [
                    'email' => 'ancien@exemple.fr',
                    'phone' => '01 00 00 00 00',
                    'office_address' => 'Ancienne adresse',
                ],
                'style' => [],
            ]],
            [],
            true,
            '',
        ));

        $this->site->setClientDashboard([
            'medias_enabled' => false,
            'site_enabled' => true,
            'pages' => [
                'contact' => [
                    'sections' => [
                        'contact-1' => ['fields' => ['email', 'phone']],
                    ],
                ],
            ],
        ]);

        $ui = new AdminDashboard(
            $root . '/resources/admin',
            new ResponseFactory(),
            $this->site,
            null,
            $this->cssDir,
        );
        $media = new MediaRepository($pdo);
        $library = new MediaLibrary($media, $this->uploadDir);
        $uploader = new MediaUploader($this->uploadDir);

        $this->controller = new SiteController(
            $ui,
            $this->site,
            $library,
            $media,
            $uploader,
            new SiteContactSync($this->pages),
        );
    }

    protected function tearDown(): void
    {
        foreach ([$this->cssDir, $this->uploadDir] as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            foreach (glob($dir . '/*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($dir);
        }
    }

    public function testEditShowsIdentityAndContactSeededFromSection(): void
    {
        $response = $this->controller->edit(new Request('GET', '/admin/site', [], [], [], []));
        $body = (string) $response->getBody();

        $this->assertSame(200, $response->getStatus());
        $this->assertStringContainsString('name="site_name"', $body);
        $this->assertStringContainsString('name="contact_email"', $body);
        $this->assertStringContainsString('value="ancien@exemple.fr"', $body);
        $this->assertStringContainsString('value="01 00 00 00 00"', $body);
    }

    public function testUpdatePersistsSiteAndPropagatesContactSections(): void
    {
        $body = http_build_query([
            'site_name' => 'Studio Nord',
            'site_tagline' => 'Design et contenu',
            'logo_url' => '',
            'favicon_url' => '',
            'contact_email' => 'hello@studio.fr',
            'contact_phone' => '01 23 45 67 89',
            'contact_address' => '10 rue du Port, Nantes',
        ]);

        $response = $this->controller->update(new Request(
            'POST',
            '/admin/site',
            [],
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            [],
            [],
            'http',
            null,
            null,
            $body,
        ));

        $this->assertSame(302, $response->getStatus());

        $site = $this->site->getSite();
        $this->assertSame('Studio Nord', $site['name']);
        $this->assertSame('Design et contenu', $site['tagline']);
        $this->assertSame('hello@studio.fr', $site['contact_email']);
        $this->assertSame('01 23 45 67 89', $site['contact_phone']);
        $this->assertSame('10 rue du Port, Nantes', $site['contact_address']);

        $page = $this->pages->findBySlug('contact', false);
        $this->assertNotNull($page);
        $content = $page->sections[0]['content'] ?? [];
        $this->assertSame('hello@studio.fr', $content['email'] ?? null);
        $this->assertSame('01 23 45 67 89', $content['phone'] ?? null);
        $this->assertSame('10 rue du Port, Nantes', $content['office_address'] ?? null);
    }

    public function testEditDeniedWhenSiteDisabled(): void
    {
        $this->site->setClientDashboard([
            'medias_enabled' => false,
            'site_enabled' => false,
            'pages' => [],
        ]);

        $response = $this->controller->edit(new Request('GET', '/admin/site', [], [], [], []));
        $this->assertSame(302, $response->getStatus());
    }
}

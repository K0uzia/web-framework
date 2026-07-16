<?php

declare(strict_types=1);

namespace Tests\Http\Admin;

use App\Http\Admin\AuthController;
use App\Http\Admin\HomeController;
use App\Http\Admin\PageEditContentApplier;
use App\Http\Admin\PageEditFormRenderer;
use App\Http\Admin\PagesController;
use Capsule\AdminDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\MediaLibrary;
use Capsule\MediaRepository;
use Capsule\Middleware\ClientAuth;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\Section\SectionFieldSchema;
use Capsule\SectionRegistry;
use Capsule\SiteRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthController::class)]
#[CoversClass(PagesController::class)]
#[CoversClass(HomeController::class)]
#[CoversClass(ClientAuth::class)]
final class AdminAuthAndLayoutTest extends TestCase
{
    private SiteRepository $site;
    private PageRepository $pages;
    private AdminDashboard $ui;
    private AuthController $auth;
    private PagesController $pagesController;
    private string $cssDir;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__, 3) . '/migrations/sqlite_init.sql') ?: '');

        $this->site = new SiteRepository($pdo);
        $this->pages = new PageRepository($pdo);
        $site = $this->site->getSite();
        $site['name'] = 'Studio Nord';
        $site['logo_url'] = '/uploads/site/logo.png';
        $this->site->setSite($site);

        $theme = $this->site->getTheme();
        $theme['colors']['primary'] = '#0d9488';
        $this->site->setTheme($theme);

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
        $this->site->setClientDashboard([
            'medias_enabled' => true,
            'pages' => [
                '' => [
                    'sections' => [
                        'hero-1' => ['fields' => ['title', 'subtitle']],
                    ],
                ],
            ],
        ]);

        $this->cssDir = sys_get_temp_dir() . '/capsule-admin-css-' . bin2hex(random_bytes(4));
        mkdir($this->cssDir);
        $this->ui = new AdminDashboard(
            dirname(__DIR__, 3) . '/resources/admin',
            new ResponseFactory(),
            $this->site,
            null,
            $this->cssDir,
        );
        $this->auth = new AuthController($this->ui, new ResponseFactory(), 'client-secret');

        $root = dirname(__DIR__, 3);
        $registry = new SectionRegistry(
            $root . '/resources/sections/registry.yaml',
            $root . '/resources/sections/_shared/style-fields.yaml',
        );
        $schema = new SectionFieldSchema($registry);
        $media = new MediaRepository($pdo);
        $mediaLibrary = new MediaLibrary($media, sys_get_temp_dir());
        $this->pagesController = new PagesController(
            $this->ui,
            $this->site,
            $this->pages,
            new PageEditFormRenderer($registry, $schema, $mediaLibrary, $media),
            new PageEditContentApplier($schema),
        );
    }

    protected function tearDown(): void
    {
        if (is_dir($this->cssDir)) {
            @rmdir($this->cssDir);
        }
    }

    public function testLoginFormShowsSiteIdentityAndThemeVars(): void
    {
        $body = (string) $this->auth->loginForm(new Request('GET', '/admin', [], [], [], []))->getBody();

        $this->assertStringContainsString('Studio Nord', $body);
        $this->assertStringContainsString('/uploads/site/logo.png', $body);
        $this->assertStringContainsString('--color-primary: #0d9488', $body);
        $this->assertStringContainsString('/assets/css/admin.css', $body);
        $this->assertStringNotContainsString('/assets/css/dev.css', $body);
        $this->assertStringContainsString('action="/admin/login"', $body);
    }

    public function testLoginSetsClientCookie(): void
    {
        $response = $this->auth->login(new Request(
            'POST',
            '/admin/login',
            [],
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            [],
            [],
            rawBody: 'password=client-secret',
        ));

        $this->assertSame(302, $response->getStatus());
        $this->assertStringContainsString('/admin/pages', $response->getHeader('Location')[0] ?? '');
        $cookies = $response->getHeader('Set-Cookie');
        $this->assertNotEmpty($cookies);
        $this->assertStringContainsString('capsule_client=1', $cookies[0]);
    }

    public function testWrongPasswordRedirectsWithFlash(): void
    {
        $response = $this->auth->login(new Request(
            'POST',
            '/admin/login',
            [],
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            [],
            [],
            rawBody: 'password=wrong',
        ));

        $this->assertSame(302, $response->getStatus());
        $this->assertStringContainsString('/admin', $response->getHeader('Location')[0] ?? '');
    }

    public function testPagesIndexListsEditablePagesAndMediasNav(): void
    {
        $body = (string) $this->pagesController->index(new Request('GET', '/admin/pages', [], [], [], []))->getBody();

        $this->assertStringContainsString('Pages', $body);
        $this->assertStringContainsString('Studio Nord', $body);
        $this->assertStringContainsString('admin-sidebar', $body);
        $this->assertStringContainsString('Accueil', $body);
        $this->assertStringContainsString('/admin/pages/_', $body);
        $this->assertStringContainsString('Modifier', $body);
        $this->assertStringContainsString('/admin/medias', $body);
        $this->assertDoesNotMatchRegularExpression('/class="[^"]*\bhidden\b[^"]*"[^>]*>\s*<i[^>]*fa-photo-film/', $body);
    }

    public function testHomeRedirectsToPages(): void
    {
        $home = new HomeController($this->ui);
        $response = $home->index(new Request('GET', '/admin/home', [], [], [], []));
        $this->assertSame(302, $response->getStatus());
        $this->assertStringContainsString('/admin/pages', $response->getHeader('Location')[0] ?? '');
    }

    public function testClientAuthRedirectsWhenUnauthenticated(): void
    {
        $middleware = new ClientAuth(new ResponseFactory(), 'secret', false);
        $response = $middleware->process(
            new Request('GET', '/admin/pages', [], [], [], []),
            new class implements \Capsule\Middleware\HandlerInterface {
                public function handle(Request $request): \Capsule\Http\Message\Response
                {
                    return (new ResponseFactory())->text('ok');
                }
            },
        );

        $this->assertSame(302, $response->getStatus());
        $this->assertSame('/admin', $response->getHeader('Location')[0] ?? '');
    }
}

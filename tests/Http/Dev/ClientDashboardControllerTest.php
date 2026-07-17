<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\ClientDashboardController;
use Capsule\ClientDashboardConfig;
use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\Section\SectionFieldSchema;
use Capsule\SectionRegistry;
use Capsule\SiteRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClientDashboardController::class)]
final class ClientDashboardControllerTest extends TestCase
{
    private SiteRepository $site;
    private PageRepository $pages;
    private ClientDashboardController $controller;
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 3);
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents($this->root . '/migrations/sqlite_init.sql') ?: '');

        $this->site = new SiteRepository($pdo);
        $this->pages = new PageRepository($pdo);
        $registry = new SectionRegistry(
            $this->root . '/resources/sections/registry.yaml',
            $this->root . '/resources/sections/_shared/style-fields.yaml',
        );
        $fieldSchema = new SectionFieldSchema($registry);
        $ui = new DevDashboard($this->root . '/resources/dev', new ResponseFactory(), $this->site);
        $this->controller = new ClientDashboardController(
            $ui,
            $this->site,
            $this->pages,
            $registry,
            $fieldSchema,
        );

        $this->pages->save(new Page(
            '',
            'Accueil',
            'default',
            '',
            [
                [
                    'id' => 'hero-abc123',
                    'type' => 'hero',
                    'variant' => 'hero1',
                    'visible' => true,
                    'content' => ['title' => 'Hello'],
                    'style' => [],
                ],
            ],
            [],
            true,
            '',
        ));
        $this->pages->save(new Page(
            'contact',
            'Contact',
            'default',
            '',
            [
                [
                    'id' => 'cta-def456',
                    'type' => 'cta',
                    'variant' => 'cta4',
                    'visible' => true,
                    'content' => ['title' => 'Contactez-nous'],
                    'style' => [],
                ],
            ],
            [],
            true,
            '',
        ));
    }

    public function testEditRendersTreeWithPagesAndClientEditableFields(): void
    {
        $body = (string) $this->controller->edit(new Request('GET', '/dev/client-dashboard', [], [], [], []))->getBody();

        $this->assertStringContainsString('Configuration du dashboard client', $body);
        $this->assertStringContainsString('Espace client', $body);
        $this->assertStringContainsString('Prêt', $body);
        $this->assertStringContainsString('Accueil', $body);
        $this->assertStringContainsString('Contact', $body);
        $this->assertStringContainsString('name="cd::hero-abc123:title"', $body);
        $this->assertStringContainsString('dev-perm-chevron', $body);
        $this->assertStringContainsString('cd_medias', $body);
        $this->assertStringContainsString('cd_site', $body);
        $this->assertStringContainsString('name="cd_site" value="1" checked', $body);
        $this->assertStringContainsString('dev-cd__stats', $body);
        $this->assertStringContainsString('Prévisualiser /admin', $body);
        $this->assertStringContainsString('dev-nav__link is-active', $body);
    }

    public function testUpdatePersistsAllowedFieldsOnly(): void
    {
        $titleKey = ClientDashboardConfig::formFieldKey('', 'hero-abc123', 'title');
        $subtitleKey = ClientDashboardConfig::formFieldKey('', 'hero-abc123', 'subtitle');
        $forbiddenKey = ClientDashboardConfig::formFieldKey('', 'hero-abc123', 'not_a_real_field');
        $contactKey = ClientDashboardConfig::formFieldKey('contact', 'cta-def456', 'title');

        $body = http_build_query([
            $titleKey => '1',
            $subtitleKey => '1',
            $forbiddenKey => '1',
            $contactKey => '1',
        ]);

        $response = $this->controller->update($this->post('/dev/client-dashboard', $body));
        $this->assertSame(302, $response->getStatus());

        $config = $this->site->getClientDashboard();
        $this->assertTrue($this->site->isClientPageEditable(''));
        $this->assertTrue($this->site->isClientPageEditable('contact'));
        $this->assertSame(['subtitle', 'title'], $this->site->clientAllowedFields('', 'hero-abc123'));
        $this->assertContains('title', $config['pages']['contact']['sections']['cta-def456']['fields']);
        $this->assertNotContains('not_a_real_field', $config['pages']['']['sections']['hero-abc123']['fields']);
    }

    public function testUpdateHxReturnsPartial(): void
    {
        $titleKey = ClientDashboardConfig::formFieldKey('', 'hero-abc123', 'title');
        $response = $this->controller->update($this->hxPost(
            '/dev/client-dashboard',
            http_build_query([$titleKey => '1']),
        ));

        $this->assertSame(200, $response->getStatus());
        $this->assertStringContainsString('Permissions enregistrées', (string) $response->getBody());
        $this->assertSame(['title'], $this->site->clientAllowedFields('', 'hero-abc123'));
    }

    public function testEmptyPostClearsPermissions(): void
    {
        $this->site->setClientDashboard([
            'pages' => [
                '' => [
                    'sections' => [
                        'hero-abc123' => ['fields' => ['title']],
                    ],
                ],
            ],
        ]);

        $this->controller->update($this->post('/dev/client-dashboard', ''));

        $this->assertSame(
            ['medias_enabled' => false, 'site_enabled' => false, 'pages' => []],
            $this->site->getClientDashboard(),
        );
        $this->assertFalse($this->site->isClientPageEditable(''));
    }

    private function hxPost(string $path, string $body): Request
    {
        return new Request(
            'POST',
            $path,
            [],
            ['HX-Request' => 'true', 'Content-Type' => 'application/x-www-form-urlencoded'],
            [],
            [],
            rawBody: $body,
        );
    }

    private function post(string $path, string $body): Request
    {
        return new Request(
            'POST',
            $path,
            [],
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            [],
            [],
            rawBody: $body,
        );
    }
}

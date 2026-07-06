<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\MediaUploader;
use App\Http\Dev\SiteController;
use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteRepository;
use PHPUnit\Framework\TestCase;

final class SiteControllerTest extends TestCase
{
    private PageRepository $pages;
    private SiteRepository $site;
    private SiteController $controller;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__, 3) . '/migrations/sqlite_init.sql') ?: '');

        $this->pages = new PageRepository($pdo);
        $this->site = new SiteRepository($pdo);
        $ui = new DevDashboard(dirname(__DIR__, 3) . '/resources/dev', new ResponseFactory());
        $media = new MediaUploader(sys_get_temp_dir() . '/capsule-test-uploads');
        $this->controller = new SiteController($ui, $this->site, $this->pages, $media);

        $this->pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));
        $this->pages->save(new Page('about', 'About', 'default', '', [], [], true, ''));
    }

    public function testIdentityUpdateDoesNotForceCustomNav(): void
    {
        $this->controller->update($this->post('/dev/site', 'site_name=Demo+Site'));

        $saved = $this->site->getSite();
        $this->assertSame('Demo Site', $saved['name']);
        $this->assertSame('auto', $saved['nav_mode']);
    }

    public function testUpdatePersistsFaviconAndOgImage(): void
    {
        $this->controller->update($this->post(
            '/dev/site',
            'site_name=Demo&favicon_url=%2Ffavicon.svg&og_image_url=https%3A%2F%2Fexample.com%2Fog.jpg',
        ));

        $saved = $this->site->getSite();
        $this->assertSame('/favicon.svg', $saved['favicon_url']);
        $this->assertSame('https://example.com/og.jpg', $saved['og_image_url']);
    }

    public function testAddCustomLinkAndReorder(): void
    {
        $this->controller->addNav($this->hxPost(
            '/dev/site/nav/add',
            'nav_type=link&nav_label=GitHub&nav_target=https%3A%2F%2Fgithub.com',
        ));

        $site = $this->site->getSite();
        $this->assertSame('custom', $site['nav_mode']);
        $items = $site['nav_items'];
        $this->assertNotEmpty($items);
        $last = $items[array_key_last($items)];
        $this->assertSame('link', $last['type']);
        $this->assertTrue($last['visible']);

        $firstId = (string) $items[0]['id'];
        $this->controller->moveNav($this->hxPost(
            '/dev/site/nav/' . rawurlencode($firstId) . '/move',
            'direction=down',
        ), $firstId);

        $reordered = $this->site->getSite()['nav_items'];
        $this->assertNotSame($firstId, $reordered[0]['id'] ?? null);
    }

    public function testDeleteNavEntry(): void
    {
        $this->controller->addNav($this->hxPost(
            '/dev/site/nav/add',
            'nav_type=button&nav_label=Contact&nav_target=%2Fcontact',
        ));

        $id = (string) $this->site->getSite()['nav_items'][array_key_last($this->site->getSite()['nav_items'])]['id'];
        $countBefore = count($this->site->getSite()['nav_items']);

        $response = $this->controller->deleteNav($this->hxPost(
            '/dev/site/nav/' . rawurlencode($id) . '/delete',
            '',
        ), $id);

        $this->assertCount($countBefore - 1, $this->site->getSite()['nav_items']);
        $this->assertStringContainsString('nav-form', (string) $response->getBody());
    }

    public function testSyncNavPreservesCustomLinks(): void
    {
        $this->controller->addNav($this->hxPost(
            '/dev/site/nav/add',
            'nav_type=link&nav_label=Docs&nav_target=https%3A%2F%2Fdocs.example',
        ));

        $this->controller->syncNav($this->hxPost('/dev/site/nav/sync', ''));

        $items = $this->site->getSite()['nav_items'];
        $types = array_column($items, 'type');
        $this->assertContains('page', $types);
        $this->assertContains('link', $types);
    }

    public function testUpdateNavPreservesVisibleWhenCheckboxMissing(): void
    {
        $this->controller->addNav($this->hxPost(
            '/dev/site/nav/add',
            'nav_type=link&nav_label=Blog&nav_target=%2Fblog',
        ));

        $site = $this->site->getSite();
        $id = (string) $site['nav_items'][array_key_last($site['nav_items'])]['id'];

        $this->controller->updateNav($this->hxPost(
            '/dev/site/nav',
            'update_nav=1&nav_item_' . $id . '_label=Blog+OK',
        ));

        $updated = $this->site->getSite()['nav_items'];
        $entry = array_values(array_filter($updated, static fn (array $i): bool => ($i['id'] ?? '') === $id))[0];
        $this->assertSame('Blog OK', $entry['label']);
        $this->assertTrue($entry['visible']);
    }

    public function testUpdateNavCanChangeTargetFromPageToExternalLink(): void
    {
        $this->controller->syncNav($this->hxPost('/dev/site/nav/sync', ''));
        $site = $this->site->getSite();
        $id = (string) $site['nav_items'][0]['id'];

        $this->controller->updateNav($this->hxPost(
            '/dev/site/nav',
            'update_nav=1&nav_item_' . $id . '_type=link&nav_item_' . $id . '_target=https%3A%2F%2Fexample.com',
        ));

        $updated = $this->site->getSite()['nav_items'];
        $entry = array_values(array_filter($updated, static fn (array $i): bool => ($i['id'] ?? '') === $id))[0];
        $this->assertSame('link', $entry['type']);
        $this->assertSame('https://example.com', $entry['href']);
    }

    public function testUpdateNavCanChangeTargetFromLinkToPage(): void
    {
        $this->controller->addNav($this->hxPost(
            '/dev/site/nav/add',
            'nav_type=link&nav_label=Temp&nav_target=https%3A%2F%2Fexample.com',
        ));
        $site = $this->site->getSite();
        $id = (string) $site['nav_items'][array_key_last($site['nav_items'])]['id'];

        $this->controller->updateNav($this->hxPost(
            '/dev/site/nav',
            'update_nav=1&nav_item_' . $id . '_type=page&nav_item_' . $id . '_target=%2Fabout',
        ));

        $updated = $this->site->getSite()['nav_items'];
        $entry = array_values(array_filter($updated, static fn (array $i): bool => ($i['id'] ?? '') === $id))[0];
        $this->assertSame('page', $entry['type']);
        $this->assertSame('about', $entry['slug']);
    }

    public function testUpdateNavCanChangeTypeFromPageToButton(): void
    {
        $this->controller->syncNav($this->hxPost('/dev/site/nav/sync', ''));
        $site = $this->site->getSite();
        $id = (string) $site['nav_items'][0]['id'];

        $this->controller->updateNav($this->hxPost(
            '/dev/site/nav',
            'update_nav=1&nav_item_' . $id . '_type=button',
        ));

        $updated = $this->site->getSite()['nav_items'];
        $entry = array_values(array_filter($updated, static fn (array $i): bool => ($i['id'] ?? '') === $id))[0];
        $this->assertSame('button', $entry['type']);
        $this->assertSame('/', $entry['href']);
        $this->assertSame('', $entry['slug']);
    }

    public function testReorderNavAppliesRequestedOrder(): void
    {
        $this->controller->addNav($this->hxPost(
            '/dev/site/nav/add',
            'nav_type=link&nav_label=GitHub&nav_target=https%3A%2F%2Fgithub.com',
        ));
        $this->controller->addNav($this->hxPost(
            '/dev/site/nav/add',
            'nav_type=link&nav_label=Docs&nav_target=https%3A%2F%2Fdocs.example',
        ));

        $items = $this->site->getSite()['nav_items'];
        $ids = array_column($items, 'id');
        $reversed = array_reverse($ids);

        $this->controller->reorderNav($this->hxPost(
            '/dev/site/nav/reorder',
            'order=' . implode(',', $reversed),
        ));

        $reordered = array_column($this->site->getSite()['nav_items'], 'id');
        $this->assertSame($reversed, $reordered);
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

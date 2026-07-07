<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\MediaUploader;
use App\Http\Dev\PagesController;
use App\Http\Dev\SectionFormRenderer;
use App\Http\Dev\SlugCodec;
use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\LayoutRegistry;
use Capsule\MediaLibrary;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SectionRegistry;
use Capsule\SiteRepository;
use PHPUnit\Framework\TestCase;

final class PagesControllerTest extends TestCase
{
    private PageRepository $pages;
    private SiteRepository $site;
    private PagesController $controller;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__, 3) . '/migrations/sqlite_init.sql') ?: '');
        $this->pages = new PageRepository($pdo);
        $this->site = new SiteRepository($pdo);

        $root = dirname(__DIR__, 3);
        $ui = new DevDashboard($root . '/resources/dev', new ResponseFactory());
        $registry = new SectionRegistry($root . '/resources/sections/registry.yaml');
        $library = new MediaLibrary(sys_get_temp_dir(), '/uploads/site', $this->pages, $this->site);
        $uploader = new MediaUploader(sys_get_temp_dir());
        $forms = new SectionFormRenderer($registry, $this->pages, $library, $uploader);
        $layouts = new LayoutRegistry($root . '/resources/layouts');

        $this->controller = new PagesController($ui, $this->pages, $this->site, $registry, $forms, $layouts);

        $this->pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));
        $this->pages->save(new Page('about', 'About', 'default', '', [[
            'id' => 'hero-1',
            'type' => 'hero',
            'variant' => 'centered',
            'visible' => true,
            'content' => ['title' => 'Hello'],
            'style' => [],
        ]], [], true, ''));
    }

    public function testStoreCreatesUnpublishedDraftByDefault(): void
    {
        $response = $this->controller->store($this->post('title=Nouvelle+page&slug=nouvelle-page&layout=default'));

        $page = $this->pages->findBySlug('nouvelle-page', false);
        $this->assertNotNull($page);
        $this->assertFalse($page->published);
        $this->assertSame(302, $response->getStatus());
        $this->assertSame([], $page->sections);
    }

    public function testStoreAppliesLandingTemplate(): void
    {
        $this->controller->store($this->post(
            'title=Landing&slug=landing-test&layout=default&page_template=landing-02',
        ));

        $page = $this->pages->findBySlug('landing-test', false);
        $this->assertNotNull($page);
        $this->assertGreaterThanOrEqual(4, count($page->sections));
        $this->assertSame('hero', $page->sections[0]['type'] ?? null);
        $this->assertTrue($page->published);
    }

    public function testStorePublishesAboutTemplateWithSuggestedSlug(): void
    {
        $this->controller->store($this->post(
            'title=&slug=&layout=default&page_template=about-1',
        ));

        $page = $this->pages->findBySlug('about-2', false);
        $this->assertNotNull($page);
        $this->assertTrue($page->published);
        $this->assertSame('À propos', $page->title);
        $this->assertGreaterThanOrEqual(3, count($page->sections));
    }

    public function testStoreRejectsEmptyTitleForAjax(): void
    {
        $response = $this->controller->store($this->hxPost('title=&slug=test'));

        $this->assertSame(422, $response->getStatus());
        $this->assertNull($this->pages->findBySlug('test', false));
    }

    public function testStoreRejectsNumericOnlySlug(): void
    {
        $response = $this->controller->store($this->hxPost('title=Test&slug=1'));

        $this->assertSame(422, $response->getStatus());
        $this->assertNull($this->pages->findBySlug('1', false));
    }

    public function testCreateFormRedirectsToPagesModal(): void
    {
        $response = $this->controller->createForm($this->getRequest());

        $this->assertSame(302, $response->getStatus());
        $this->assertStringContainsString('/dev/pages#new', $response->getHeaderLine('Location'));
    }

    public function testUpdatePersistsShowHeaderAndShowFooterOverrides(): void
    {
        $this->controller->update(
            $this->post('title=About&layout=default&show_header=hide&show_footer=show'),
            SlugCodec::encode('about'),
        );

        $page = $this->pages->findBySlug('about', false);
        $this->assertNotNull($page);
        $this->assertSame('hide', $page->meta['show_header']);
        $this->assertSame('show', $page->meta['show_footer']);
    }

    public function testUpdateNormalizesInvalidVisibilityValueToDefault(): void
    {
        $this->controller->update(
            $this->post('title=About&layout=default&show_header=not-a-real-value'),
            SlugCodec::encode('about'),
        );

        $page = $this->pages->findBySlug('about', false);
        $this->assertNotNull($page);
        $this->assertSame('default', $page->meta['show_header']);
    }

    public function testDuplicateCreatesDraftCopyWithUniqueSlug(): void
    {
        $response = $this->controller->duplicate($this->post(), SlugCodec::encode('about'));

        $this->assertSame(302, $response->getStatus());
        $copy = $this->pages->findBySlug('about-copie', false);
        $this->assertNotNull($copy);
        $this->assertFalse($copy->published);
        $this->assertSame('About (copie)', $copy->title);
        $this->assertCount(1, $copy->sections);

        $original = $this->pages->findBySlug('about', false);
        $this->assertNotNull($original);
        $this->assertTrue($original->published);
    }

    public function testDuplicateTwiceGeneratesIncrementingSlug(): void
    {
        $this->controller->duplicate($this->post(), SlugCodec::encode('about'));
        $this->controller->duplicate($this->post(), SlugCodec::encode('about'));

        $this->assertNotNull($this->pages->findBySlug('about-copie', false));
        $this->assertNotNull($this->pages->findBySlug('about-copie-2', false));
    }

    public function testRenameUpdatesSlugAndRemovesOldEntry(): void
    {
        $response = $this->controller->rename(
            $this->post('new_slug=a-propos'),
            SlugCodec::encode('about'),
        );

        $this->assertSame(302, $response->getStatus());
        $this->assertNull($this->pages->findBySlug('about', false));
        $renamed = $this->pages->findBySlug('a-propos', false);
        $this->assertNotNull($renamed);
        $this->assertSame('About', $renamed->title);
    }

    public function testRenameRejectsInvalidSlug(): void
    {
        $this->controller->rename(
            $this->post('new_slug=Not Valid!'),
            SlugCodec::encode('about'),
        );

        $this->assertNotNull($this->pages->findBySlug('about', false));
        $this->assertNull($this->pages->findBySlug('Not Valid!', false));
    }

    public function testRenameRejectsSlugAlreadyTaken(): void
    {
        $this->pages->save(new Page('contact', 'Contact', 'default', '', [], [], true, ''));

        $this->controller->rename(
            $this->post('new_slug=contact'),
            SlugCodec::encode('about'),
        );

        $this->assertNotNull($this->pages->findBySlug('about', false));
        $contact = $this->pages->findBySlug('contact', false);
        $this->assertNotNull($contact);
        $this->assertSame('Contact', $contact->title);
    }

    public function testHomePageCannotBeRenamed(): void
    {
        $response = $this->controller->edit($this->getRequest(), SlugCodec::encode(''));
        $this->assertStringContainsString('adresse racine', (string) $response->getBody());
    }

    public function testDestroyDeletesPage(): void
    {
        $this->controller->destroy($this->hxPost(), SlugCodec::encode('about'));

        $this->assertNull($this->pages->findBySlug('about', false));
    }

    public function testDestroyBlocksHomePage(): void
    {
        $response = $this->controller->destroy($this->hxPost(), SlugCodec::encode(''));

        $this->assertSame(422, $response->getStatus());
        $this->assertNotNull($this->pages->findBySlug('', false));
    }

    public function testSetHomeSwapsSlugs(): void
    {
        $this->controller->setHome($this->hxPost(), SlugCodec::encode('about'));

        $home = $this->pages->findBySlug('', false);
        $formerHome = $this->pages->findBySlug('about', false);
        $this->assertNotNull($home);
        $this->assertSame('About', $home->title);
        $this->assertNotNull($formerHome);
        $this->assertSame('Home', $formerHome->title);
    }

    private function hxPost(string $body = ''): Request
    {
        return new Request(
            'POST',
            '/dev/pages',
            [],
            ['HX-Request' => 'true', 'Content-Type' => 'application/x-www-form-urlencoded'],
            [],
            [],
            rawBody: $body,
        );
    }

    private function post(string $body = ''): Request
    {
        return new Request(
            'POST',
            '/dev/pages',
            [],
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            [],
            [],
            rawBody: $body,
        );
    }

    private function getRequest(): Request
    {
        return new Request('GET', '/dev/pages', [], [], [], []);
    }
}

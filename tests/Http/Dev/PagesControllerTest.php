<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\PagesController;
use App\Http\Dev\SectionFormRenderer;
use App\Http\Dev\SlugCodec;
use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\LayoutRegistry;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SectionRegistry;
use PHPUnit\Framework\TestCase;

final class PagesControllerTest extends TestCase
{
    private PageRepository $pages;
    private PagesController $controller;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__, 3) . '/migrations/sqlite_init.sql') ?: '');
        $this->pages = new PageRepository($pdo);

        $root = dirname(__DIR__, 3);
        $ui = new DevDashboard($root . '/resources/dev', new ResponseFactory());
        $registry = new SectionRegistry($root . '/resources/sections/registry.yaml');
        $forms = new SectionFormRenderer($registry, $this->pages);
        $layouts = new LayoutRegistry($root . '/resources/layouts');

        $this->controller = new PagesController($ui, $this->pages, $registry, $forms, $layouts);

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
        $this->controller->store($this->post('title=Nouvelle+page&slug=nouvelle-page&layout=default'));

        $page = $this->pages->findBySlug('nouvelle-page', false);
        $this->assertNotNull($page);
        $this->assertFalse($page->published);
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

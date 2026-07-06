<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\SectionFormRenderer;
use App\Http\Dev\SectionsController;
use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SectionRegistry;
use PHPUnit\Framework\TestCase;

final class SectionsControllerTest extends TestCase
{
    private PageRepository $pages;
    private SectionsController $controller;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__, 3) . '/migrations/sqlite_init.sql') ?: '');
        $this->pages = new PageRepository($pdo);

        $root = dirname(__DIR__, 3);
        $ui = new DevDashboard($root . '/resources/dev', new ResponseFactory());
        $registry = new SectionRegistry($root . '/resources/sections/registry.yaml');
        $forms = new SectionFormRenderer($registry, $this->pages);

        $this->controller = new SectionsController($ui, $this->pages, $registry, $forms);

        $this->pages->save(new Page(
            slug: 'about',
            title: 'About',
            layout: 'default',
            description: '',
            sections: [[
                'id' => 'hero-1',
                'type' => 'hero',
                'variant' => 'centered',
                'visible' => true,
                'content' => ['title' => 'Hello'],
                'style' => [],
            ]],
            meta: [],
            published: true,
            updatedAt: '',
        ));
    }

    public function testAddSectionUsesFirstVariant(): void
    {
        $response = $this->controller->add($this->hxPost(
            '/dev/pages/about/sections',
            'type=features',
        ), 'about');

        $page = $this->pages->findBySlug('about', false);
        $this->assertNotNull($page);
        $last = $page->sections[array_key_last($page->sections)];
        $this->assertSame('features', $last['type']);
        $this->assertSame('grid-3', $last['variant']);
        $this->assertStringContainsString('dev-section-card', (string) $response->getBody());
    }

    public function testAddHeroUsesRequestedVariant(): void
    {
        $this->controller->add($this->hxPost(
            '/dev/pages/about/sections',
            'type=hero&variant=fullscreen',
        ), 'about');

        $page = $this->pages->findBySlug('about', false);
        $this->assertNotNull($page);
        $last = $page->sections[array_key_last($page->sections)];
        $this->assertSame('hero', $last['type']);
        $this->assertSame('fullscreen', $last['variant']);
        $this->assertSame('Construisez quelque chose d\'exceptionnel', $last['content']['title'] ?? null);
    }

    public function testUpdateFallsBackWhenVariantInvalid(): void
    {
        $this->controller->update($this->hxPost(
            '/dev/pages/about/sections/hero-1',
            'variant=grid-3',
        ), 'about', 'hero-1');

        $page = $this->pages->findBySlug('about', false);
        $this->assertSame('centered', $page->sections[0]['variant']);
    }

    public function testUpdateKeepsValidVariantAndTogglesVisible(): void
    {
        $this->controller->update($this->hxPost(
            '/dev/pages/about/sections/hero-1',
            'variant=split&visible=0&content_title=Updated',
        ), 'about', 'hero-1');

        $page = $this->pages->findBySlug('about', false);
        $this->assertNotNull($page);
        $section = $page->sections[0];
        $this->assertSame('split', $section['variant']);
        $this->assertFalse($section['visible']);
        $this->assertSame('Updated', $section['content']['title']);
    }

    public function testMoveAndDestroySections(): void
    {
        $this->controller->add($this->post('/dev/pages/about/sections', 'type=cta'), 'about');

        $page = $this->pages->findBySlug('about', false);
        $this->assertCount(2, $page->sections);
        $ctaId = (string) $page->sections[1]['id'];

        $this->controller->move($this->post(
            '/dev/pages/about/sections/' . rawurlencode($ctaId) . '/move',
            'direction=up',
        ), 'about', $ctaId);

        $page = $this->pages->findBySlug('about', false);
        $this->assertSame('cta', $page->sections[0]['type']);

        $heroId = 'hero-1';
        $this->controller->destroy($this->hxPost(
            '/dev/pages/about/sections/' . $heroId . '/delete',
            '',
        ), 'about', $heroId);

        $page = $this->pages->findBySlug('about', false);
        $this->assertCount(1, $page->sections);
        $this->assertSame('cta', $page->sections[0]['type']);
    }

    public function testRestoreReinsertsDeletedSectionAtRequestedIndex(): void
    {
        $this->controller->add($this->post('/dev/pages/about/sections', 'type=cta'), 'about');

        $page = $this->pages->findBySlug('about', false);
        $this->assertNotNull($page);
        $this->assertCount(2, $page->sections);
        $hero = $page->sections[0];
        $ctaId = (string) $page->sections[1]['id'];

        $this->controller->destroy($this->hxPost(
            '/dev/pages/about/sections/' . rawurlencode((string) $hero['id']) . '/delete',
            '',
        ), 'about', (string) $hero['id']);

        $page = $this->pages->findBySlug('about', false);
        $this->assertCount(1, $page->sections);

        $this->controller->restore($this->hxPost(
            '/dev/pages/about/sections/restore',
            'section=' . rawurlencode((string) json_encode($hero, JSON_UNESCAPED_UNICODE)) . '&index=0',
        ), 'about');

        $page = $this->pages->findBySlug('about', false);
        $this->assertCount(2, $page->sections);
        $this->assertSame((string) $hero['id'], $page->sections[0]['id']);
        $this->assertSame($ctaId, $page->sections[1]['id']);
    }

    public function testReorderAppliesRequestedOrderAndKeepsUnknownIdsAtEnd(): void
    {
        $this->controller->add($this->post('/dev/pages/about/sections', 'type=cta'), 'about');
        $this->controller->add($this->post('/dev/pages/about/sections', 'type=features'), 'about');

        $page = $this->pages->findBySlug('about', false);
        $this->assertNotNull($page);
        $ctaId = (string) $page->sections[1]['id'];
        $featuresId = (string) $page->sections[2]['id'];

        $this->controller->reorder($this->hxPost(
            '/dev/pages/about/sections/reorder',
            'order=' . $featuresId . ',' . $ctaId . ',hero-1',
        ), 'about');

        $page = $this->pages->findBySlug('about', false);
        $this->assertSame($featuresId, $page->sections[0]['id']);
        $this->assertSame($ctaId, $page->sections[1]['id']);
        $this->assertSame('hero-1', $page->sections[2]['id']);
    }

    public function testReorderIgnoresEmptyOrder(): void
    {
        $response = $this->controller->reorder($this->hxPost(
            '/dev/pages/about/sections/reorder',
            'order=',
        ), 'about');

        $page = $this->pages->findBySlug('about', false);
        $this->assertNotNull($page);
        $this->assertSame('hero-1', $page->sections[0]['id']);
        $this->assertSame(200, $response->getStatus());
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

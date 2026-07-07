<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteNavHelper;
use PHPUnit\Framework\TestCase;

final class SiteNavHelperTest extends TestCase
{
    public function testRenderGroupDropdown(): void
    {
        $html = SiteNavHelper::renderNavHtml([
            [
                'type' => 'group',
                'label' => 'Produit',
                'children' => [
                    ['path' => '/features', 'label' => 'Fonctionnalités', 'type' => 'page'],
                    ['path' => '/pricing', 'label' => 'Tarifs', 'type' => 'page'],
                ],
            ],
        ], '/pricing');

        $this->assertStringContainsString('site-nav__item--group', $html);
        $this->assertStringContainsString('site-nav__details', $html);
        $this->assertStringContainsString('site-nav__submenu', $html);
        $this->assertStringContainsString('is-active', $html);
    }

    public function testResolvePublicTreePreservesGroups(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');
        $pages = new PageRepository($pdo);
        $pages->save(new Page(
            slug: 'features',
            title: 'Fonctionnalités',
            layout: 'default',
            description: '',
            sections: [],
            meta: [],
            published: true,
            updatedAt: '',
        ));

        $tree = SiteNavHelper::resolvePublicTree([
            [
                'id' => 'nav-group',
                'type' => 'group',
                'label' => 'Produit',
                'visible' => true,
                'children' => [
                    [
                        'id' => 'nav-child',
                        'type' => 'page',
                        'slug' => 'features',
                        'label' => 'Fonctionnalités',
                        'visible' => true,
                    ],
                ],
            ],
        ], $pages, 'Accueil');

        $this->assertCount(1, $tree);
        $this->assertSame('group', $tree[0]['type']);
        $this->assertCount(1, $tree[0]['children']);
    }
}

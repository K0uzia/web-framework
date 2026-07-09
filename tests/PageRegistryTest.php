<?php

declare(strict_types=1);

namespace Tests;

use Capsule\PageRegistry;
use Capsule\PageRepository;
use Capsule\PageRoute;
use PHPUnit\Framework\TestCase;

final class PageRegistryTest extends TestCase
{
    public function testBuildsStaticRoutesFromPublishedPages(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/seed_default_site.sql') ?: '');

        $registry = new PageRegistry(new PageRepository($pdo));
        $routes = $registry->routes();

        $this->assertArrayHasKey('GET /', $routes['static']);
        $this->assertInstanceOf(PageRoute::class, $routes['static']['GET /']);
        $this->assertSame('', $routes['static']['GET /']->slug);
    }
}

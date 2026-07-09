<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\OverviewController;
use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteRepository;
use PHPUnit\Framework\TestCase;

final class OverviewControllerTest extends TestCase
{
    public function testIndexReportsPageCountsAndRecentPages(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__, 3) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $site = new SiteRepository($pdo);
        $root = dirname(__DIR__, 3);
        $ui = new DevDashboard($root . '/resources/dev', new ResponseFactory());

        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));
        $pages->save(new Page('draft', 'Draft', 'default', '', [], [], false, ''));

        $controller = new OverviewController($ui, $pages, $site);
        $response = $controller->index(new Request('GET', '/dev/overview', [], [], [], []));
        $body = (string) $response->getBody();

        $this->assertSame(200, $response->getStatus());
        $this->assertStringContainsString('>2<', $body);
        $this->assertStringContainsString('Tableau de bord', $body);
        $this->assertStringContainsString('Home', $body);
    }
}

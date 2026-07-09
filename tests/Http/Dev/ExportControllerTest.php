<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\ExportController;
use Capsule\DevDashboard;
use Capsule\ExportPathPicker;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\PageRepository;
use Capsule\SectionRenderer;
use Capsule\SiteChrome;
use Capsule\SiteExportPath;
use Capsule\SiteExporter;
use Capsule\SiteRepository;
use Capsule\StylesheetResolver;
use Capsule\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExportController::class)]
final class ExportControllerTest extends TestCase
{
    public function testIndexShowsExportForm(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__, 3) . '/migrations/sqlite_init.sql') ?: '');

        $root = dirname(__DIR__, 3);
        $resources = $root . '/resources';
        $site = new SiteRepository($pdo);
        $pages = new PageRepository($pdo);
        $view = new View($resources . '/layouts', $resources . '/partials', $resources . '/pages');
        $ui = new DevDashboard($root . '/resources/dev', new ResponseFactory(), $site);

        $exporter = new SiteExporter(
            new ResponseFactory(),
            $view,
            $pages,
            $site,
            new SectionRenderer($view, $resources . '/sections', false),
            new SiteChrome($pages, $site, $view, 'Test'),
            new StylesheetResolver($root . '/public/assets/css'),
            $root,
            $root . '/public',
            'http://localhost:8080',
        );

        $controller = new ExportController(
            $ui,
            $site,
            $exporter,
            new SiteExportPath($root),
            new ExportPathPicker($root),
            new ResponseFactory(),
            $root,
            'http://localhost:8080',
            true,
        );

        $response = $controller->index(new Request('GET', '/dev/export', [], [], [], []));
        $body = (string) $response->getBody();

        $this->assertSame(200, $response->getStatus());
        $this->assertStringContainsString('Créer le site', $body);
        $this->assertStringContainsString('name="output_path"', $body);
        $this->assertStringContainsString('data-dev-export-browse', $body);
    }
}

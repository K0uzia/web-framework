<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Http\Factory\ResponseFactory;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SectionRenderer;
use Capsule\SiteChrome;
use Capsule\SiteExporter;
use Capsule\SiteRepository;
use Capsule\ScriptResolver;
use Capsule\StylesheetResolver;
use Capsule\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SiteExporter::class)]
final class SiteExporterTest extends TestCase
{
    public function testExportWritesHtmlAndAssets(): void
    {
        $root = dirname(__DIR__);
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents($root . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $siteRepo = new SiteRepository($pdo);
        $pages->save(new Page('', 'Accueil', 'default', 'Description accueil', [], [], true, ''));
        $pages->save(new Page('about', 'À propos', 'default', 'Description about', [], [], true, ''));

        $resources = $root . '/resources';
        $view = new View($resources . '/layouts', $resources . '/partials', $resources . '/pages');
        $sections = new SectionRenderer($view, $resources . '/sections', false);
        $chrome = new SiteChrome($pages, $siteRepo, $view, 'Test');
        $stylesheets = new StylesheetResolver($root . '/public/assets/css');
        $scripts = new ScriptResolver($root . '/public/assets/js');

        $outputDir = sys_get_temp_dir() . '/capsule-export-' . bin2hex(random_bytes(4));
        if (is_dir($outputDir)) {
            $this->removeTree($outputDir);
        }

        $exporter = new SiteExporter(
            new ResponseFactory(),
            $view,
            $pages,
            $siteRepo,
            $sections,
            $chrome,
            $stylesheets,
            $scripts,
            $root,
            $root . '/public',
            'http://localhost:8080',
        );

        $result = $exporter->export($outputDir);

        $this->assertSame(2, $result->pageCount);
        $this->assertFileExists($outputDir . '/index.html');
        $this->assertFileExists($outputDir . '/about/index.html');
        $homeHtml = file_get_contents($outputDir . '/index.html') ?: '';
        $aboutHtml = file_get_contents($outputDir . '/about/index.html') ?: '';
        $this->assertStringContainsString('Accueil', $homeHtml);
        $this->assertStringContainsString('href="assets/css/base.css"', $homeHtml);
        $this->assertStringNotContainsString('href="/assets/css/base.css"', $homeHtml);
        $this->assertStringContainsString('href="../assets/css/base.css"', $aboutHtml);
        $this->assertStringNotContainsString('href="/assets/css/base.css"', $aboutHtml);
        $this->assertDirectoryExists($outputDir . '/assets');
        $this->assertFileExists($outputDir . '/assets/css/base.css');
        $this->assertFileExists($outputDir . '/.htaccess');
        $htaccess = file_get_contents($outputDir . '/.htaccess') ?: '';
        $this->assertStringContainsString('RewriteEngine On', $htaccess);
        $this->assertStringContainsString('DirectoryIndex index.html', $htaccess);

        $this->removeTree($outputDir);
    }

    public function testExportRefusesNonEmptyDirectory(): void
    {
        $root = dirname(__DIR__);
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents($root . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $siteRepo = new SiteRepository($pdo);
        $resources = $root . '/resources';
        $view = new View($resources . '/layouts', $resources . '/partials', $resources . '/pages');

        $outputDir = sys_get_temp_dir() . '/capsule-export-full-' . bin2hex(random_bytes(4));
        mkdir($outputDir, 0755, true);
        file_put_contents($outputDir . '/existing.txt', 'x');

        $exporter = new SiteExporter(
            new ResponseFactory(),
            $view,
            $pages,
            $siteRepo,
            new SectionRenderer($view, $resources . '/sections', false),
            new SiteChrome($pages, $siteRepo, $view, 'Test'),
            new StylesheetResolver($root . '/public/assets/css'),
            new ScriptResolver($root . '/public/assets/js'),
            $root,
            $root . '/public',
            'http://localhost:8080',
        );

        $this->expectException(\InvalidArgumentException::class);
        $exporter->export($outputDir);

        unlink($outputDir . '/existing.txt');
        rmdir($outputDir);
    }

    private function removeTree(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->removeTree($path) : unlink($path);
        }

        rmdir($dir);
    }
}

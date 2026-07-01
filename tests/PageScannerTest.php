<?php

declare(strict_types=1);

namespace Tests;

use Capsule\PageScanner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PageScanner::class)]
final class PageScannerTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/capsule-pages-' . bin2hex(random_bytes(4));
        mkdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    public function testIndexMapsToRoot(): void
    {
        file_put_contents($this->tmpDir . '/index.html', '---' . "\ntitle: Home\n---\n<body></body>");

        $scanner = new PageScanner();
        $routes = $scanner->discover($this->tmpDir);

        $this->assertArrayHasKey('GET /', $routes['static']);
        $this->assertSame([], $routes['dynamic']);
    }

    public function testDynamicSlugRoute(): void
    {
        $blogDir = $this->tmpDir . '/blog';
        mkdir($blogDir);
        file_put_contents($blogDir . '/[slug].html', '---' . "\ntitle: Post\n---\n<body></body>");

        $scanner = new PageScanner();
        $routes = $scanner->discover($this->tmpDir);

        $this->assertCount(1, $routes['dynamic']);
        $params = $routes['dynamic'][0]->match('/blog/hello');
        $this->assertNotNull($params);
        $this->assertSame('hello', $params['slug']);
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir . '/' . $entry;
            is_dir($path) ? $this->removeDir($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}

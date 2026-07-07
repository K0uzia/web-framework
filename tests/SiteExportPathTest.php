<?php

declare(strict_types=1);

namespace Tests;

use Capsule\SiteExportPath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SiteExportPath::class)]
final class SiteExportPathTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__);
    }

    public function testResolvesRelativePathUnderExports(): void
    {
        $validator = new SiteExportPath($this->root);
        $resolved = $validator->resolve('exports/demo-site');

        $this->assertStringEndsWith('/exports/demo-site', $resolved);
    }

    public function testRejectsVendorPath(): void
    {
        $validator = new SiteExportPath($this->root);

        $this->expectException(\InvalidArgumentException::class);
        $validator->resolve('vendor/out');
    }

    public function testRejectsProjectRoot(): void
    {
        $validator = new SiteExportPath($this->root);

        $this->expectException(\InvalidArgumentException::class);
        $validator->resolve('.');
    }

    public function testRejectsTraversalToProjectRoot(): void
    {
        $validator = new SiteExportPath($this->root);

        $this->expectException(\InvalidArgumentException::class);
        $validator->resolve('exports/..');
    }

    public function testAllowsAbsolutePathOutsideProject(): void
    {
        $validator = new SiteExportPath($this->root);
        $resolved = $validator->resolve('/tmp/capsule-export-test');

        $this->assertSame('/tmp/capsule-export-test', $resolved);
    }
}

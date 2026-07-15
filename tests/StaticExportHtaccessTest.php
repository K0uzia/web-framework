<?php

declare(strict_types=1);

namespace Tests;

use Capsule\StaticExportHtaccess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StaticExportHtaccess::class)]
final class StaticExportHtaccessTest extends TestCase
{
    public function testDefaultRewriteBaseIsRoot(): void
    {
        $content = StaticExportHtaccess::content();

        $this->assertStringContainsString('DirectoryIndex index.html', $content);
        $this->assertStringContainsString('RewriteBase /', $content);
        $this->assertStringContainsString('index.html', $content);
    }

    public function testSubpathRewriteBase(): void
    {
        $content = StaticExportHtaccess::content('mon-site');

        $this->assertStringContainsString('RewriteBase /mon-site/', $content);
    }
}

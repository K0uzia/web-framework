<?php

declare(strict_types=1);

namespace Tests;

use Capsule\BasePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BasePath::class)]
final class BasePathTest extends TestCase
{
    public function testStripRemovesPrefix(): void
    {
        $base = new BasePath('/wf');

        $this->assertSame('/', $base->strip('/wf'));
        $this->assertSame('/', $base->strip('/wf/'));
        $this->assertSame('/dev', $base->strip('/wf/dev'));
        $this->assertSame('/dev/pages', $base->strip('/wf/dev/pages'));
    }

    public function testUrlPrefixesAbsolutePaths(): void
    {
        $base = new BasePath('/wf');

        $this->assertSame('/wf/dev', $base->url('/dev'));
        $this->assertSame('/wf/assets/css/base.css', $base->url('/assets/css/base.css'));
        $this->assertSame('https://example.org', $base->url('https://example.org'));
    }

    public function testRewriteHtmlPrefixesRootRelativeUrls(): void
    {
        $base = new BasePath('/wf');
        $html = '<a href="/dev">Dash</a><img src="/assets/logo.png" /><form action="/dev/login"></form>';

        $out = $base->rewriteHtml($html);

        $this->assertStringContainsString('href="/wf/dev"', $out);
        $this->assertStringContainsString('src="/wf/assets/logo.png"', $out);
        $this->assertStringContainsString('action="/wf/dev/login"', $out);
    }

    public function testEmptyBasePathIsNoOp(): void
    {
        $base = new BasePath('');

        $this->assertSame('/dev', $base->strip('/dev'));
        $this->assertSame('/dev', $base->url('/dev'));
        $this->assertSame('<a href="/dev">x</a>', $base->rewriteHtml('<a href="/dev">x</a>'));
    }

    public function testFromEnvNormalizesValue(): void
    {
        $this->assertSame('/wf', BasePath::fromEnv('/wf/')->value());
        $this->assertSame('/wf', BasePath::fromEnv('wf')->value());
    }

    public function testDetectsWfFromRequestUri(): void
    {
        $backup = $_SERVER;
        $_SERVER['REQUEST_URI'] = '/wf/dev/pages';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        try {
            $this->assertSame('/wf', BasePath::fromEnv('')->value());
        } finally {
            $_SERVER = $backup;
        }
    }
}

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
        $base = new BasePath('/mon-app');

        $this->assertSame('/', $base->strip('/mon-app'));
        $this->assertSame('/', $base->strip('/mon-app/'));
        $this->assertSame('/dev', $base->strip('/mon-app/dev'));
        $this->assertSame('/dev/pages', $base->strip('/mon-app/dev/pages'));
    }

    public function testUrlPrefixesAbsolutePaths(): void
    {
        $base = new BasePath('/mon-app');

        $this->assertSame('/mon-app/dev', $base->url('/dev'));
        $this->assertSame('/mon-app/assets/css/base.css', $base->url('/assets/css/base.css'));
        $this->assertSame('https://example.org', $base->url('https://example.org'));
    }

    public function testRewriteHtmlPrefixesRootRelativeUrls(): void
    {
        $base = new BasePath('/mon-app');
        $html = '<a href="/dev">Dash</a><img src="/assets/logo.png" /><form action="/dev/login"></form>';

        $out = $base->rewriteHtml($html);

        $this->assertStringContainsString('href="/mon-app/dev"', $out);
        $this->assertStringContainsString('src="/mon-app/assets/logo.png"', $out);
        $this->assertStringContainsString('action="/mon-app/dev/login"', $out);
    }

    public function testRewriteHtmlSkipsAlreadyPrefixedUrls(): void
    {
        $base = new BasePath('/wf');
        $html = '<link rel="stylesheet" href="/wf/assets/css/base.css" />'
            . '<script src="/wf/assets/js/site-nav.js"></script>'
            . '<img src="/assets/sections/hero.png" alt="" />';

        $once = $base->rewriteHtml($html);
        $twice = $base->rewriteHtml($once);

        $this->assertSame($once, $twice);
        $this->assertStringContainsString('href="/wf/assets/css/base.css"', $once);
        $this->assertStringContainsString('src="/wf/assets/sections/hero.png"', $once);
    }

    public function testUrlDoesNotDoublePrefix(): void
    {
        $base = new BasePath('/wf');

        $this->assertSame('/wf/assets/css/base.css', $base->url('/wf/assets/css/base.css'));
        $this->assertSame('/wf/dev', $base->url('/dev'));
    }

    public function testEmptyBasePathIsNoOp(): void
    {
        $base = new BasePath('');

        $this->assertSame('/dev', $base->strip('/dev'));
        $this->assertSame('/dev', $base->url('/dev'));
        $this->assertSame('<a href="/dev">x</a>', $base->rewriteHtml('<a href="/dev">x</a>'));
    }

    public function testStripDetectedPrefixUsesScriptName(): void
    {
        $backup = $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/apps/demo/public/index.php';
        unset($_ENV['APP_BASE_PATH'], $_SERVER['APP_BASE_PATH']);

        try {
            $this->assertSame('/', BasePath::stripDetectedPrefix('/apps/demo/'));
            $this->assertSame('/dev', BasePath::stripDetectedPrefix('/apps/demo/dev'));
            $this->assertSame('/apps/demo', BasePath::fromEnv('')->value());
        } finally {
            $_SERVER = $backup;
        }
    }

    public function testFromEnvNormalizesValue(): void
    {
        $this->assertSame('/mon-app', BasePath::fromEnv('/mon-app/')->value());
        $this->assertSame('/mon-app', BasePath::fromEnv('mon-app')->value());
    }

    public function testDetectsFromScriptNameStripsPublicDirectory(): void
    {
        $backup = $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/wf/public/index.php';
        unset($_ENV['APP_BASE_PATH'], $_SERVER['APP_BASE_PATH']);

        try {
            $this->assertSame('/wf', BasePath::fromEnv('')->value());
        } finally {
            $_SERVER = $backup;
        }
    }
}

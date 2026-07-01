<?php

declare(strict_types=1);

use Capsule\StylesheetResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StylesheetResolver::class)]
final class StylesheetResolverTest extends TestCase
{
    private string $cssDir;

    protected function setUp(): void
    {
        $this->cssDir = sys_get_temp_dir() . '/capsule-css-test-' . uniqid('', true);
        mkdir($this->cssDir . '/layouts', 0775, true);
        mkdir($this->cssDir . '/pages/index', 0775, true);
        mkdir($this->cssDir . '/partials', 0775, true);

        file_put_contents($this->cssDir . '/base.css', '/* base */');
        file_put_contents($this->cssDir . '/layouts/default.css', '/* layout */');
        file_put_contents($this->cssDir . '/pages/index/index.css', '/* page */');
        file_put_contents($this->cssDir . '/pages/index/hero.css', '/* section */');
        file_put_contents($this->cssDir . '/partials/site-header.css', '/* partial */');
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->cssDir);
    }

    public function testResolvesLayoutPageSectionAndPartial(): void
    {
        $resolver = new StylesheetResolver($this->cssDir);
        $body = '<section>{{> site-header.html }}</section>';

        $hrefs = $resolver->resolve('default', 'index', $body, [
            'styles_sections' => 'hero',
        ]);

        $this->assertSame([
            '/assets/css/base.css',
            '/assets/css/layouts/default.css',
            '/assets/css/pages/index/index.css',
            '/assets/css/pages/index/hero.css',
            '/assets/css/partials/site-header.css',
        ], $hrefs);
    }

    public function testToHtmlEscapesHref(): void
    {
        $resolver = new StylesheetResolver($this->cssDir);
        $html = $resolver->toHtml(['/assets/css/base.css']);

        $this->assertStringContainsString('rel="stylesheet"', $html);
        $this->assertStringContainsString('href="/assets/css/base.css"', $html);
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        if ($items === false) {
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}

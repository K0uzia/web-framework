<?php

declare(strict_types=1);

use Capsule\ScriptResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScriptResolver::class)]
final class ScriptResolverTest extends TestCase
{
    private string $jsDir;

    protected function setUp(): void
    {
        $this->jsDir = sys_get_temp_dir() . '/capsule-js-test-' . uniqid('', true);
        mkdir($this->jsDir . '/sections', 0775, true);

        foreach ([
            'site-nav.js',
            'site-header-blocks.js',
            'sections/hero.js',
            'sections/gallery.js',
            'sections/pricing.js',
            'sections/hero-video.js',
            'sections/auth-switch.js',
            'site-login-modal.js',
        ] as $file) {
            file_put_contents($this->jsDir . '/' . $file, '/* js */');
        }
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->jsDir);
    }

    public function testAlwaysIncludesChromeScripts(): void
    {
        $resolver = new ScriptResolver($this->jsDir);
        $srcs = $resolver->resolve('', []);

        $this->assertContains('/assets/js/site-nav.js', $srcs);
        $this->assertContains('/assets/js/site-header-blocks.js', $srcs);
    }

    public function testLoadsGalleryScriptOnlyWhenGalleryPresent(): void
    {
        $resolver = new ScriptResolver($this->jsDir);

        $galleryOnly = $resolver->resolve('', [['type' => 'gallery', 'variant' => 'gallery6']]);
        $this->assertContains('/assets/js/sections/gallery.js', $galleryOnly);
        $this->assertNotContains('/assets/js/sections/pricing.js', $galleryOnly);

        $heroOnly = $resolver->resolve('', [['type' => 'hero', 'variant' => 'hero3']]);
        $this->assertContains('/assets/js/sections/hero.js', $heroOnly);
        $this->assertNotContains('/assets/js/sections/gallery.js', $heroOnly);
    }

    public function testLoadsHeroVideoWhenMarkupContainsDataAttribute(): void
    {
        $resolver = new ScriptResolver($this->jsDir);
        $srcs = $resolver->resolve('<div data-hero-video></div>', []);

        $this->assertContains('/assets/js/sections/hero-video.js', $srcs);
    }

    public function testLoadsAuthSwitchWhenMarkupContainsAuthRoot(): void
    {
        $resolver = new ScriptResolver($this->jsDir);
        $srcs = $resolver->resolve('<div class="site-auth" data-auth-root data-auth-mode="login"></div>', []);

        $this->assertContains('/assets/js/sections/auth-switch.js', $srcs);
    }

    public function testLoadsLoginModalAndAuthSwitchForModalMarkup(): void
    {
        $resolver = new ScriptResolver($this->jsDir);
        $srcs = $resolver->resolve(
            '<div id="site-login-modal"><div data-auth-root><button data-auth-switch="signup"></button></div></div>',
            [],
        );

        $this->assertContains('/assets/js/site-login-modal.js', $srcs);
        $this->assertContains('/assets/js/sections/auth-switch.js', $srcs);
    }

    public function testToHtmlEscapesSrc(): void
    {
        $resolver = new ScriptResolver($this->jsDir);
        $html = $resolver->toHtml(['/assets/js/site-nav.js'], '/prefix');

        $this->assertStringContainsString('defer', $html);
        $this->assertStringContainsString('src="/prefix/assets/js/site-nav.js"', $html);
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->removeDir($path) : unlink($path);
        }

        rmdir($dir);
    }
}

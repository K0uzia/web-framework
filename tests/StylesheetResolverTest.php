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
        mkdir($this->cssDir . '/sections/hero', 0775, true);
        file_put_contents($this->cssDir . '/sections/shared.css', '/* shared */');
        file_put_contents($this->cssDir . '/sections/hero/hero3.css', '/* section */');
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
        ], [
            ['type' => 'hero', 'variant' => 'hero3'],
        ]);

        $this->assertContains('/assets/css/sections/shared.css', $hrefs);
        $this->assertContains('/assets/css/sections/hero/hero3.css', $hrefs);
        $this->assertContains('/assets/css/partials/site-header.css', $hrefs);
    }

    public function testIncludesFontAwesomeWhenVendorPresent(): void
    {
        $assetsRoot = dirname($this->cssDir);
        mkdir($assetsRoot . '/vendor/fontawesome/css', 0775, true);
        file_put_contents($assetsRoot . '/vendor/fontawesome/css/all.min.css', '/* fa */');

        $resolver = new StylesheetResolver($this->cssDir);
        $hrefs = $resolver->resolve('default', 'index', '', [], []);

        $this->assertContains('/assets/vendor/fontawesome/css/all.min.css', $hrefs);
        $this->assertContains('/assets/css/base.css', $hrefs);
        $this->assertSame(
            '/assets/vendor/fontawesome/css/all.min.css',
            $hrefs[0],
            'Font Awesome doit être chargé avant les feuilles du site',
        );
    }

    public function testToHtmlEscapesHref(): void
    {
        $resolver = new StylesheetResolver($this->cssDir);
        $html = $resolver->toHtml(['/assets/css/base.css']);

        $this->assertStringContainsString('rel="stylesheet"', $html);
        $this->assertStringContainsString('href="/assets/css/base.css"', $html);
    }

    public function testHeroCustomizeCssSkippedWithoutCustomStyle(): void
    {
        mkdir($this->cssDir . '/sections/hero', 0775, true);
        file_put_contents($this->cssDir . '/sections/hero/base.css', '/* base */');
        file_put_contents($this->cssDir . '/sections/hero/variants.css', '/* variants */');
        file_put_contents($this->cssDir . '/sections/hero/customize.css', '/* customize */');

        $resolver = new StylesheetResolver($this->cssDir);
        $hrefs = $resolver->resolve('default', 'index', '', [], [
            ['type' => 'hero', 'variant' => 'hero3'],
        ], [[
            'type' => 'hero',
            'variant' => 'hero3',
            'style' => ['bg' => 'primary', 'padding' => 'lg'],
        ]]);

        $this->assertNotContains('/assets/css/sections/hero/customize.css', $hrefs);
    }

    public function testHeroCustomizeCssLoadedWithCustomStyle(): void
    {
        mkdir($this->cssDir . '/sections/hero', 0775, true);
        file_put_contents($this->cssDir . '/sections/hero/base.css', '/* base */');
        file_put_contents($this->cssDir . '/sections/hero/variants.css', '/* variants */');
        file_put_contents($this->cssDir . '/sections/hero/customize.css', '/* customize */');

        $resolver = new StylesheetResolver($this->cssDir);
        $hrefs = $resolver->resolve('default', 'index', '', [], [
            ['type' => 'hero', 'variant' => 'hero3'],
        ], [[
            'type' => 'hero',
            'variant' => 'hero3',
            'style' => ['bg' => 'primary', 'padding' => 'lg', 'text_align' => 'center'],
        ]]);

        $this->assertContains('/assets/css/sections/hero/customize.css', $hrefs);
    }

    public function testAppearanceCssLoadedWithTypographyStyle(): void
    {
        mkdir($this->cssDir . '/sections/features', 0775, true);
        file_put_contents($this->cssDir . '/sections/features/base.css', '/* base */');
        file_put_contents($this->cssDir . '/sections/features/feature1.css', '/* feature */');
        file_put_contents($this->cssDir . '/sections/appearance.css', '/* appearance */');

        $resolver = new StylesheetResolver($this->cssDir);
        $hrefs = $resolver->resolve('default', 'index', '', [], [
            ['type' => 'features', 'variant' => 'feature1'],
        ], [[
            'type' => 'features',
            'variant' => 'feature1',
            'style' => ['bg' => 'background', 'padding' => 'md', 'title_size' => 'xl'],
        ]]);

        $this->assertContains('/assets/css/sections/appearance.css', $hrefs);
        $appearanceIndex = array_search('/assets/css/sections/appearance.css', $hrefs, true);
        $featureIndex = array_search('/assets/css/sections/features/feature1.css', $hrefs, true);
        $this->assertNotFalse($appearanceIndex);
        $this->assertNotFalse($featureIndex);
        $this->assertGreaterThan($featureIndex, $appearanceIndex);
    }

    public function testAppearanceCssSkippedWithoutTypographyStyle(): void
    {
        file_put_contents($this->cssDir . '/sections/appearance.css', '/* appearance */');

        $resolver = new StylesheetResolver($this->cssDir);
        $hrefs = $resolver->resolve('default', 'index', '', [], [
            ['type' => 'features', 'variant' => 'feature1'],
        ], [[
            'type' => 'features',
            'variant' => 'feature1',
            'style' => ['bg' => 'background', 'padding' => 'md'],
        ]]);

        $this->assertNotContains('/assets/css/sections/appearance.css', $hrefs);
    }

    public function testLoadsLoginCssForModalSectionMeta(): void
    {
        mkdir($this->cssDir . '/sections/login', 0775, true);
        mkdir($this->cssDir . '/sections/signup', 0775, true);
        file_put_contents($this->cssDir . '/sections/login/base.css', '/* login base */');
        file_put_contents($this->cssDir . '/sections/login/login1.css', '/* login1 */');
        file_put_contents($this->cssDir . '/sections/signup/base.css', '/* signup base */');
        file_put_contents($this->cssDir . '/sections/signup/signup1.css', '/* signup1 */');
        file_put_contents($this->cssDir . '/sections/auth-switch.css', '/* auth */');

        $resolver = new StylesheetResolver($this->cssDir);
        $hrefs = $resolver->resolve('default', 'index', '', [
            'login_modal_section' => [
                'type' => 'login',
                'variant' => 'login1',
                'visible' => true,
                'content' => [],
                'style' => [],
            ],
            'login_modal_auth_refs' => [
                ['type' => 'login', 'variant' => 'login1'],
                ['type' => 'signup', 'variant' => 'signup1'],
            ],
        ], []);

        $this->assertContains('/assets/css/sections/login/base.css', $hrefs);
        $this->assertContains('/assets/css/sections/login/login1.css', $hrefs);
        $this->assertContains('/assets/css/sections/signup/base.css', $hrefs);
        $this->assertContains('/assets/css/sections/signup/signup1.css', $hrefs);
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

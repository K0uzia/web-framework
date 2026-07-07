<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\FontUploader;
use App\Http\Dev\ThemeController;
use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\SiteRepository;
use PHPUnit\Framework\TestCase;

final class ThemeControllerTest extends TestCase
{
    private SiteRepository $site;
    private ThemeController $controller;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__, 3) . '/migrations/sqlite_init.sql') ?: '');

        $this->site = new SiteRepository($pdo);
        $ui = new DevDashboard(dirname(__DIR__, 3) . '/resources/dev', new ResponseFactory(), $this->site);
        $fonts = new FontUploader(sys_get_temp_dir() . '/capsule-test-fonts-' . bin2hex(random_bytes(4)));
        $this->controller = new ThemeController($ui, $this->site, $fonts);
    }

    public function testEditListsCustomFontsInPickerAndManager(): void
    {
        $theme = $this->site->getTheme();
        $theme['custom_fonts'] = [
            ['id' => 'font-abc', 'name' => 'Brand Sans', 'url' => '/uploads/fonts/brand.woff2', 'format' => 'woff2'],
        ];
        $this->site->setTheme($theme);

        $body = (string) $this->controller->edit(new Request('GET', '/dev/theme', [], [], [], []))->getBody();

        $this->assertStringContainsString('Brand Sans (importée)', $body);
        $this->assertStringContainsString('Brand Sans', $body);
        $this->assertStringContainsString('brand.woff2', $body);
    }

    public function testUploadFontWithoutFileKeepsCustomFontsUnchanged(): void
    {
        $before = $this->site->getTheme()['custom_fonts'];

        $response = $this->controller->uploadFont(new Request('POST', '/dev/theme/fonts', [], [], [], []));

        $this->assertSame(302, $response->getStatus());
        $this->assertSame($before, $this->site->getTheme()['custom_fonts']);
    }

    public function testRemoveFontDeletesMatchingEntryOnly(): void
    {
        $theme = $this->site->getTheme();
        $theme['custom_fonts'] = [
            ['id' => 'font-a', 'name' => 'Brand A', 'url' => '/uploads/fonts/a.woff2', 'format' => 'woff2'],
            ['id' => 'font-b', 'name' => 'Brand B', 'url' => '/uploads/fonts/b.woff2', 'format' => 'woff2'],
        ];
        $this->site->setTheme($theme);

        $this->controller->removeFont(new Request('POST', '/dev/theme/fonts/font-a/remove', [], [], [], []), 'font-a');

        $remaining = $this->site->getTheme()['custom_fonts'];
        $this->assertCount(1, $remaining);
        $this->assertSame('font-b', $remaining[0]['id']);
    }

    public function testEditUsesPreviewRoute(): void
    {
        $response = $this->controller->edit(new Request('GET', '/dev/theme', [], [], [], []));
        $body = (string) $response->getBody();

        $this->assertStringContainsString('/dev/preview/theme', $body);
    }

    public function testEditNormalizesInvalidColorValuesForColorInputs(): void
    {
        $theme = $this->site->getTheme();
        $theme['colors']['background'] = 'rgba(0, 0, 0, 0)';
        $this->site->setTheme($theme);

        $body = (string) $this->controller->edit(new Request('GET', '/dev/theme', [], [], [], []))->getBody();

        $this->assertStringContainsString('id="color_background" name="color_background" value="#ffffff"', $body);
        $this->assertStringContainsString('Palette de base', $body);
        $this->assertStringContainsString('dev-color-accordion', $body);
        $this->assertStringContainsString('name="color_text_muted"', $body);
    }

    public function testUpdatePersistsThemeAndReturnsHxPartial(): void
    {
        $response = $this->controller->update(new Request(
            'POST',
            '/dev/theme',
            [],
            ['HX-Request' => 'true', 'Content-Type' => 'application/x-www-form-urlencoded'],
            [],
            [],
            rawBody: 'color_primary=%23ff00aa&color_secondary=%2364748b&color_background=%23ffffff&color_text=%231a1a1a&color_surface=%23f8fafc&font_heading=Inter&font_body=system-ui&spacing_section=3rem',
        ));

        $theme = $this->site->getTheme();
        $this->assertSame('#ff00aa', $theme['colors']['primary']);
        $this->assertSame('3rem', $theme['spacing']['section']);
        $this->assertStringContainsString('Thème enregistré', (string) $response->getBody());
        $this->assertStringContainsString('--color-primary: #ff00aa', $this->site->themeCss());
    }
}

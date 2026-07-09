<?php

declare(strict_types=1);

namespace Tests;

use Capsule\ThemePalette;
use PHPUnit\Framework\TestCase;

final class ThemePaletteTest extends TestCase
{
    public function testDefaultsExposeAllDefinitions(): void
    {
        $defaults = ThemePalette::defaults();

        $this->assertCount(count(ThemePalette::definitions()), $defaults);
        $this->assertSame('#3b82f6', $defaults['primary']);
        $this->assertSame('#f8fafc', $defaults['surface']);
        $this->assertSame('#16a34a', $defaults['success']);
    }

    public function testNormalizeMigratesLegacyMutedToSurface(): void
    {
        $colors = ThemePalette::normalize([
            'primary' => '#111111',
            'muted' => '#eeeeee',
        ]);

        $this->assertSame('#eeeeee', $colors['surface']);
        $this->assertSame('#64748b', $colors['text_muted']);
    }

    public function testRenderFieldsHtmlGroupsPaletteSections(): void
    {
        $html = ThemePalette::renderFieldsHtml(ThemePalette::defaults());

        $this->assertStringContainsString('Palette de base', $html);
        $this->assertStringContainsString('Couleurs d', $html);
        $this->assertStringContainsString('action', $html);
        $this->assertStringContainsString('États UI', $html);
        $this->assertStringContainsString('data-dev-color-accordion', $html);
        $this->assertStringContainsString('<details class="dev-color-group" name="theme-colors">', $html);
        $this->assertStringNotContainsString('name="theme-colors" open', $html);
        $this->assertStringContainsString('dev-color-group__summary', $html);
        $this->assertStringContainsString('dev-color-row__type', $html);
        $this->assertStringContainsString('fa-link', $html);
        $this->assertStringContainsString('fa-panorama', $html);
        $this->assertStringContainsString('name="color_button_primary_bg"', $html);
        $this->assertStringContainsString('name="color_focus_ring"', $html);
        $this->assertStringContainsString('>Primaire</label>', $html);
        $this->assertStringContainsString('dev-color-row__help', $html);
        $this->assertStringNotContainsString('dev-field__hint', $html);
    }

    public function testFromFormUpdatesOnlySubmittedFields(): void
    {
        $before = ThemePalette::defaults();
        $after = ThemePalette::fromForm(['color_primary' => '#ff00aa'], $before);

        $this->assertSame('#ff00aa', $after['primary']);
        $this->assertSame($before['secondary'], $after['secondary']);
    }
}

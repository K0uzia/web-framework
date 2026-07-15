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
        $this->assertStringContainsString('En-tête', $html);
        $this->assertStringContainsString('Pied de page', $html);
        $this->assertStringContainsString('name="color_header_background"', $html);
        $this->assertStringContainsString('name="color_footer_background"', $html);
        $this->assertStringContainsString('États UI', $html);
        $this->assertStringContainsString('data-dev-color-accordion', $html);
        $this->assertStringContainsString('<details class="dev-color-group" name="theme-colors">', $html);
        $this->assertStringNotContainsString('name="theme-colors" open', $html);
        $this->assertStringContainsString('dev-color-group__summary', $html);
        $this->assertStringContainsString('dev-color-row__type', $html);
        $this->assertStringContainsString('fa-link', $html);
        $this->assertStringContainsString('fa-panorama', $html);
        $this->assertStringContainsString('name="color_button_primary_bg"', $html);
        $this->assertStringContainsString('name="color_button_primary_border_hover"', $html);
        $this->assertStringContainsString('name="color_button_outline_text_hover"', $html);
        $this->assertStringContainsString('name="color_focus_ring"', $html);
        $this->assertStringContainsString('dev-action-matrix', $html);
        $this->assertStringContainsString('Bouton principal', $html);
        $this->assertStringContainsString('Liens de navigation', $html);
        $this->assertStringContainsString('name="color_nav_link_text"', $html);
        $this->assertStringContainsString('name="color_nav_link_bg"', $html);
        $this->assertStringContainsString('data-color-transparent', $html);
        $this->assertStringContainsString('value="transparent"', $html);
        $this->assertStringContainsString('name="color_nav_link_bg_active"', $html);
        $this->assertStringContainsString('Liens de contenu', $html);
        $this->assertStringContainsString('dev-action-matrix--cols-2', $html);
        $this->assertStringNotContainsString('name="color_button_outline_bg"', $html);
        $this->assertStringNotContainsString('name="color_button_outline_hover"', $html);
        $this->assertStringNotContainsString('dev-action-matrix__scroll', $html);
        $this->assertStringNotContainsString('dev-field__hint', $html);
    }

    public function testNormalizeFillsMissingButtonHoverTokensFromLegacyPalette(): void
    {
        $colors = ThemePalette::normalize([
            'button_primary_bg' => '#111111',
            'button_primary_hover' => '#222222',
            'button_primary_text' => '#ffffff',
            'button_outline_text' => '#333333',
            'button_outline_border' => '#444444',
        ]);

        $this->assertSame('#111111', $colors['button_primary_border']);
        $this->assertSame('#ffffff', $colors['button_primary_text_hover']);
        $this->assertSame('#222222', $colors['button_primary_border_hover']);
        $this->assertSame('#333333', $colors['button_outline_text_hover']);
        $this->assertSame('#444444', $colors['button_outline_border_hover']);
    }

    public function testFromFormUpdatesOnlySubmittedFields(): void
    {
        $before = ThemePalette::defaults();
        $after = ThemePalette::fromForm(['color_primary' => '#ff00aa'], $before);

        $this->assertSame('#ff00aa', $after['primary']);
        $this->assertSame($before['secondary'], $after['secondary']);
    }

    public function testFromFormPersistsTransparentBackground(): void
    {
        $before = ThemePalette::defaults();
        $after = ThemePalette::fromForm(['color_button_secondary_bg' => 'transparent'], $before);

        $this->assertSame('transparent', $after['button_secondary_bg']);
    }
}

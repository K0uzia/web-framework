<?php

declare(strict_types=1);

namespace Tests;

use Capsule\SectionRenderer;
use Capsule\View;
use PHPUnit\Framework\TestCase;

final class SectionRendererTest extends TestCase
{
    private SectionRenderer $renderer;

    protected function setUp(): void
    {
        $root = dirname(__DIR__);
        $this->renderer = new SectionRenderer(
            new View($root . '/resources/layouts', $root . '/resources/partials'),
            $root . '/resources/sections',
            true,
        );
    }

    public function testRendersHeroSection(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'hero-1',
            'type' => 'hero',
            'variant' => 'centered',
            'content' => [
                'title' => 'Hello',
                'subtitle' => 'World',
                'cta_label' => 'Go',
                'cta_href' => '/go',
            ],
            'style' => ['bg' => 'primary', 'text_align' => 'center', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-hero--centered', $html);
        $this->assertStringContainsString('Hello', $html);
        $this->assertStringContainsString('World', $html);
    }

    public function testRendersFeaturesItemsAsHtml(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'features-1',
            'type' => 'features',
            'variant' => 'grid-3',
            'content' => [
                'items' => [
                    ['title' => 'Rapide', 'text' => 'Pages servies depuis SQLite.'],
                    ['title' => 'Simple', 'text' => 'Sections composables.'],
                ],
            ],
            'style' => ['bg' => 'muted', 'padding' => 'lg'],
        ]);

        $this->assertStringContainsString('<article class="section-features__item">', $html);
        $this->assertStringContainsString('<h3>Rapide</h3>', $html);
        $this->assertStringNotContainsString('&lt;article', $html);
    }

    public function testSkipsHiddenSection(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'hero-hidden',
            'type' => 'hero',
            'variant' => 'centered',
            'visible' => false,
            'content' => [
                'title' => 'Hidden',
                'subtitle' => 'Nope',
                'cta_label' => 'Go',
                'cta_href' => '#',
            ],
            'style' => ['bg' => 'primary', 'text_align' => 'center', 'padding' => 'lg'],
        ]);

        $this->assertSame('', $html);
    }

    public function testExtractSectionRefs(): void
    {
        $refs = $this->renderer->extractSectionRefs([
            ['type' => 'hero', 'variant' => 'centered'],
            ['type' => 'cta', 'variant' => 'banner'],
        ]);

        $this->assertSame([
            ['type' => 'hero', 'variant' => 'centered'],
            ['type' => 'cta', 'variant' => 'banner'],
        ], $refs);
    }

    public function testRendersHeroVariants(): void
    {
        foreach (['centered', 'split', 'split-left', 'fullscreen', 'image-below', 'badge', 'minimal', 'video'] as $variant) {
            $html = $this->renderer->renderOne([
                'id' => 'hero-' . $variant,
                'type' => 'hero',
                'variant' => $variant,
                'content' => [
                    'title' => 'Titre',
                    'subtitle' => 'Sous-titre',
                    'badge' => 'Nouveau',
                    'buttons' => [['label' => 'Go', 'href' => '#', 'style' => 'primary']],
                ],
                'style' => ['bg' => 'background', 'text_align' => 'center', 'padding' => 'xl'],
            ]);

            $this->assertStringContainsString('section-hero--' . $variant, $html, 'Variant ' . $variant);
            $this->assertStringContainsString('Titre', $html);
        }
    }

    public function testRendersStockFallbackForHeroSplitWithoutImage(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'hero-split',
            'type' => 'hero',
            'variant' => 'split',
            'content' => [
                'title' => 'Titre',
                'subtitle' => 'Sous-titre',
                'buttons' => [['label' => 'Go', 'href' => '#', 'style' => 'primary']],
            ],
            'style' => ['bg' => 'background', 'text_align' => 'center', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('/assets/stock/', $html);
        $this->assertStringContainsString('section-hero__img', $html);
    }
}

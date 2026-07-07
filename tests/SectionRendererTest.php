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
            'variant' => 'feature-3',
            'content' => [
                'title' => 'Fonctionnalités',
                'items' => [
                    ['title' => 'Rapide', 'text' => 'Pages servies depuis SQLite.'],
                    ['title' => 'Simple', 'text' => 'Sections composables.'],
                ],
            ],
            'style' => ['bg' => 'muted', 'padding' => 'lg'],
        ]);

        $this->assertStringContainsString('section-features--feature-3', $html);
        $this->assertStringContainsString('section-features__card', $html);
        $this->assertStringContainsString('<h3 class="section-features__item-title">Rapide</h3>', $html);
    }

    public function testRendersLegacyGridVariantAsFeature3(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'features-legacy',
            'type' => 'features',
            'variant' => 'grid-3',
            'content' => [
                'items' => [
                    ['title' => 'Test', 'text' => 'Desc'],
                ],
            ],
            'style' => ['bg' => 'background', 'padding' => 'lg'],
        ]);

        $this->assertStringContainsString('section-features--feature-3', $html);
    }

    public function testRendersFeature1WithImageAndButton(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'features-f1',
            'type' => 'features',
            'variant' => 'feature-1',
            'content' => [
                'title' => 'Titre',
                'subtitle' => 'Sous-titre',
                'image_url' => '/assets/stock/01-bureau.jpg',
                'buttons' => [['label' => 'En savoir plus', 'href' => '#', 'style' => 'primary']],
            ],
            'style' => ['bg' => 'background', 'padding' => 'lg'],
        ]);

        $this->assertStringContainsString('section-features--feature-1', $html);
        $this->assertStringContainsString('section-features__figure', $html);
        $this->assertStringContainsString('01-bureau.jpg', $html);
    }

    public function testRendersFeaturesVariantsWithDistinctMarkup(): void
    {
        $variants = [
            'feature-1' => 'section-features--feature-1',
            'feature-2' => 'section-features--feature-2',
            'feature-3' => 'section-features--feature-3',
            'feature-4' => 'section-features--feature-4',
            'feature-5' => 'section-features--feature-5',
            'feature-6' => 'section-features--feature-6',
            'feature-7' => 'section-features--feature-7',
            'feature-8' => 'section-features--feature-8',
            'feature-9' => 'section-features--feature-9',
            'feature-10' => 'section-features--feature-10',
        ];

        foreach ($variants as $variant => $class) {
            $html = $this->renderer->renderOne([
                'id' => 'features-' . $variant,
                'type' => 'features',
                'variant' => $variant,
                'content' => [
                    'title' => 'Fonctionnalités',
                    'items' => [
                        ['title' => 'Point 1', 'text' => 'Description.'],
                        ['title' => 'Point 2', 'text' => 'Description.'],
                    ],
                ],
                'style' => ['bg' => 'background', 'padding' => 'lg'],
            ]);

            $this->assertStringContainsString($class, $html, 'Variant ' . $variant);
        }

        $bento = $this->renderer->renderOne([
            'id' => 'features-bento-lead',
            'type' => 'features',
            'variant' => 'feature-5',
            'content' => [
                'items' => [
                    ['title' => 'Principal', 'text' => 'Mise en avant.'],
                    ['title' => 'Secondaire', 'text' => 'Complément.'],
                ],
            ],
            'style' => ['bg' => 'background', 'padding' => 'lg'],
        ]);
        $this->assertStringContainsString('section-features__item--lead', $bento);

        $list = $this->renderer->renderOne([
            'id' => 'features-checklist',
            'type' => 'features',
            'variant' => 'feature-6',
            'content' => [
                'title' => 'Avantages',
                'items' => [
                    ['title' => 'Point clé'],
                ],
            ],
            'style' => ['bg' => 'background', 'padding' => 'lg'],
        ]);
        $this->assertStringContainsString('section-features__checklist', $list);
        $this->assertStringContainsString('Point clé', $list);
    }

    public function testRendersCustomFeatureIconFromContent(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'features-icon',
            'type' => 'features',
            'variant' => 'feature-3',
            'content' => [
                'items' => [
                    ['icon' => 'fa-star', 'title' => 'Qualité', 'text' => 'Test.'],
                ],
            ],
            'style' => ['bg' => 'background', 'padding' => 'lg'],
        ]);

        $this->assertStringContainsString('class="fa-solid fa-star"', $html);
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

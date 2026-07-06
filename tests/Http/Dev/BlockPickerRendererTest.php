<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\BlockPickerRenderer;
use Capsule\SectionRegistry;
use PHPUnit\Framework\TestCase;

final class BlockPickerRendererTest extends TestCase
{
    public function testRendersVariantCardPerLayout(): void
    {
        $root = dirname(__DIR__, 3);
        $registry = new SectionRegistry($root . '/resources/sections/registry.yaml');
        $renderer = new BlockPickerRenderer($registry);
        $html = $renderer->renderPickerHtml();

        $this->assertGreaterThanOrEqual(140, $renderer->countPickerCards());
        $this->assertSame($renderer->countPickerCards(), substr_count($html, 'data-block-type="'));
        $this->assertStringContainsString('data-block-variant="centered"', $html);
        $this->assertStringContainsString('Hero : Plein écran', $html);
        $this->assertStringContainsString('Fonctionnalités : Bento', $html);
        $this->assertStringContainsString('data-block-filter="blog"', $html);
    }
}

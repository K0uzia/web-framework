<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\Sections\BlockPickerRenderer;
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

        $this->assertGreaterThan(0, $renderer->countPickerCards());
        $this->assertSame($renderer->countPickerCards(), substr_count($html, 'data-block-type="'));
        $this->assertStringContainsString('data-block-variant="hero3"', $html);
        $this->assertStringContainsString('Hero 3', $html);
        $this->assertStringContainsString('data-block-filter="hero"', $html);
        $this->assertStringContainsString('id="dev-block-picker-search"', $html);
        $this->assertStringContainsString('dev-block-picker__layout', $html);
        $this->assertStringContainsString('dev-block-nav-item', $html);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Section\Support;

use Capsule\Section\Support\SectionButtonStyle;
use Capsule\Section\Support\SectionButtons;
use PHPUnit\Framework\TestCase;

final class SectionButtonStyleTest extends TestCase
{
    public function testNormalizeAcceptsOutline(): void
    {
        $this->assertSame('outline', SectionButtonStyle::normalize('outline'));
        $this->assertSame('primary', SectionButtonStyle::normalize('invalid'));
    }

    public function testRenderOutputsOutlineClass(): void
    {
        $html = SectionButtons::render([
            ['label' => 'Découvrir', 'href' => '#', 'style' => 'outline'],
        ]);

        $this->assertStringContainsString('section-button--outline', $html);
    }
}

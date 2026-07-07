<?php

declare(strict_types=1);

namespace Tests;

use Capsule\SectionLayoutFamilies;
use PHPUnit\Framework\TestCase;

final class SectionLayoutFamiliesTest extends TestCase
{
    public function testGridVariantsShareGridFamily(): void
    {
        $this->assertContains('grid-3', SectionLayoutFamilies::htmlFamilies('grid-2'));
        $this->assertSame(['shared'], SectionLayoutFamilies::cssFamilies('feature-3'));
    }

    public function testVerticalTimelineUsesRowFallback(): void
    {
        $this->assertContains('vertical', SectionLayoutFamilies::htmlFamilies('vertical'));
        $this->assertContains('row', SectionLayoutFamilies::htmlFamilies('vertical'));
    }
}

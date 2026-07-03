<?php

declare(strict_types=1);

namespace Tests;

use Capsule\SectionRegistry;
use PHPUnit\Framework\TestCase;

final class SectionRegistryTest extends TestCase
{
    public function testLoadsHeroVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $types = $registry->getTypes();

        $this->assertContains('hero', $types);
        $this->assertArrayHasKey('centered', $registry->getVariants('hero'));
        $this->assertArrayHasKey('title', $registry->getContentFields('hero'));
    }

    public function testLoadsFeaturesGrid3Variant(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');

        $this->assertArrayHasKey('grid-3', $registry->getVariants('features'));
    }

    public function testLoadsCtaBannerVariant(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');

        $this->assertArrayHasKey('banner', $registry->getVariants('cta'));
    }
}

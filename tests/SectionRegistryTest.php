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

    public function testLoadsBlockGroups(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');

        $this->assertSame('hero', $registry->getGroup('hero'));
        $this->assertContains('pricing', $registry->getGroups());
        $this->assertContains('about', $registry->getTypes());
        $this->assertContains('steps', $registry->getTypes());
        $this->assertSame('gallery', $registry->getGroup('gallery'));
    }
}

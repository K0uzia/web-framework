<?php

declare(strict_types=1);

namespace Tests\Section;

use Capsule\Section\SectionHandlerRegistry;
use Capsule\Section\SectionVariantResolver;
use Capsule\SectionRegistry;
use PHPUnit\Framework\TestCase;

final class SectionVariantResolverTest extends TestCase
{
    private SectionVariantResolver $resolver;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 2);
        $registry = new SectionRegistry(
            $root . '/resources/sections/registry.yaml',
            $root . '/resources/sections/_shared/style-fields.yaml',
        );
        $this->resolver = new SectionVariantResolver($registry, new SectionHandlerRegistry());
    }

    public function testLegacyHeroSplitMapsToHero1(): void
    {
        $this->assertSame('hero1', $this->resolver->resolve('hero', 'split'));
    }

    public function testLegacyFeaturesGrid3MapsToFeature3(): void
    {
        $this->assertSame('feature3', $this->resolver->resolve('features', 'grid-3'));
    }

    public function testRegistryVariantIsKept(): void
    {
        $this->assertSame('hero3', $this->resolver->resolve('hero', 'hero3'));
    }
}

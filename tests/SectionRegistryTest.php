<?php

declare(strict_types=1);

namespace Tests;

use Capsule\SectionAssets;
use Capsule\SectionRegistry;
use PHPUnit\Framework\TestCase;

final class SectionRegistryTest extends TestCase
{
    public function testLoadsAllHeroVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $types = $registry->getTypes();
        $variants = array_keys($registry->getVariants('hero'));

        $this->assertContains('hero', $types);
        $this->assertSame(SectionAssets::heroVariantIds(), $variants);
        $this->assertArrayHasKey('title', $registry->getContentFields('hero'));
        $this->assertArrayHasKey('review_avatars', $registry->getContentFields('hero'));
    }

    public function testLoadsAllFeatureVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('features'));

        $this->assertSame(SectionAssets::featureVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('features'));
    }

    public function testLoadsBlockGroups(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');

        $this->assertSame('hero', $registry->getGroup('hero'));
        $this->assertSame('feature', $registry->getGroup('features'));
        $this->assertSame('integration', $registry->getGroup('integrations'));
        $this->assertContains('hero', $registry->getGroups());
        $this->assertContains('feature', $registry->getGroups());
        $this->assertContains('integration', $registry->getGroups());
    }

    public function testLoadsAllIntegrationVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('integrations'));

        $this->assertSame(SectionAssets::integrationVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('integrations'));
    }

    public function testLoadsAllPricingVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('pricing'));

        $this->assertSame(SectionAssets::pricingVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('pricing'));
        $this->assertSame('pricing', $registry->getGroup('pricing'));
    }

    public function testLoadsAllContactVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('contact'));

        $this->assertSame(SectionAssets::contactVariantIds(), $variants);
        $this->assertArrayHasKey('email', $registry->getContentFields('contact'));
        $this->assertSame('contact', $registry->getGroup('contact'));
    }

    public function testLoadsAllTestimonialVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('testimonials'));

        $this->assertSame(SectionAssets::testimonialVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('testimonials'));
        $this->assertSame('testimonial', $registry->getGroup('testimonials'));
    }

    public function testLoadsAllGalleryVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('gallery'));

        $this->assertSame(SectionAssets::galleryVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('gallery'));
        $this->assertSame('gallery', $registry->getGroup('gallery'));
    }
}

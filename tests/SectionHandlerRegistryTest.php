<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Section\SectionHandlerRegistry;
use Capsule\Section\Hero\HeroSectionHandler;
use PHPUnit\Framework\TestCase;

final class SectionHandlerRegistryTest extends TestCase
{
    public function testRegistryProvidesKnownHandlers(): void
    {
        $registry = new SectionHandlerRegistry();

        $this->assertTrue($registry->has('hero'));
        $this->assertInstanceOf(HeroSectionHandler::class, $registry->get('hero'));
        $this->assertSame('hero3', $registry->normalizeVariant('hero', 'hero3'));
        $this->assertTrue($registry->has('signup'));
        $this->assertSame('signup1', $registry->normalizeVariant('signup', 'signup1'));
    }

    public function testLoginHandlerDeclaresAuthSwitchJsModule(): void
    {
        $registry = new SectionHandlerRegistry();
        $handler = $registry->get('login');
        $this->assertNotNull($handler);
        $this->assertSame(['sections/auth-switch.js'], $handler->jsModules('login1'));
    }

    public function testGalleryHandlerDeclaresJsModule(): void
    {
        $registry = new SectionHandlerRegistry();
        $handler = $registry->get('gallery');
        $this->assertNotNull($handler);
        $this->assertSame(['sections/gallery.js'], $handler->jsModules('gallery4'));
    }
}

<?php

declare(strict_types=1);

namespace Tests;

use Capsule\FontAwesomeIcon;
use PHPUnit\Framework\TestCase;

final class FontAwesomeIconTest extends TestCase
{
    public function testGlyphNormalizesFullClassName(): void
    {
        $this->assertSame('fa-bolt', FontAwesomeIcon::glyph('fa-solid fa-bolt'));
    }

    public function testGlyphAddsPrefixWhenMissing(): void
    {
        $this->assertSame('fa-rocket', FontAwesomeIcon::glyph('rocket'));
    }

    public function testSolidClassReturnsFullClassAttribute(): void
    {
        $this->assertSame('fa-solid fa-shield-halved', FontAwesomeIcon::solidClass('fa-shield-halved'));
    }

    public function testDefaultForIndexRotates(): void
    {
        $this->assertSame('fa-bolt', FontAwesomeIcon::defaultForIndex(1));
        $this->assertSame('fa-shield-halved', FontAwesomeIcon::defaultForIndex(2));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Support;

use Capsule\Support\Utf8;
use PHPUnit\Framework\TestCase;

final class Utf8Test extends TestCase
{
    public function testStrtolowerHandlesAccents(): void
    {
        $this->assertSame('éléphant café', Utf8::strtolower('Éléphant Café'));
    }

    public function testStrtoupperHandlesAccents(): void
    {
        $this->assertSame('ÉLÉPHANT', Utf8::strtoupper('Éléphant'));
    }

    public function testSubstrReturnsFirstCharacter(): void
    {
        $this->assertSame('É', Utf8::substr('Éléphant', 0, 1));
    }
}

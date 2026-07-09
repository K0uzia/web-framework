<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use Capsule\LayoutRegistry;
use PHPUnit\Framework\TestCase;

final class LayoutRegistryTest extends TestCase
{
    private LayoutRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new LayoutRegistry(dirname(__DIR__, 3) . '/resources/layouts');
    }

    public function testAllIncludesDefaultLayout(): void
    {
        $layouts = $this->registry->all();

        $this->assertContains('default', $layouts);
        $this->assertSame($layouts, array_values(array_unique($layouts)));
    }

    public function testExistsSanitizesLayoutName(): void
    {
        $this->assertTrue($this->registry->exists('default'));
        $this->assertFalse($this->registry->exists('../etc/passwd'));
        $this->assertFalse($this->registry->exists('missing-layout'));
    }
}

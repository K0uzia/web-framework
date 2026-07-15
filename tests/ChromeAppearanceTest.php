<?php

declare(strict_types=1);

namespace Tests;

use Capsule\ChromeAppearance;
use PHPUnit\Framework\TestCase;

final class ChromeAppearanceTest extends TestCase
{
    public function testHeaderBorderClassModifiers(): void
    {
        $this->assertSame('', ChromeAppearance::headerClassModifiers(['appearance' => ['show_border' => true, 'bg' => 'theme']]));
        $this->assertSame(' site-header--no-border', ChromeAppearance::headerClassModifiers(['appearance' => ['show_border' => false, 'bg' => 'theme']]));
        $this->assertSame(' site-header--bg-primary', ChromeAppearance::headerClassModifiers(['appearance' => ['show_border' => true, 'bg' => 'primary']]));
    }

    public function testFooterBorderClassModifiers(): void
    {
        $this->assertSame('', ChromeAppearance::footerClassModifiers(['appearance' => ['show_border' => true, 'bg' => 'theme']]));
        $this->assertSame(' site-footer--no-border', ChromeAppearance::footerClassModifiers(['appearance' => ['show_border' => false, 'bg' => 'theme']]));
        $this->assertSame(' site-footer--bg-primary', ChromeAppearance::footerClassModifiers(['appearance' => ['show_border' => true, 'bg' => 'primary']]));
    }
}

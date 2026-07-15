<?php

declare(strict_types=1);

namespace Tests;

use Capsule\ThemeColor;
use PHPUnit\Framework\TestCase;

final class ThemeColorTest extends TestCase
{
    public function testNormalizesSixDigitHex(): void
    {
        $this->assertSame('#ff00aa', ThemeColor::normalize('#FF00AA', '#000000'));
    }

    public function testExpandsShortHex(): void
    {
        $this->assertSame('#ff00aa', ThemeColor::normalize('#f0a', '#000000'));
    }

    public function testConvertsRgbToHex(): void
    {
        $this->assertSame('#0f172a', ThemeColor::normalize('rgb(15, 23, 42)', '#ffffff'));
    }

    public function testTransparentKeywordIsPreserved(): void
    {
        $this->assertSame('transparent', ThemeColor::normalize('transparent', '#ffffff'));
        $this->assertSame('transparent', ThemeColor::normalize('Transparent', '#ffffff'));
    }

    public function testTransparentRgbaNormalizesToTransparent(): void
    {
        $this->assertSame('transparent', ThemeColor::normalize('rgba(0, 0, 0, 0)', '#ffffff'));
    }

    public function testPickerHexUsesFallbackWhenValueIsTransparent(): void
    {
        $this->assertSame('#f8fafc', ThemeColor::pickerHex('transparent', '#f8fafc'));
    }
}

<?php

declare(strict_types=1);

namespace Tests;

use Capsule\MediaDisplaySettings;
use PHPUnit\Framework\TestCase;

final class MediaDisplaySettingsTest extends TestCase
{
    public function testImageFitDefaultsToCover(): void
    {
        $this->assertSame('cover', MediaDisplaySettings::imageFit([]));
    }

    public function testVideoFitClass(): void
    {
        $class = MediaDisplaySettings::videoFitClass(['video_fit' => 'contain'], 'section-hero__iframe', 'cover');

        $this->assertSame('section-hero__iframe--fit-contain', $class);
    }

    public function testVideoEmbedFlagsFromContent(): void
    {
        $flags = MediaDisplaySettings::videoFlags([
            'video_autoplay' => 'on',
            'video_muted' => 'off',
            'video_loop' => 'on',
            'video_controls' => 'off',
        ], 'embed');

        $this->assertTrue($flags['autoplay']);
        $this->assertFalse($flags['muted']);
        $this->assertTrue($flags['loop']);
        $this->assertFalse($flags['controls']);
    }
}

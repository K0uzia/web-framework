<?php

declare(strict_types=1);

namespace Tests;

use Capsule\BackgroundType;
use Capsule\ShaderLibrary;
use PHPUnit\Framework\TestCase;

final class ShaderLibraryTest extends TestCase
{
    public function testPresetsIncludeShader3Default(): void
    {
        $ids = array_map(static fn (array $preset): string => (string) ($preset['id'] ?? ''), ShaderLibrary::presets());

        $this->assertContains('shader3-default', $ids);
        $this->assertContains('shader3-sky', $ids);
    }

    public function testNormalizeIdFallsBackToDefault(): void
    {
        $this->assertSame('shader3-default', ShaderLibrary::normalizeId('unknown'));
    }

    public function testBackgroundTypeLabels(): void
    {
        $labels = BackgroundType::labels();

        $this->assertSame('Image', $labels[BackgroundType::IMAGE]);
        $this->assertSame('Vidéo', $labels[BackgroundType::VIDEO]);
        $this->assertSame('Shader animé', $labels[BackgroundType::SHADER]);
    }

    public function testSceneBackgroundForPreset(): void
    {
        $this->assertSame('#0f172a', ShaderLibrary::sceneBackgroundFor('shader3-sky'));
        $this->assertSame('#1e293b', ShaderLibrary::sceneBackgroundFor('shader3-default'));
        $this->assertSame('#1e293b', ShaderLibrary::sceneBackgroundFor('unknown'));
    }
}

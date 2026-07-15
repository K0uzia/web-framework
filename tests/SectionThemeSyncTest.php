<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Section\SectionCssModules;
use Capsule\Section\SectionHandlerRegistry;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class SectionThemeSyncTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__);
    }

    public function testEverySectionTemplateAndCssModuleIsThemeReady(): void
    {
        $htmlRoot = $this->root . '/resources/sections';
        $cssRoot = $this->root . '/public/assets/css/sections';
        $handlers = new SectionHandlerRegistry();
        $typesSeen = [];

        foreach (glob($htmlRoot . '/*/*.html') ?: [] as $htmlPath) {
            $type = basename(dirname($htmlPath));
            $variant = basename($htmlPath, '.html');
            if ($type === '_shared' || str_starts_with($variant, '_')) {
                continue;
            }

            $typesSeen[$type] = true;
            $content = file_get_contents($htmlPath) ?: '';
            $this->assertStringContainsString('<section', $content, $type . '/' . $variant);

            if ($type !== 'login' && $type !== 'signup') {
                $this->assertStringContainsString(
                    'section--bg-{{style_bg}}',
                    $content,
                    'Template sans fond thème : ' . $type . '/' . $variant,
                );
            }

            $handler = $handlers->get($type);
            $resolvedVariant = $handler !== null
                ? $handler->normalizeVariant($variant)
                : $variant;

            $modules = SectionCssModules::forType($type, $resolvedVariant);
            $existingModules = array_values(array_filter(
                $modules,
                fn (string $module): bool => is_file($this->root . '/public/assets/css/' . $module),
            ));
            $this->assertNotEmpty(
                $existingModules,
                'Aucun CSS chargé pour ' . $type . '/' . $resolvedVariant,
            );
        }

        foreach (array_keys($typesSeen) as $type) {
            $this->assertFileExists(
                $cssRoot . '/' . $type . '/base.css',
                'base.css manquant pour ' . $type,
            );
        }

        $this->assertGreaterThanOrEqual(30, count($typesSeen), 'Nombre de types de blocs inattendu');
    }

    public function testSectionCssAvoidsHardcodedPaletteOutsideDecorativeCases(): void
    {
        $cssRoot = $this->root . '/public/assets/css/sections';
        $allowedDecorative = [
            '#000',
            'rgb(0 0 0',
            'rgb(15 23 42',
            'var(--shader-fallback',
        ];

        $offenders = [];
        foreach (glob($cssRoot . '/*/*.css') ?: [] as $file) {
            $lines = file($file) ?: [];
            foreach ($lines as $number => $line) {
                if (!preg_match('/#[0-9a-fA-F]{3,8}\b/', $line, $match)) {
                    continue;
                }
                if (str_contains($line, 'var(')) {
                    continue;
                }
                $decorative = false;
                foreach ($allowedDecorative as $token) {
                    if (str_contains($line, $token)) {
                        $decorative = true;
                        break;
                    }
                }
                if ($decorative) {
                    continue;
                }
                $offenders[] = str_replace($cssRoot . '/', '', $file) . ':' . ($number + 1) . ' ' . strtolower($match[0]);
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Couleurs hex hors thème détectées :\n" . implode("\n", $offenders),
        );
    }

    public function testThemeBindingsEnforcePrimarySectionColors(): void
    {
        $css = file_get_contents($this->root . '/public/assets/css/theme-bindings.css') ?: '';
        $this->assertStringContainsString('section.section--bg-primary', $css);
        $this->assertStringContainsString('section.section-stats.section-stats--stats6.section--bg-muted', $css);
        $this->assertStringContainsString('--color-text: var(--color-button-primary-text)', $css);
        $this->assertStringContainsString('--color-muted-foreground: color-mix', $css);
        $this->assertStringContainsString('[class*="__link"]', $css);
    }
}

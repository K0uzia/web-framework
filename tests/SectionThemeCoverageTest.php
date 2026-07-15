<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Dev\Sections\SectionDefaults;
use Capsule\Section\SectionCssModules;
use Capsule\Section\SectionHandlerRegistry;
use Capsule\SectionRenderer;
use Capsule\View;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class SectionThemeCoverageTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__);
    }

    public function testPrimaryBackgroundScopesSemanticTokensInBindings(): void
    {
        $css = file_get_contents($this->root . '/public/assets/css/theme-bindings.css') ?: '';

        $this->assertStringContainsString('--color-text: var(--color-button-primary-text)', $css);
        $this->assertStringContainsString('--color-muted-foreground: color-mix', $css);
        $this->assertStringContainsString(
            '.site-main section[class*="section-"]:not(.section--bg-primary)',
            $css,
        );
        $this->assertStringContainsString('[class*="__card--"]', $css);
        $this->assertStringNotContainsString('[class*="__card"],', $css);
        $this->assertStringNotContainsString('[class*="__card"]', str_replace('[class*="__card--"]', '', $css));
    }

    public function testEachSectionTypeRendersAllBackgroundOptions(): void
    {
        $renderer = new SectionRenderer(
            new View($this->root . '/resources/layouts', $this->root . '/resources/partials', $this->root . '/resources/sections'),
            $this->root . '/resources/sections',
            false,
            new SectionHandlerRegistry(),
        );

        $htmlRoot = $this->root . '/resources/sections';
        $backgrounds = ['background', 'muted', 'primary'];

        foreach (glob($htmlRoot . '/*/*.html') ?: [] as $htmlPath) {
            $type = basename(dirname($htmlPath));
            $variant = basename($htmlPath, '.html');
            if ($type === '_shared' || str_starts_with($variant, '_')) {
                continue;
            }

            if ($type === 'login' || $type === 'signup') {
                continue;
            }

            $handler = (new SectionHandlerRegistry())->get($type);
            $resolvedVariant = $handler !== null
                ? $handler->normalizeVariant($variant)
                : $variant;

            foreach ($backgrounds as $bg) {
                $section = [
                    'id' => 'theme-coverage-' . $type . '-' . $resolvedVariant,
                    'type' => $type,
                    'variant' => $resolvedVariant,
                    'visible' => true,
                    'content' => SectionDefaults::content($type, $resolvedVariant),
                    'style' => array_merge(SectionDefaults::style($type), ['bg' => $bg]),
                ];

                $html = $renderer->renderOne($section);
                $this->assertNotSame(
                    '',
                    trim($html),
                    'Rendu vide pour ' . $type . '/' . $resolvedVariant,
                );
                $this->assertStringContainsString(
                    'section--bg-' . $bg,
                    $html,
                    'Fond thème absent pour ' . $type . '/' . $resolvedVariant . ' (' . $bg . ')',
                );
            }
        }
    }

    public function testStatsBlockUsesThemeClassesForAllVariantsAndBackgrounds(): void
    {
        $renderer = new SectionRenderer(
            new View($this->root . '/resources/layouts', $this->root . '/resources/partials', $this->root . '/resources/sections'),
            $this->root . '/resources/sections',
            false,
            new SectionHandlerRegistry(),
        );

        foreach (['stats6', 'stats8'] as $variant) {
            foreach (['background', 'muted', 'primary'] as $bg) {
                $section = [
                    'id' => 'stats-theme-' . $variant . '-' . $bg,
                    'type' => 'stats',
                    'variant' => $variant,
                    'visible' => true,
                    'content' => SectionDefaults::content('stats', $variant),
                    'style' => array_merge(SectionDefaults::style('stats'), ['bg' => $bg]),
                ];

                $html = $renderer->renderOne($section);

                $this->assertStringContainsString('section-stats--' . $variant, $html);
                $this->assertStringContainsString('section--bg-' . $bg, $html);
                $this->assertStringContainsString('section-stats__value--' . $variant, $html);
                $this->assertStringContainsString('section-stats__label--' . $variant, $html);

                if ($variant === 'stats6') {
                    $this->assertStringContainsString('section-button', $html);
                }
            }
        }
    }

    public function testEachHandlerVariantHasThemeReadyCssModules(): void
    {
        $handlers = new SectionHandlerRegistry();
        $cssRoot = $this->root . '/public/assets/css';

        foreach (glob($this->root . '/resources/sections/*/*.html') ?: [] as $htmlPath) {
            $type = basename(dirname($htmlPath));
            $variant = basename($htmlPath, '.html');
            if ($type === '_shared' || str_starts_with($variant, '_')) {
                continue;
            }

            $handler = $handlers->get($type);
            if ($handler === null) {
                continue;
            }

            $resolvedVariant = $handler->normalizeVariant($variant);
            $modules = SectionCssModules::forType($type, $resolvedVariant);
            $existing = array_values(array_filter(
                $modules,
                static fn (string $module): bool => is_file($cssRoot . '/' . $module),
            ));

            $this->assertNotEmpty($existing, 'CSS manquant pour ' . $type . '/' . $resolvedVariant);

            foreach ($existing as $module) {
                $this->assertFileDoesNotContainLegacyStatsCss($type, $resolvedVariant, $module);
            }
        }
    }

    public function testSectionColorPropertiesUseThemeTokensOrAllowedDecorativeValues(): void
    {
        $cssRoot = $this->root . '/public/assets/css/sections';
        $props = ['color', 'background', 'background-color', 'border-color', 'fill', 'stroke'];
        $skipValues = ['transparent', 'none', 'inherit', 'currentcolor', 'unset', 'initial', 'auto'];
        $allowedGradientFiles = [
            'features/feature239.css',
            'gallery/gallery4.css',
            'hero/hero45.css',
            'services/services12.css',
        ];

        $offenders = [];
        foreach (glob($cssRoot . '/**/*.css') ?: [] as $file) {
            $rel = str_replace($cssRoot . '/', '', $file);
            foreach (file($file) ?: [] as $number => $line) {
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '/*')) {
                    continue;
                }

                foreach ($props as $prop) {
                    if (!preg_match('/\b' . preg_quote($prop, '/') . '\s*:\s*([^;]+)/', $trimmed, $match)) {
                        continue;
                    }

                    $value = trim($match[1]);
                    if (str_contains($value, 'var(')) {
                        continue;
                    }

                    $skip = false;
                    foreach ($skipValues as $token) {
                        if (strcasecmp($value, $token) === 0) {
                            $skip = true;
                            break;
                        }
                    }
                    if ($skip) {
                        continue;
                    }

                    if (preg_match('/^(linear|radial|conic)-gradient/', $value)) {
                        if (!in_array($rel, $allowedGradientFiles, true)) {
                            $offenders[] = $rel . ':' . ($number + 1) . ' gradient ' . $prop;
                        }
                        continue;
                    }

                    $offenders[] = $rel . ':' . ($number + 1) . ' ' . $prop . ': ' . $value;
                }
            }
        }

        $this->assertSame([], $offenders, "Couleurs hors tokens thème :\n" . implode("\n", $offenders));
    }

    private function assertFileDoesNotContainLegacyStatsCss(string $type, string $variant, string $module): void
    {
        if ($type !== 'stats') {
            return;
        }

        $this->assertStringNotContainsString(
            'sections/stats/row.css',
            $module,
            'Variante stats legacy encore référencée : ' . $variant,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/sections\/stats\/(row|grid|centered|cards|grid-\d)\.css/',
            $module,
            'CSS legacy stats pour ' . $variant,
        );
    }

    public function testStats6BackgroundUsesThemeTokensInBindings(): void
    {
        $css = file_get_contents($this->root . '/public/assets/css/theme-bindings.css') ?: '';

        $this->assertStringContainsString(
            'section.section-stats.section-stats--stats6.section--bg-primary',
            $css,
        );
        $this->assertStringContainsString('var(--color-primary) !important', $css);
        $this->assertStringContainsString(
            'section.section-stats.section-stats--stats6.section--bg-muted',
            $css,
        );
        $this->assertStringContainsString('color-mix(in srgb, var(--color-primary)', $css);
    }
}

<?php

declare(strict_types=1);

namespace Capsule\Section\Feature;

use Capsule\SectionAssets;

/**
 * Styles et variantes du bloc Features (conversion shadcnblocks).
 */
final class FeatureStyle
{
    /** @var list<string> */
    public const VISUAL_VARIANTS = [
        'feature1', 'feature2', 'feature3', 'feature13', 'feature15', 'feature16', 'feature17',
        'feature42', 'feature43', 'feature51', 'feature72', 'feature73', 'feature74',
        'feature166', 'feature197', 'feature239',
    ];

    /** @var array<string, string> */
    private const LEGACY_VARIANT_MAP = [
        'grid-2' => 'feature13',
        'grid-3' => 'feature3',
        'grid-4' => 'feature15',
        'bento' => 'feature166',
        'list' => 'feature17',
    ];

    public static function normalizeVariant(string $variant): string
    {
        if (in_array($variant, SectionAssets::featureVariantIds(), true)) {
            return $variant;
        }

        return self::LEGACY_VARIANT_MAP[$variant] ?? $variant;
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(string $variant): array
    {
        return [
            'bg' => 'background',
            'padding' => 'xl',
        ];
    }

    /**
     * @param array<string, mixed> $style
     *
     * @return array<string, string>
     */
    public static function resolve(array $style, string $variant): array
    {
        $defaults = self::defaults($variant);
        $resolved = $defaults;
        foreach ($style as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }
            $str = trim((string) $value);
            if ($str !== '') {
                $resolved[(string) $key] = $str;
            }
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed> $style
     */
    public static function modifierClasses(array $style, string $variant): string
    {
        return '';
    }
}

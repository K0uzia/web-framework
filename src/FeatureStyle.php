<?php

declare(strict_types=1);

namespace Capsule;

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

    public static function normalizeVariant(string $variant): string
    {
        if (in_array($variant, SectionAssets::featureVariantIds(), true)) {
            return $variant;
        }

        return $variant;
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

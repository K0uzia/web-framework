<?php

declare(strict_types=1);

namespace Capsule\Section\Stats;

use Capsule\SectionAssets;

/**
 * Styles et variantes du bloc Chiffres clés (conversion shadcnblocks).
 */
final class StatsStyle
{
    /** @var list<string> */
    public const VISUAL_VARIANTS = [
        'stats6',
        'stats8',
    ];

    /** @var array<string, string> */
    private const LEGACY_VARIANT_MAP = [
        'row' => 'stats6',
        'centered' => 'stats8',
        'grid' => 'stats6',
    ];

    public static function normalizeVariant(string $variant): string
    {
        if (in_array($variant, SectionAssets::statsVariantIds(), true)) {
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
            'bg' => $variant === 'stats6' ? 'muted' : 'background',
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
}

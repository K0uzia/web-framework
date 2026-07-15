<?php

declare(strict_types=1);

namespace Capsule\Section\Industry;

use Capsule\SectionAssets;

/**
 * Styles et variantes du bloc Secteurs (conversion shadcnblocks).
 */
final class IndustryStyle
{
    /** @var list<string> */
    public const VISUAL_VARIANTS = [
        'industries1',
        'industries2',
    ];

    public static function normalizeVariant(string $variant): string
    {
        if (in_array($variant, SectionAssets::industryVariantIds(), true)) {
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
            'padding' => $variant === 'industries1' ? 'lg' : 'xl',
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

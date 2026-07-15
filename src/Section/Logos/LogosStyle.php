<?php

declare(strict_types=1);

namespace Capsule\Section\Logos;

use Capsule\SectionAssets;

/**
 * Styles et variantes du bloc Logos (conversion shadcnblocks).
 */
final class LogosStyle
{
    /** @var list<string> */
    public const VISUAL_VARIANTS = [
        'logos3',
        'logos8',
        'logos18',
        'logos19',
    ];

    public static function normalizeVariant(string $variant): string
    {
        if (in_array($variant, SectionAssets::logosVariantIds(), true)) {
            return $variant;
        }

        return $variant;
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(string $variant): array
    {
        $padding = match ($variant) {
            'logos18' => 'md',
            'logos19' => 'lg',
            default => 'xl',
        };

        return [
            'bg' => 'background',
            'padding' => $padding,
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

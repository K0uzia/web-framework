<?php

declare(strict_types=1);

namespace Capsule\Section\Team;

use Capsule\SectionAssets;

/**
 * Styles et variantes du bloc Équipe (conversion shadcnblocks).
 */
final class TeamStyle
{
    /** @var list<string> */
    public const VISUAL_VARIANTS = [
        'team1',
        'team2',
    ];

    public static function normalizeVariant(string $variant): string
    {
        if (in_array($variant, SectionAssets::teamVariantIds(), true)) {
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
}

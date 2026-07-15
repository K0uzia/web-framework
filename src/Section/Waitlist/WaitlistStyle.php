<?php

declare(strict_types=1);

namespace Capsule\Section\Waitlist;

use Capsule\SectionAssets;

/**
 * Styles et variantes du bloc Liste d'attente (conversion shadcnblocks waitlist1).
 */
final class WaitlistStyle
{
    /** @var list<string> */
    public const VISUAL_VARIANTS = [
        'waitlist1',
    ];

    public static function normalizeVariant(string $variant): string
    {
        if (in_array($variant, SectionAssets::waitlistVariantIds(), true)) {
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

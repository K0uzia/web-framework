<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Styles et variantes du bloc Appel à l'action (conversion shadcnblocks).
 */
final class CtaStyle
{
    /** @var list<string> */
    public const VISUAL_VARIANTS = [
        'cta4',
        'cta10',
        'cta11',
        'cta13',
        'cta34',
        'cta35',
        'cta36',
        'cta37',
        'cta38',
    ];

    public static function normalizeVariant(string $variant): string
    {
        if (in_array($variant, SectionAssets::ctaVariantIds(), true)) {
            return $variant;
        }

        return $variant;
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(string $variant): array
    {
        $padding = $variant === 'cta10' ? 'lg' : 'xl';

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

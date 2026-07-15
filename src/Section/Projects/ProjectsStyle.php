<?php

declare(strict_types=1);

namespace Capsule\Section\Projects;

use Capsule\SectionAssets;

/**
 * Styles et variantes du bloc Projets (conversion shadcnblocks).
 */
final class ProjectsStyle
{
    /** @var list<string> */
    public const VISUAL_VARIANTS = [
        'projects5',
    ];

    public static function normalizeVariant(string $variant): string
    {
        if (in_array($variant, SectionAssets::projectsVariantIds(), true)) {
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

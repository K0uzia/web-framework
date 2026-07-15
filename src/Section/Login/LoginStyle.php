<?php

declare(strict_types=1);

namespace Capsule\Section\Login;

/**
 * Styles et variantes du bloc Connexion (conversion shadcnblocks login1, login2).
 */
final class LoginStyle
{
    /** @var list<string> */
    public const VISUAL_VARIANTS = [
        'login1',
        'login2',
    ];

    public static function normalizeVariant(string $variant): string
    {
        return in_array($variant, self::VISUAL_VARIANTS, true) ? $variant : 'login1';
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(string $variant): array
    {
        return [
            'bg' => 'muted',
            'padding' => 'none',
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

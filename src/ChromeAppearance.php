<?php

declare(strict_types=1);

namespace Capsule;

final class ChromeAppearance
{
    /** @var list<string> */
    public const CHROME_BG_TOKENS = ['background', 'surface', 'muted', 'primary'];

    /** @var list<string> */
    public const FOOTER_BG_TOKENS = self::CHROME_BG_TOKENS;

    /** @var list<string> */
    public const HEADER_BG_TOKENS = self::CHROME_BG_TOKENS;

    /**
     * @param array<string, mixed> $variant
     */
    public static function showBorder(array $variant): bool
    {
        $appearance = is_array($variant['appearance'] ?? null) ? $variant['appearance'] : [];

        return ($appearance['show_border'] ?? true) !== false;
    }

    /**
     * @param array<string, mixed> $variant
     */
    public static function footerBgToken(array $variant): string
    {
        return self::bgToken($variant, self::FOOTER_BG_TOKENS);
    }

    public static function headerBgToken(array $variant): string
    {
        return self::bgToken($variant, self::HEADER_BG_TOKENS);
    }

    public static function normalizeFooterBg(string $value): string
    {
        return self::normalizeBg($value, self::FOOTER_BG_TOKENS);
    }

    public static function normalizeHeaderBg(string $value): string
    {
        return self::normalizeBg($value, self::HEADER_BG_TOKENS);
    }

    /**
     * @param list<string> $allowed
     */
    private static function bgToken(array $variant, array $allowed): string
    {
        $appearance = is_array($variant['appearance'] ?? null) ? $variant['appearance'] : [];
        $bg = trim((string) ($appearance['bg'] ?? 'theme'));

        if ($bg === 'theme') {
            return 'theme';
        }

        return in_array($bg, $allowed, true) ? $bg : 'theme';
    }

    /**
     * @param list<string> $allowed
     */
    private static function normalizeBg(string $value, array $allowed): string
    {
        $value = trim($value);

        return in_array($value, $allowed, true) ? $value : 'theme';
    }

    /**
     * @param array<string, mixed> $variant
     */
    public static function headerClassModifiers(array $variant): string
    {
        $classes = [];
        if (!self::showBorder($variant)) {
            $classes[] = 'site-header--no-border';
        }
        $bg = self::headerBgToken($variant);
        if ($bg !== 'theme') {
            $classes[] = 'site-header--bg-' . $bg;
        }

        return $classes !== [] ? ' ' . implode(' ', $classes) : '';
    }

    /**
     * @param array<string, mixed> $variant
     */
    public static function footerClassModifiers(array $variant): string
    {
        $classes = [];
        if (!self::showBorder($variant)) {
            $classes[] = 'site-footer--no-border';
        }
        $bg = self::footerBgToken($variant);
        if ($bg !== 'theme') {
            $classes[] = 'site-footer--bg-' . $bg;
        }

        return $classes !== [] ? ' ' . implode(' ', $classes) : '';
    }
}

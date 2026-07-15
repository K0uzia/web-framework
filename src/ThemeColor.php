<?php

declare(strict_types=1);

namespace Capsule;

final class ThemeColor
{
    public const TRANSPARENT = 'transparent';

    /**
     * Normalise une couleur thème (#rrggbb ou transparent).
     */
    public static function normalize(string $value, string $fallback = '#000000'): string
    {
        $value = trim($value);
        if ($value === '') {
            return self::expandShortHex($fallback);
        }

        if (self::isTransparent($value)) {
            return self::TRANSPARENT;
        }

        if (preg_match('/^#([0-9a-fA-F]{3})$/', $value, $short) === 1) {
            $hex = $short[1];

            return '#' . strtolower($hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2]);
        }

        if (preg_match('/^#([0-9a-fA-F]{6})$/', $value) === 1) {
            return strtolower($value);
        }

        if (preg_match(
            '/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)(?:\s*,\s*([\d.]+))?\s*\)$/i',
            $value,
            $rgb,
        ) === 1) {
            if (isset($rgb[4]) && (float) $rgb[4] === 0.0) {
                return self::TRANSPARENT;
            }

            return sprintf(
                '#%02x%02x%02x',
                (int) $rgb[1],
                (int) $rgb[2],
                (int) $rgb[3],
            );
        }

        return self::expandShortHex($fallback);
    }

    public static function isTransparent(string $value): bool
    {
        return strtolower(trim($value)) === self::TRANSPARENT;
    }

    /**
     * Valeur affichée dans <input type="color"> (hex uniquement).
     */
    public static function pickerHex(string $value, string $fallback = '#ffffff'): string
    {
        if (self::isTransparent($value)) {
            $pickerFallback = self::isTransparent($fallback) ? '#ffffff' : $fallback;

            return self::expandShortHex($pickerFallback);
        }

        return self::normalize($value, $fallback);
    }

    private static function expandShortHex(string $value): string
    {
        if (self::isTransparent($value)) {
            return self::TRANSPARENT;
        }

        $value = trim($value);
        if (preg_match('/^#([0-9a-fA-F]{6})$/', $value) === 1) {
            return strtolower($value);
        }
        if (preg_match('/^#([0-9a-fA-F]{3})$/', $value, $short) === 1) {
            $hex = $short[1];

            return '#' . strtolower($hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2]);
        }

        return '#000000';
    }
}

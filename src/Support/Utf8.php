<?php

declare(strict_types=1);

namespace Capsule\Support;

use Transliterator;

/**
 * Opérations UTF-8 sans dépendre de l'extension mbstring en production.
 */
final class Utf8
{
    public static function strtolower(string $string): string
    {
        if (\function_exists('mb_strtolower')) {
            return \mb_strtolower($string, 'UTF-8');
        }

        return self::transliterateCase($string, 'Any-Lower') ?? strtolower($string);
    }

    public static function strtoupper(string $string): string
    {
        if (\function_exists('mb_strtoupper')) {
            return \mb_strtoupper($string, 'UTF-8');
        }

        return self::transliterateCase($string, 'Any-Upper') ?? strtoupper($string);
    }

    public static function substr(string $string, int $start, ?int $length = null): string
    {
        if (\function_exists('mb_substr')) {
            return \mb_substr($string, $start, $length, 'UTF-8');
        }

        if (!preg_match_all('/./us', $string, $matches)) {
            return '';
        }

        $chars = $matches[0];
        if ($start < 0) {
            $start = max(0, count($chars) + $start);
        }

        $slice = array_slice($chars, $start, $length ?? PHP_INT_MAX);

        return implode('', $slice);
    }

    private static function transliterateCase(string $string, string $rule): ?string
    {
        if (!class_exists(Transliterator::class)) {
            return null;
        }

        $transliterator = Transliterator::create($rule);
        if ($transliterator === null) {
            return null;
        }

        return $transliterator->transliterate($string);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Dev;

final class SlugCodec
{
    public static function decode(string $slug): string
    {
        return $slug === '_' ? '' : rawurldecode($slug);
    }

    public static function encode(string $slug): string
    {
        return $slug === '' ? '_' : rawurlencode($slug);
    }
}

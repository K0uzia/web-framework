<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Visuels d'exemple hébergés localement (public/assets/stock).
 * Provenance : photos Unsplash, téléchargées pour respecter la CSP img-src 'self'.
 */
final class StockImages
{
    public const BASE = '/assets/stock';

    /** @var list<string> */
    private const HERO = [
        '01-bureau.jpg',
        '02-equipe.jpg',
        '06-reunion.jpg',
    ];

    /** @var list<string> */
    private const PRODUCT = [
        '03-tableau-de-bord.jpg',
        '08-portable.jpg',
        '03-tableau-de-bord.jpg',
    ];

    /** @var list<string> */
    private const GALLERY = [
        '04-architecture.jpg',
        '07-abstrait.jpg',
        '05-open-space.jpg',
        '02-equipe.jpg',
        '01-bureau.jpg',
        '06-reunion.jpg',
    ];

    /** @var list<string> */
    private const PROJECT = [
        '05-open-space.jpg',
        '06-reunion.jpg',
        '04-architecture.jpg',
        '08-portable.jpg',
        '02-equipe.jpg',
        '01-bureau.jpg',
    ];

    public static function hero(int $index = 0): string
    {
        return self::fromList(self::HERO, $index);
    }

    public static function product(int $index = 0): string
    {
        return self::fromList(self::PRODUCT, $index);
    }

    public static function gallery(int $index = 0): string
    {
        return self::fromList(self::GALLERY, $index);
    }

    public static function project(int $index = 0): string
    {
        return self::fromList(self::PROJECT, $index);
    }

    public static function blog(int $index = 0): string
    {
        return self::fromList(self::GALLERY, $index);
    }

    public static function sectionUsesHeroImage(string $type, string $variant): bool
    {
        return match ($type) {
            'hero' => in_array($variant, ['split', 'split-left', 'image-below', 'video'], true),
            'demo' => $variant === 'split',
            'ui-embed' => true,
            default => false,
        };
    }

    public static function sectionHeroFallback(string $type, int $index = 0): string
    {
        return match ($type) {
            'demo', 'ui-embed' => self::product($index),
            'features' => self::product($index),
            default => self::hero($index),
        };
    }

    public static function itemFallback(string $type, int $index): string
    {
        return match ($type) {
            'projects' => self::project($index),
            'blog' => self::blog($index),
            'features' => self::product($index),
            'gallery' => self::gallery($index),
            default => self::gallery($index),
        };
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $files = array_unique(array_merge(self::HERO, self::PRODUCT, self::GALLERY, self::PROJECT));
        sort($files);

        return array_map(static fn (string $file): string => self::path($file), $files);
    }

    public static function path(string $filename): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename) ?? $filename;

        return self::BASE . '/' . $safe;
    }

    /**
     * Remplace une URL Unsplash (ou vide) par un visuel local pour respecter la CSP.
     */
    public static function resolve(string $url, callable $fallback): string
    {
        $url = trim($url);
        if ($url === '' || str_contains($url, 'images.unsplash.com')) {
            return $fallback();
        }

        return $url;
    }

    /**
     * @param list<string> $files
     */
    private static function fromList(array $files, int $index): string
    {
        if ($files === []) {
            return '';
        }

        return self::path($files[max(0, $index) % count($files)]);
    }
}

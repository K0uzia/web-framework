<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Chemins publics des médias liés aux sections (images, vidéos, motifs).
 * Convention : /assets/sections/{type}/{variant}/… ou /assets/sections/{type}/_shared/…
 */
final class SectionAssets
{
    public const BASE = '/assets/sections';

    public static function url(string $type, string $variant, string $filename): string
    {
        $safeType = self::safeSegment($type);
        $safeVariant = self::safeSegment($variant);
        $safeFile = self::safeFilename($filename);

        return self::BASE . '/' . $safeType . '/' . $safeVariant . '/' . $safeFile;
    }

    public static function shared(string $type, string $filename): string
    {
        $safeType = self::safeSegment($type);
        $safeFile = self::safeFilename($filename);

        return self::BASE . '/' . $safeType . '/_shared/' . $safeFile;
    }

    public static function resolve(string $url, string $fallback): string
    {
        $url = trim($url);
        if ($url === ''
            || str_contains($url, 'images.unsplash.com')
            || str_contains($url, 'cloudfront.net')) {
            return $fallback;
        }

        return $url;
    }

    /**
     * @return list<string>
     */
    public static function heroVariantIds(): array
    {
        return [
            'hero1', 'hero3', 'hero7', 'hero12', 'hero34', 'hero45', 'hero47',
            'hero67', 'hero78', 'hero115', 'hero195', 'hero206', 'hero243',
        ];
    }

    /**
     * @return list<string>
     */
    public static function featureVariantIds(): array
    {
        return FeatureStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function integrationVariantIds(): array
    {
        return IntegrationStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function pricingVariantIds(): array
    {
        return PricingStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function contactVariantIds(): array
    {
        return ContactStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function testimonialVariantIds(): array
    {
        return TestimonialStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function galleryVariantIds(): array
    {
        return GalleryStyle::VISUAL_VARIANTS;
    }

    private static function safeSegment(string $value): string
    {
        $safe = preg_replace('/[^a-z0-9_-]/', '', strtolower($value)) ?? '';

        return $safe !== '' ? $safe : 'section';
    }

    private static function safeFilename(string $filename): string
    {
        $filename = str_replace('\\', '/', trim($filename));
        $parts = array_values(array_filter(explode('/', $filename), static fn (string $p): bool => $p !== ''));
        $safeParts = [];
        foreach ($parts as $part) {
            $safe = preg_replace('/[^a-zA-Z0-9._-]/', '', $part) ?? '';
            if ($safe !== '') {
                $safeParts[] = $safe;
            }
        }

        return $safeParts !== [] ? implode('/', $safeParts) : 'asset.bin';
    }
}

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
        return HeroStyle::VISUAL_VARIANTS;
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

    /**
     * @return list<string>
     */
    public static function blogVariantIds(): array
    {
        return BlogStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function changelogVariantIds(): array
    {
        return ChangelogStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function processVariantIds(): array
    {
        return ProcessStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function listVariantIds(): array
    {
        return ListStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function industryVariantIds(): array
    {
        return IndustryStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function rateCardVariantIds(): array
    {
        return RateCardStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function downloadVariantIds(): array
    {
        return DownloadStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function teamVariantIds(): array
    {
        return TeamStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function projectsVariantIds(): array
    {
        return ProjectsStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function timelineVariantIds(): array
    {
        return TimelineStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function logosVariantIds(): array
    {
        return LogosStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function servicesVariantIds(): array
    {
        return ServicesStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function compareVariantIds(): array
    {
        return CompareStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function ctaVariantIds(): array
    {
        return CtaStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function awardsVariantIds(): array
    {
        return AwardsStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function communityVariantIds(): array
    {
        return CommunityStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function statsVariantIds(): array
    {
        return StatsStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function careersVariantIds(): array
    {
        return CareersStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function faqVariantIds(): array
    {
        return FaqStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function codeVariantIds(): array
    {
        return CodeStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function complianceVariantIds(): array
    {
        return ComplianceStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function caseStudyVariantIds(): array
    {
        return CaseStudyStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function demoVariantIds(): array
    {
        return DemoStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function experienceVariantIds(): array
    {
        return ExperienceStyle::VISUAL_VARIANTS;
    }

    /**
     * @return list<string>
     */
    public static function waitlistVariantIds(): array
    {
        return WaitlistStyle::VISUAL_VARIANTS;
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

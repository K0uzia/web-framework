<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Rendu HTML spécifique aux variantes compliance (conversion des blocs React).
 */
final class ComplianceVariantRenderer
{
    private const MAX_LOGOS = 6;
    private const MAX_FEATURES = 8;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        if ($variant === 'compliance1') {
            $data['compliance_logos_html'] = self::logosHtml($content);
            $data['compliance_features_html'] = self::featuresHtml($content);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function logosHtml(array $content): string
    {
        $html = '';
        foreach (self::logos($content) as $logo) {
            $html .= self::logoImgHtml(
                (string) ($logo['url'] ?? ''),
                (string) ($logo['label'] ?? ''),
            );
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function featuresHtml(array $content): string
    {
        $features = self::features($content);
        $html = '';
        foreach ($features as $feature) {
            $title = trim((string) ($feature['title'] ?? ''));
            $description = trim((string) ($feature['text'] ?? ''));
            if ($title === '') {
                continue;
            }
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<div class="section-compliance__feature--compliance1">'
                . '<div class="section-compliance__feature-body--compliance1">'
                . '<h3 class="section-compliance__feature-title--compliance1">' . $safeTitle . '</h3>';
            if ($description !== '') {
                $html .= '<p class="section-compliance__feature-text--compliance1">' . $safeDescription . '</p>';
            }
            $html .= '</div>'
                . self::badgeImgHtml(
                    (string) ($feature['url'] ?? ''),
                    (string) ($feature['label'] ?? $title),
                )
                . '</div>';
        }

        return $html;
    }

    private static function logoImgHtml(string $url, string $alt): string
    {
        $fallback = SectionAssets::shared('compliance', 'badge-placeholder.svg');
        $resolved = SectionAssets::resolve($url, $fallback);
        $safeUrl = htmlspecialchars($resolved, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeAlt = htmlspecialchars($alt !== '' ? $alt : 'Certification', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<img class="section-compliance__logo--compliance1" src="'
            . $safeUrl . '" alt="' . $safeAlt . '" height="112" loading="lazy" decoding="async" />';
    }

    private static function badgeImgHtml(string $url, string $alt): string
    {
        $fallback = SectionAssets::shared('compliance', 'badge-placeholder.svg');
        $resolved = SectionAssets::resolve($url, $fallback);
        $safeUrl = htmlspecialchars($resolved, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeAlt = htmlspecialchars($alt !== '' ? $alt : 'Certification', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<img class="section-compliance__feature-badge--compliance1" src="'
            . $safeUrl . '" alt="' . $safeAlt . '" width="128" height="128" loading="lazy" decoding="async" />';
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    private static function logos(array $content): array
    {
        $raw = is_array($content['logos'] ?? null) ? $content['logos'] : [];

        return array_slice(array_values(array_filter($raw, 'is_array')), 0, self::MAX_LOGOS);
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    private static function features(array $content): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];

        return array_slice(array_values(array_filter($raw, 'is_array')), 0, self::MAX_FEATURES);
    }
}

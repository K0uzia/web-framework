<?php

declare(strict_types=1);

namespace Capsule\Section\Cta;

use Capsule\Section\Support\SectionButtonStyle;
use Capsule\SectionAssets;
use Capsule\FontAwesomeIcon;

/**
 * Rendu HTML spécifique aux variantes CTA (conversion des blocs React).
 */
final class CtaVariantRenderer
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['cta_features_html'] = '';
        $data['cta_image_html'] = '';
        $data['cta_badge_html'] = '';

        if ($variant === 'cta4') {
            $data['cta_features_html'] = self::featuresHtml($content);
            $data['buttons_html'] = self::primaryButtonWithArrow($content) ?: ($data['buttons_html'] ?? '');
        }

        if ($variant === 'cta11') {
            $data['cta_image_html'] = self::imageHtml($content);
            $data['cta_badge_html'] = self::badgeHtml($content);
            $data['buttons_html'] = self::primaryButtonWithArrow($content) ?: ($data['buttons_html'] ?? '');
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function featuresHtml(array $content): string
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $html = '<ul class="section-cta__features--cta4">';
        foreach (array_slice($raw, 0, 8) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $label = trim((string) ($item['title'] ?? ''));
            if ($label === '') {
                continue;
            }
            $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<li class="section-cta__feature--cta4">'
                . '<i class="fa-solid fa-check section-cta__feature-icon--cta4" aria-hidden="true"></i>'
                . '<span>' . $safeLabel . '</span>'
                . '</li>';
        }

        return $html . '</ul>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function imageHtml(array $content): string
    {
        $url = SectionAssets::resolve(
            (string) ($content['image_url'] ?? ''),
            SectionAssets::shared('hero', 'saas-hero-1-16x9.png'),
        );
        $alt = trim((string) ($content['image_alt'] ?? $content['title'] ?? 'Illustration'));
        $safeUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeAlt = htmlspecialchars($alt !== '' ? $alt : 'Illustration', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<img class="section-cta__image--cta11" src="' . $safeUrl . '" alt="' . $safeAlt
            . '" width="640" height="360" loading="lazy" decoding="async" />';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function badgeHtml(array $content): string
    {
        $raw = trim((string) ($content['icon'] ?? $content['tagline'] ?? ''));
        if ($raw === '') {
            $raw = 'fa-wand-magic-sparkles';
        }
        $class = FontAwesomeIcon::solidClass(FontAwesomeIcon::glyph($raw));

        return '<span class="section-cta__badge--cta11" aria-hidden="true">'
            . '<i class="' . htmlspecialchars($class, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"></i>'
            . '</span>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function primaryButtonWithArrow(array $content): string
    {
        $buttons = $content['buttons'] ?? null;
        if (!is_array($buttons)) {
            $label = trim((string) ($content['cta_label'] ?? $content['button_label'] ?? ''));
            $href = trim((string) ($content['cta_href'] ?? $content['button_href'] ?? ''));
            if ($label === '' || $href === '') {
                return '';
            }

            return self::buttonHtml($label, $href, 'primary', true);
        }

        foreach ($buttons as $button) {
            if (!is_array($button)) {
                continue;
            }
            if (SectionButtonStyle::normalize((string) ($button['style'] ?? 'primary')) !== 'primary') {
                continue;
            }
            $label = trim((string) ($button['label'] ?? ''));
            $href = trim((string) ($button['href'] ?? ''));
            if ($label === '' || $href === '') {
                continue;
            }

            return self::buttonHtml($label, $href, 'primary', true);
        }

        return '';
    }

    private static function buttonHtml(string $label, string $href, string $style, bool $withArrow): string
    {
        $arrow = $withArrow ? ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>' : '';
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeHref = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $class = SectionButtonStyle::sectionClass($style);

        return '<a class="section-button ' . $class . '" href="' . $safeHref . '">'
            . $safeLabel . $arrow . '</a>';
    }
}

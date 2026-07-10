<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Section\SectionItemsTrait;

/**
 * Rendu HTML spécifique aux variantes rate-card (conversion des blocs React).
 */
final class RateCardVariantRenderer
{
    use SectionItemsTrait;

    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'rate-card2' => 4,
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['rate_card_plans_html'] = match ($variant) {
            default => self::plansRateCard2Html($content),
        };

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function plansRateCard2Html(array $content): string
    {
        $featuresHeading = trim((string) ($content['features_heading'] ?? ''));
        if ($featuresHeading === '') {
            $featuresHeading = 'Inclus :';
        }
        $safeFeaturesHeading = htmlspecialchars($featuresHeading, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $defaultCta = trim((string) ($content['cta_label'] ?? ''));
        if ($defaultCta === '') {
            $defaultCta = 'Commencer';
        }

        $html = '';
        foreach (self::itemsFromContent($content, self::MAX_ITEMS['rate-card2']) as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $description = trim((string) ($item['text'] ?? ''));
            $price = self::formatPrice((string) ($item['price'] ?? ''));
            $period = trim((string) ($item['period'] ?? ''));
            $href = self::hrefFromItem((string) ($item['href'] ?? '#'));
            $ctaLabel = trim((string) ($item['cta_label'] ?? ''));
            if ($ctaLabel === '') {
                $ctaLabel = $defaultCta;
            }

            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safePrice = htmlspecialchars($price, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safePeriod = htmlspecialchars($period, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeCtaLabel = htmlspecialchars($ctaLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $featuresHtml = '<li class="section-rate-card__features-heading--rate-card2">' . $safeFeaturesHeading . '</li>';
            foreach (self::parseFeatures((string) ($item['features'] ?? '')) as $feature) {
                $safeFeature = htmlspecialchars($feature, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $featuresHtml .= '<li class="section-rate-card__feature--rate-card2">'
                    . '<i class="fa-solid fa-bullseye" aria-hidden="true"></i>'
                    . '<span>' . $safeFeature . '</span>'
                    . '</li>';
            }

            $html .= '<article class="section-rate-card__card--rate-card2">'
                . '<div class="section-rate-card__card-top--rate-card2">'
                . '<h2 class="section-rate-card__card-title--rate-card2">' . $safeTitle . '</h2>'
                . ($description !== ''
                    ? '<p class="section-rate-card__card-desc--rate-card2">' . $safeDescription . '</p>'
                    : '')
                . '</div>'
                . '<ul class="section-rate-card__features--rate-card2">' . $featuresHtml . '</ul>'
                . '<div class="section-rate-card__footer--rate-card2">'
                . ($price !== ''
                    ? '<p class="section-rate-card__price--rate-card2">'
                    . '<span class="section-rate-card__price-value--rate-card2">' . $safePrice . '</span>'
                    . ($period !== ''
                        ? '<sup class="section-rate-card__price-period--rate-card2">' . $safePeriod . '</sup>'
                        : '')
                    . '</p>'
                    : '')
                . self::borderButtonHtml($safeCtaLabel, $href)
                . '</div>'
                . '</article>';
        }

        return $html;
    }

    private static function borderButtonHtml(string $label, string $href): string
    {
        $corners = self::cornerSvg('section-rate-card__btn-corner--rate-card2 section-rate-card__btn-corner--bl--rate-card2')
            . self::cornerSvg('section-rate-card__btn-corner--rate-card2 section-rate-card__btn-corner--br--rate-card2')
            . self::cornerSvg('section-rate-card__btn-corner--rate-card2 section-rate-card__btn-corner--tl--rate-card2')
            . self::cornerSvg('section-rate-card__btn-corner--rate-card2 section-rate-card__btn-corner--tr--rate-card2');

        return '<a class="section-rate-card__btn--rate-card2" href="' . $href . '">'
            . '<span>' . $label . '</span>'
            . $corners
            . '</a>';
    }

    private static function cornerSvg(string $class): string
    {
        return '<svg class="' . $class . '" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
            . '<path d="M0 0V12" stroke="currentColor" stroke-width="1" stroke-linecap="square"></path>'
            . '<path d="M0 12H12" stroke="currentColor" stroke-width="1" stroke-linecap="square"></path>'
            . '</svg>';
    }

    /**
     * @return list<string>
     */
    private static function parseFeatures(string $raw): array
    {
        $lines = preg_split('/\r\n|\n|\r/', $raw) ?: [];

        return array_values(array_filter(array_map('trim', $lines), static fn (string $line): bool => $line !== ''));
    }

    private static function formatPrice(string $raw): string
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return '';
        }

        return preg_replace('/\s+/u', "\u{202F}", $trimmed) ?? $trimmed;
    }
}

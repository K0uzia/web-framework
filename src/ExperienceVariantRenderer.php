<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Section\SectionItemsTrait;

/**
 * Rendu HTML spécifique aux variantes experience (parcours professionnel shadcnblocks).
 */
final class ExperienceVariantRenderer
{
    use SectionItemsTrait;

    private const SHARED_TYPE = 'experience';

    /** @var list<string> */
    private const DEFAULT_LOGOS = [
        'logos/google-icon.svg',
        'logos/microsoft-icon.svg',
        'logos/apple-icon.svg',
        'logos/netflix-icon.svg',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['experience_cv_button_html'] = self::cvButtonHtml($content);
        $data['experience_entries_html'] = self::entriesHtml($content);

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function cvButtonHtml(array $content): string
    {
        $label = trim((string) ($content['link_label'] ?? $content['button_label'] ?? ''));
        $href = trim((string) ($content['href'] ?? $content['button_href'] ?? ''));
        if ($label === '' || $href === '') {
            return '';
        }

        $safeHref = self::hrefFromItem($href);
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<a class="section-experience__cv-btn--experience1" href="' . $safeHref . '">'
            . $safeLabel
            . ' <i class="fa-solid fa-download" aria-hidden="true"></i></a>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function entriesHtml(array $content): string
    {
        $items = self::itemsFromContent($content, 12);
        $html = '';
        foreach ($items as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $period = trim((string) ($item['label'] ?? $item['date'] ?? ''));
            $description = trim((string) ($item['text'] ?? ''));
            $company = trim((string) ($item['company'] ?? $item['author'] ?? ''));
            $logo = self::imageUrlFromItem(
                (string) ($item['logo'] ?? $item['url'] ?? ''),
                (int) $index,
                self::SHARED_TYPE,
                self::DEFAULT_LOGOS[$index % count(self::DEFAULT_LOGOS)],
            );
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safePeriod = htmlspecialchars($period, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeCompany = htmlspecialchars($company, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeLogo = htmlspecialchars($logo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeLogoAlt = htmlspecialchars(
                $company !== '' ? $company : $title,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8',
            );

            $html .= '<li class="section-experience__entry--experience1">'
                . ($period !== ''
                    ? '<div class="section-experience__period--experience1">' . $safePeriod . '</div>'
                    : '<div class="section-experience__period--experience1" aria-hidden="true"></div>')
                . '<div class="section-experience__body--experience1">'
                . '<h3 class="section-experience__role--experience1">' . $safeTitle . '</h3>'
                . ($description !== ''
                    ? '<p class="section-experience__description--experience1">' . $safeDescription . '</p>'
                    : '')
                . '</div>'
                . '<div class="section-experience__company--experience1">'
                . '<img class="section-experience__logo--experience1" src="' . $safeLogo
                . '" alt="' . $safeLogoAlt . '" width="24" height="24" loading="lazy" decoding="async" />'
                . ($company !== ''
                    ? '<span class="section-experience__company-name--experience1">' . $safeCompany . '</span>'
                    : '')
                . '</div>'
                . '</li>';
        }

        return $html;
    }
}

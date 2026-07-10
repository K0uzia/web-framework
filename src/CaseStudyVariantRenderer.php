<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Section\SectionItemsTrait;

/**
 * Rendu HTML spécifique aux variantes case-study (conversion des blocs React).
 */
final class CaseStudyVariantRenderer
{
    use SectionItemsTrait;

    private const SHARED_TYPE = 'case-study';

    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'casestudies2' => 2,
        'casestudies3' => 2,
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $linkLabel = trim((string) ($content['read_more_label'] ?? ''));
        if ($linkLabel === '') {
            $linkLabel = 'Lire l\'étude de cas';
        }
        $safeLinkLabel = htmlspecialchars($linkLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $data['case_study_rows_html'] = match ($variant) {
            'casestudies2' => self::rowsCasestudies2Html($content),
            default => '',
        };
        $data['case_study_featured_html'] = match ($variant) {
            'casestudies3' => self::featuredCasestudies3Html($content, $safeLinkLabel),
            default => '',
        };
        $data['case_study_cards_html'] = match ($variant) {
            'casestudies3' => self::cardsCasestudies3Html($content, $safeLinkLabel),
            default => '',
        };

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function rowsCasestudies2Html(array $content): string
    {
        $items = self::itemsFromContent($content, self::MAX_ITEMS['casestudies2']);
        $html = '';
        foreach ($items as $index => $item) {
            $quote = trim((string) ($item['text'] ?? ''));
            if ($quote === '') {
                continue;
            }
            if ($html !== '') {
                $html .= '<hr class="section-case-study__divider--casestudies2" />';
            }
            $html .= self::rowCasestudies2Html($item, (int) $index);
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function rowCasestudies2Html(array $item, int $index): string
    {
        $quote = trim((string) ($item['text'] ?? ''));
        $author = trim((string) ($item['author'] ?? ''));
        $role = trim((string) ($item['role'] ?? ''));
        $photo = self::imageUrlFromItem(
            (string) ($item['url'] ?? ''),
            $index,
            self::SHARED_TYPE,
            $index === 0 ? 'placeholder-1.svg' : 'placeholder-2.svg',
        );
        $logo = self::imageUrlFromItem(
            (string) ($item['logo'] ?? ''),
            $index,
            self::SHARED_TYPE,
            'logos/fictional-company-logo-' . ($index + 2) . '.svg',
        );
        $safeQuote = htmlspecialchars($quote, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safePhoto = htmlspecialchars($photo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLogo = htmlspecialchars($logo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeAlt = htmlspecialchars($author !== '' ? $author : 'Portrait client', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $authorHtml = '';
        if ($author !== '' || $role !== '') {
            $authorHtml = '<div class="section-case-study__author-meta--casestudies2">';
            if ($author !== '') {
                $authorHtml .= '<p class="section-case-study__author-name--casestudies2">'
                    . htmlspecialchars($author, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
            }
            if ($role !== '') {
                $authorHtml .= '<p class="section-case-study__author-role--casestudies2">'
                    . htmlspecialchars($role, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
            }
            $authorHtml .= '</div>';
        }

        return '<div class="section-case-study__row--casestudies2">'
            . '<div class="section-case-study__story--casestudies2">'
            . '<img class="section-case-study__photo--casestudies2" src="' . $safePhoto . '" alt="' . $safeAlt
            . '" width="240" height="290" loading="lazy" decoding="async" />'
            . '<div class="section-case-study__story-body--casestudies2">'
            . '<blockquote class="section-case-study__quote--casestudies2"><p>' . $safeQuote . '</p></blockquote>'
            . '<div class="section-case-study__author--casestudies2">'
            . $authorHtml
            . '<img class="section-case-study__company-logo--casestudies2" src="' . $safeLogo
            . '" alt="" width="120" height="32" loading="lazy" decoding="async" aria-hidden="true" />'
            . '</div></div></div>'
            . self::metricsCasestudies2Html($item)
            . '</div>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function metricsCasestudies2Html(array $item): string
    {
        $metrics = [];
        foreach (['stat1', 'stat2'] as $key) {
            $value = trim((string) ($item[$key . '_value'] ?? ''));
            $label = trim((string) ($item[$key . '_label'] ?? ''));
            $text = trim((string) ($item[$key . '_text'] ?? ''));
            if ($value === '' && $label === '') {
                continue;
            }
            $metrics[] = [$value, $label, $text];
        }
        if ($metrics === []) {
            return '';
        }

        $html = '<div class="section-case-study__metrics--casestudies2">';
        foreach ($metrics as [$value, $label, $text]) {
            $html .= '<div class="section-case-study__metric--casestudies2">';
            if ($value !== '') {
                $html .= '<p class="section-case-study__metric-value--casestudies2">'
                    . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
            }
            if ($label !== '') {
                $html .= '<p class="section-case-study__metric-label--casestudies2">'
                    . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
            }
            if ($text !== '') {
                $html .= '<p class="section-case-study__metric-text--casestudies2">'
                    . htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function featuredCasestudies3Html(array $content, string $linkLabel): string
    {
        $company = trim((string) ($content['featured_company'] ?? ''));
        $title = trim((string) ($content['featured_title'] ?? ''));
        if ($company === '' && $title === '') {
            return '';
        }

        return self::cardCasestudies3Html([
            'logo' => (string) ($content['featured_logo'] ?? ''),
            'company' => $company,
            'tags' => (string) ($content['featured_tags'] ?? ''),
            'title' => $title,
            'subtitle' => (string) ($content['featured_subtitle'] ?? ''),
            'url' => (string) ($content['featured_url'] ?? ''),
            'href' => (string) ($content['featured_href'] ?? '#'),
        ], $linkLabel, true);
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function cardsCasestudies3Html(array $content, string $linkLabel): string
    {
        $items = self::itemsFromContent($content, self::MAX_ITEMS['casestudies3']);
        $html = '';
        foreach ($items as $index => $item) {
            $html .= self::cardCasestudies3Html($item, $linkLabel, false, (int) $index);
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function cardCasestudies3Html(array $item, string $linkLabel, bool $featured, int $index = 0): string
    {
        $company = trim((string) ($item['company'] ?? ''));
        $title = trim((string) ($item['title'] ?? ''));
        if ($company === '' && $title === '') {
            return '';
        }
        $tags = trim((string) ($item['tags'] ?? ''));
        $subtitle = trim((string) ($item['subtitle'] ?? ''));
        $href = self::hrefFromItem((string) ($item['href'] ?? '#'));
        $logo = self::imageUrlFromItem(
            (string) ($item['logo'] ?? ''),
            $index,
            self::SHARED_TYPE,
            'block-' . ($index + 1) . '.svg',
        );
        $safeCompany = htmlspecialchars($company, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeSubtitle = htmlspecialchars($subtitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeTags = htmlspecialchars($tags, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLogo = htmlspecialchars($logo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $class = 'section-case-study__card--casestudies3'
            . ($featured ? ' section-case-study__card--featured--casestudies3' : '')
            . (!$featured && $index === 0 ? ' section-case-study__card--first--casestudies3' : '')
            . (!$featured && $index === 1 ? ' section-case-study__card--second--casestudies3' : '');

        $brand = '<div class="section-case-study__card-brand--casestudies3">'
            . '<img class="section-case-study__card-logo--casestudies3" src="' . $safeLogo . '" alt="" width="36" height="36" loading="lazy" decoding="async" aria-hidden="true" />'
            . '<span class="section-case-study__card-company--casestudies3">' . $safeCompany . '</span>'
            . '</div>';

        $copy = '<div class="section-case-study__card-copy--casestudies3">';
        if ($tags !== '') {
            $copy .= '<span class="section-case-study__card-tags--casestudies3">' . $safeTags . '</span>';
        }
        $copy .= '<h3 class="section-case-study__card-title--casestudies3">' . $safeTitle;
        if ($subtitle !== '') {
            $copy .= ' <span class="section-case-study__card-subtitle--casestudies3">' . $safeSubtitle . '</span>';
        }
        $copy .= '</h3><span class="section-case-study__card-link--casestudies3">'
            . $linkLabel
            . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>'
            . '</div>';

        if ($featured) {
            $body = '<div class="section-case-study__card-content--casestudies3">' . $brand . $copy . '</div>';
        } else {
            $body = $brand . $copy;
        }

        if ($featured) {
            $image = self::imageUrlFromItem(
                (string) ($item['url'] ?? ''),
                0,
                self::SHARED_TYPE,
                'placeholder-1.svg',
            );
            $safeImage = htmlspecialchars($image, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $body .= '<div class="section-case-study__card-media-wrap--casestudies3">'
                . '<div class="section-case-study__card-media-frame--casestudies3">'
                . '<div class="section-case-study__card-media--casestudies3">'
                . '<img class="section-case-study__card-image--casestudies3" src="' . $safeImage
                . '" alt="" width="640" height="411" loading="lazy" decoding="async" />'
                . '</div></div></div>';
        }

        return '<a class="' . $class . '" href="' . $href . '">' . $body . '</a>';
    }
}

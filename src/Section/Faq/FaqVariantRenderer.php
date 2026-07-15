<?php

declare(strict_types=1);

namespace Capsule\Section\Faq;

/**
 * Rendu HTML spécifique aux variantes FAQ (conversion des blocs React).
 */
final class FaqVariantRenderer
{
    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'faq1' => 12,
        'faq3' => 12,
        'faq5' => 12,
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['faq_accordion_html'] = in_array($variant, ['faq1', 'faq3'], true)
            ? self::accordionHtml($content, $variant)
            : '';
        $data['faq_list_html'] = $variant === 'faq5' ? self::listFaq5Html($content) : '';
        $data['faq_badge_html'] = $variant === 'faq5' ? self::badgeHtml($content) : '';

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function accordionHtml(array $content, string $variant): string
    {
        $html = '';
        foreach (self::items($content, $variant) as $index => $item) {
            $question = trim((string) ($item['title'] ?? ''));
            $answer = trim((string) ($item['text'] ?? ''));
            if ($question === '' || $answer === '') {
                continue;
            }
            $safeQuestion = htmlspecialchars($question, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeAnswer = htmlspecialchars($answer, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<details class="section-faq__item section-faq__item--' . $variant . '">'
                . '<summary class="section-faq__question section-faq__question--' . $variant . '">'
                . $safeQuestion
                . '</summary>'
                . '<div class="section-faq__answer section-faq__answer--' . $variant . '">'
                . '<p>' . $safeAnswer . '</p>'
                . '</div>'
                . '</details>';
        }

        return $html !== ''
            ? '<div class="section-faq__accordion section-faq__accordion--' . $variant . '">' . $html . '</div>'
            : '';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function listFaq5Html(array $content): string
    {
        $html = '';
        $number = 1;
        foreach (self::items($content, 'faq5') as $item) {
            $question = trim((string) ($item['title'] ?? ''));
            $answer = trim((string) ($item['text'] ?? ''));
            if ($question === '' || $answer === '') {
                continue;
            }
            $safeQuestion = htmlspecialchars($question, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeAnswer = htmlspecialchars($answer, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<div class="section-faq__entry--faq5">'
                . '<span class="section-faq__number--faq5" aria-hidden="true">' . $number . '</span>'
                . '<div class="section-faq__entry-body--faq5">'
                . '<h3 class="section-faq__entry-question--faq5">' . $safeQuestion . '</h3>'
                . '<p class="section-faq__entry-answer--faq5">' . $safeAnswer . '</p>'
                . '</div>'
                . '</div>';
            $number++;
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function badgeHtml(array $content): string
    {
        $badge = trim((string) ($content['tagline'] ?? ''));
        if ($badge === '') {
            return '';
        }
        $safeBadge = htmlspecialchars($badge, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<span class="section-faq__badge--faq5">' . $safeBadge . '</span>';
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    private static function items(array $content, string $variant): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $max = self::MAX_ITEMS[$variant] ?? 12;

        return array_slice(array_values(array_filter($raw, 'is_array')), 0, $max);
    }
}

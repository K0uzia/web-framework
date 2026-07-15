<?php

declare(strict_types=1);

namespace Capsule\Section\Compare;

use Capsule\FontAwesomeIcon;

/**
 * Rendu HTML spécifique aux variantes compare (conversion des blocs React).
 */
final class CompareVariantRenderer
{
    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'compare7' => 16,
        'compare8' => 16,
    ];

    /** @var list<string> */
    private const DEFAULT_ICONS = [
        'fa-table-columns',
        'fa-gear',
        'fa-moon',
        'fa-font',
        'fa-universal-access',
        'fa-list-check',
        'fa-certificate',
        'fa-gem',
        'fa-pen-ruler',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['compare_rows_html'] = match ($variant) {
            'compare8' => self::rowsCompare8Html($content),
            default => self::rowsCompare7Html($content),
        };

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function rowsCompare7Html(array $content): string
    {
        $html = '';
        foreach (self::items($content, 'compare7') as $item) {
            $feature = trim((string) ($item['title'] ?? ''));
            if ($feature === '') {
                continue;
            }
            $primary = trim((string) ($item['label'] ?? ''));
            $secondary = trim((string) ($item['text'] ?? ''));
            $tooltipTitle = trim((string) ($item['tooltip_title'] ?? ''));
            $tooltipText = trim((string) ($item['tooltip_text'] ?? ''));
            $safeFeature = htmlspecialchars($feature, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safePrimary = htmlspecialchars($primary, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $secondaryCell = self::secondaryCellCompare7($secondary, $tooltipTitle, $tooltipText);

            $html .= '<tr class="section-compare__row--compare7">'
                . '<th scope="row" class="section-compare__feature--compare7">' . $safeFeature . '</th>'
                . '<td class="section-compare__cell--compare7 section-compare__cell--primary--compare7">' . $safePrimary . '</td>'
                . '<td class="section-compare__cell--compare7 section-compare__cell--secondary--compare7">' . $secondaryCell . '</td>'
                . '</tr>';
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function rowsCompare8Html(array $content): string
    {
        $html = '';
        foreach (self::items($content, 'compare8') as $index => $item) {
            $label = trim((string) ($item['title'] ?? ''));
            if ($label === '') {
                continue;
            }
            $description = trim((string) ($item['text'] ?? ''));
            $tooltipTitle = trim((string) ($item['tooltip_title'] ?? ''));
            $tooltipText = trim((string) ($item['tooltip_text'] ?? ''));
            $hasTooltip = $tooltipTitle !== '' || $tooltipText !== '';
            $primaryStatus = self::statusValue((string) ($item['primary'] ?? $item['label'] ?? ''));
            $secondaryStatus = self::statusValue((string) ($item['secondary'] ?? $item['href'] ?? ''));
            $icon = self::iconHtml($item, $index);
            $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<div class="section-compare__row--compare8">'
                . '<div class="section-compare__icon-col--compare8">' . $icon
                . '<span class="section-compare__mobile-label--compare8">' . $safeLabel . '</span>'
                . '</div>'
                . '<div class="section-compare__feature-col--compare8">'
                . '<div class="section-compare__feature-label--compare8">' . $safeLabel . '</div>'
                . ($description !== ''
                    ? '<p class="section-compare__feature-text--compare8">' . $safeDescription . '</p>'
                    : '')
                . '</div>'
                . '<div class="section-compare__status-col--compare8 section-compare__status-col--primary--compare8">'
                . self::statusIconHtml($primaryStatus, false)
                . '<span class="section-compare__mobile-col-label--compare8">Colonne principale</span>'
                . '</div>'
                . '<div class="section-compare__status-col--compare8 section-compare__status-col--secondary--compare8">'
                . self::statusIconHtml($secondaryStatus, $hasTooltip && $secondaryStatus === 'no')
                . '<span class="section-compare__mobile-col-label--compare8">Colonne secondaire</span>'
                . self::tooltipNoteHtml($tooltipTitle, $tooltipText)
                . '</div>'
                . '</div>';
        }

        return $html;
    }

    private static function secondaryCellCompare7(string $value, string $tooltipTitle, string $tooltipText): string
    {
        if ($value === '') {
            return '';
        }
        $safeValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if ($tooltipTitle === '' && $tooltipText === '') {
            return $safeValue;
        }
        $safeTooltipTitle = htmlspecialchars($tooltipTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeTooltipText = htmlspecialchars($tooltipText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $ariaLabel = trim($tooltipTitle . '. ' . $tooltipText);

        return '<span class="section-compare__tooltip-wrap--compare7">'
            . '<button type="button" class="section-compare__tooltip-trigger--compare7" aria-label="'
            . htmlspecialchars($ariaLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
            . '<span class="section-compare__tooltip-value--compare7">' . $safeValue . '</span>'
            . '</button>'
            . '<span class="section-compare__tooltip-panel--compare7" role="tooltip">'
            . ($tooltipTitle !== '' ? '<strong class="section-compare__tooltip-title--compare7">' . $safeTooltipTitle . '</strong>' : '')
            . ($tooltipText !== '' ? '<span>' . $safeTooltipText . '</span>' : '')
            . '</span>'
            . '</span>';
    }

    private static function tooltipNoteHtml(string $title, string $text): string
    {
        if ($title === '' && $text === '') {
            return '';
        }
        $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeText = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<p class="section-compare__tooltip-note--compare8">'
            . ($title !== '' ? '<strong>' . $safeTitle . '</strong> ' : '')
            . $safeText
            . '</p>';
    }

    private static function statusIconHtml(string $status, bool $dashInsteadOfNo): string
    {
        if ($dashInsteadOfNo && $status === 'no') {
            return '<span class="section-compare__dash--compare8" aria-hidden="true">—</span>'
                . '<span class="visually-hidden">Non applicable</span>';
        }

        return match ($status) {
            'partial' => '<i class="fa-solid fa-check section-compare__icon--partial--compare8" aria-hidden="true"></i>'
                . '<span class="visually-hidden">Partiel</span>',
            'yes' => '<i class="fa-solid fa-check section-compare__icon--yes--compare8" aria-hidden="true"></i>'
                . '<span class="visually-hidden">Oui</span>',
            default => '<i class="fa-solid fa-xmark section-compare__icon--no--compare8" aria-hidden="true"></i>'
                . '<span class="visually-hidden">Non</span>',
        };
    }

    private static function statusValue(string $raw): string
    {
        $value = strtolower(trim($raw));
        if (in_array($value, ['yes', 'oui', 'true', '1', 'ok'], true)) {
            return 'yes';
        }
        if (in_array($value, ['partial', 'partiel', 'partielle'], true)) {
            return 'partial';
        }

        return 'no';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function iconHtml(array $item, int $index): string
    {
        $raw = trim((string) ($item['icon'] ?? ''));
        if ($raw === '') {
            $raw = self::DEFAULT_ICONS[$index % count(self::DEFAULT_ICONS)];
        }
        $class = FontAwesomeIcon::solidClass(FontAwesomeIcon::glyph($raw));

        return '<i class="' . htmlspecialchars($class, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . ' section-compare__icon--compare8" aria-hidden="true"></i>';
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    private static function items(array $content, string $variant): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $max = self::MAX_ITEMS[$variant] ?? 16;
        $items = [];
        foreach (array_slice($raw, 0, $max) as $item) {
            if (is_array($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }
}

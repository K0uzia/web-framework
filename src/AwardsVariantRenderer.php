<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Rendu HTML spécifique aux variantes awards (conversion des blocs React).
 */
final class AwardsVariantRenderer
{
    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'awards1' => 12,
        'awards2' => 16,
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['awards_rows_html'] = $variant === 'awards1' ? self::rowsAwards1Html($content) : '';
        $data['awards_list_html'] = $variant === 'awards2' ? self::listAwards2Html($content) : '';

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function rowsAwards1Html(array $content): string
    {
        $html = '';
        foreach (self::items($content, 'awards1') as $item) {
            $name = trim((string) ($item['title'] ?? ''));
            if ($name === '') {
                continue;
            }
            $description = trim((string) ($item['text'] ?? ''));
            $year = trim((string) ($item['label'] ?? ''));
            $href = trim((string) ($item['href'] ?? ''));
            $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeYear = htmlspecialchars($year, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $nameCell = $href !== ''
                ? '<a class="section-awards__link--awards1" href="'
                    . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    . '" target="_blank" rel="noopener noreferrer" title="' . $safeName . '">'
                    . $safeName . '</a>'
                : $safeName;

            $html .= '<tr class="section-awards__row--awards1">'
                . '<th scope="row" class="section-awards__name--awards1">' . $nameCell . '</th>'
                . '<td class="section-awards__description--awards1">' . $safeDescription . '</td>'
                . '<td class="section-awards__year--awards1">' . $safeYear . '</td>'
                . '</tr>';
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function listAwards2Html(array $content): string
    {
        $html = '';
        foreach (self::items($content, 'awards2') as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $year = trim((string) ($item['label'] ?? ''));
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeYear = htmlspecialchars($year, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<li class="section-awards__item--awards2">'
                . '<h3 class="section-awards__item-title--awards2">' . $safeTitle . '</h3>'
                . '<p class="section-awards__item-year--awards2">' . $safeYear . '</p>'
                . '</li>';
        }

        return $html;
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

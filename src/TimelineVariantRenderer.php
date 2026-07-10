<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Rendu HTML spécifique aux variantes timeline (conversion des blocs React).
 */
final class TimelineVariantRenderer
{
    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'timeline3' => 8,
        'timeline9' => 12,
    ];

    /** @var list<string> */
    private const DEFAULT_IMAGES = [
        'placeholder-4.svg',
        'placeholder-3.svg',
        'placeholder-2.svg',
        'placeholder-1.svg',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['timeline_entries_html'] = match ($variant) {
            'timeline9' => self::entriesTimeline9Html($content),
            default => self::entriesTimeline3Html($content),
        };

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function entriesTimeline3Html(array $content): string
    {
        $html = '';
        foreach (self::items($content, 'timeline3') as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $description = trim((string) ($item['text'] ?? ''));
            $imageUrl = self::imageUrl((string) ($item['url'] ?? ''), $index);
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeImageUrl = htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeAlt = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<article class="section-timeline__card--timeline3">'
                . '<img class="section-timeline__card-img--timeline3" src="' . $safeImageUrl . '" alt="' . $safeAlt
                . '" width="640" height="360" loading="lazy" decoding="async" />'
                . '<div class="section-timeline__card-body--timeline3">'
                . '<h3 class="section-timeline__card-title--timeline3">' . $safeTitle . '</h3>'
                . ($description !== ''
                    ? '<p class="section-timeline__card-text--timeline3">' . $safeDescription . '</p>'
                    : '')
                . '</div>'
                . '</article>';
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function entriesTimeline9Html(array $content): string
    {
        $html = '';
        foreach (self::items($content, 'timeline9') as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $date = trim((string) ($item['date'] ?? $item['label'] ?? ''));
            $text = trim((string) ($item['text'] ?? ''));
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDate = htmlspecialchars($date, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeText = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<div class="section-timeline__entry--timeline9">'
                . '<div class="section-timeline__dot--timeline9" aria-hidden="true"></div>'
                . '<h3 class="section-timeline__entry-title--timeline9">' . $safeTitle . '</h3>'
                . ($date !== '' ? '<p class="section-timeline__entry-date--timeline9">' . $safeDate . '</p>' : '')
                . ($text !== ''
                    ? '<div class="section-timeline__entry-content--timeline9"><p>' . $safeText . '</p></div>'
                    : '')
                . '</div>';
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
        $max = self::MAX_ITEMS[$variant] ?? 8;
        $items = [];
        foreach (array_slice($raw, 0, $max) as $item) {
            if (is_array($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    private static function imageUrl(string $url, int $index): string
    {
        $fallbackFile = self::DEFAULT_IMAGES[$index % count(self::DEFAULT_IMAGES)];

        return SectionAssets::resolve(
            $url,
            SectionAssets::shared('features', $fallbackFile),
        );
    }
}

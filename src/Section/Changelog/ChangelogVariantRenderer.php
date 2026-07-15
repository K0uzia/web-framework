<?php

declare(strict_types=1);

namespace Capsule\Section\Changelog;

use Capsule\SectionAssets;

use Capsule\Section\SectionItemsTrait;

/**
 * Rendu HTML spécifique aux variantes changelog (conversion des blocs React).
 */
final class ChangelogVariantRenderer
{
    use SectionItemsTrait;

    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'changelog1' => 12,
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['changelog_entries_html'] = match ($variant) {
            default => self::entriesChangelog1Html($content),
        };

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function entriesChangelog1Html(array $content): string
    {
        $html = '';
        foreach (self::itemsFromContent($content, self::MAX_ITEMS['changelog1']) as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $version = trim((string) ($item['label'] ?? ''));
            $date = trim((string) ($item['date'] ?? ''));
            $description = trim((string) ($item['text'] ?? ''));
            $imageUrl = trim((string) ($item['url'] ?? ''));
            $buttonHref = trim((string) ($item['href'] ?? ''));
            $buttonLabel = trim((string) ($item['cta_label'] ?? ''));

            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeVersion = htmlspecialchars($version, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDate = htmlspecialchars($date, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $metaHtml = '<div class="section-changelog__meta--changelog1">';
            if ($version !== '') {
                $metaHtml .= '<span class="section-changelog__badge--changelog1">' . $safeVersion . '</span>';
            }
            if ($date !== '') {
                $metaHtml .= '<span class="section-changelog__date--changelog1">' . $safeDate . '</span>';
            }
            $metaHtml .= '</div>';

            $featuresHtml = '';
            $features = self::parseFeatures((string) ($item['features'] ?? ''));
            if ($features !== []) {
                $featuresHtml = '<ul class="section-changelog__list--changelog1">';
                foreach ($features as $feature) {
                    $featuresHtml .= '<li>' . htmlspecialchars($feature, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
                }
                $featuresHtml .= '</ul>';
            }

            $imageHtml = '';
            if ($imageUrl !== '') {
                $resolvedUrl = SectionAssets::resolve(
                    $imageUrl,
                    SectionAssets::shared('features', 'placeholder-1.svg'),
                );
                $safeImageUrl = htmlspecialchars($resolvedUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $safeAlt = htmlspecialchars(
                    $version !== '' ? $version . ' visuel' : $title,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8',
                );
                $imageHtml = '<img class="section-changelog__image--changelog1" src="' . $safeImageUrl
                    . '" alt="' . $safeAlt . '" width="768" height="432" loading="lazy" decoding="async" />';
            }

            $buttonHtml = '';
            if ($buttonLabel !== '' && $buttonHref !== '') {
                $safeHref = htmlspecialchars($buttonHref, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $safeLabel = htmlspecialchars($buttonLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $buttonHtml = '<a class="section-changelog__link--changelog1" href="' . $safeHref
                    . '" rel="noopener noreferrer" target="_blank">'
                    . $safeLabel . ' <i class="fa-solid fa-arrow-up-right" aria-hidden="true"></i></a>';
            }

            $html .= '<article class="section-changelog__entry--changelog1">'
                . $metaHtml
                . '<div class="section-changelog__body--changelog1">'
                . '<h2 class="section-changelog__entry-title--changelog1">' . $safeTitle . '</h2>'
                . ($description !== ''
                    ? '<p class="section-changelog__entry-text--changelog1">' . $safeDescription . '</p>'
                    : '')
                . $featuresHtml
                . $imageHtml
                . $buttonHtml
                . '</div>'
                . '</article>';
        }

        return $html;
    }

    /**
     * @return list<string>
     */
    private static function parseFeatures(string $raw): array
    {
        $lines = preg_split('/\r\n|\n|\r/', $raw) ?: [];

        return array_values(array_filter(array_map('trim', $lines), static fn (string $line): bool => $line !== ''));
    }
}

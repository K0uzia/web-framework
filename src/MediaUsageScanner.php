<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Détecte les usages d'une URL média dans le site et les pages.
 */
final class MediaUsageScanner
{
    public function __construct(
        private readonly PageRepository $pages,
        private readonly SiteRepository $site,
    ) {
    }

    /**
     * @return list<string> descriptions d'usage
     */
    public function usages(string $url): array
    {
        $report = $this->report($url);
        $lines = $report['site_labels'];
        foreach ($report['entries'] as $entry) {
            $lines[] = $entry['detail'];
        }

        return array_values(array_unique($lines));
    }

    /**
     * @return array{
     *   total_places: int,
     *   page_count: int,
     *   block_count: int,
     *   site_labels: list<string>,
     *   entries: list<array{kind: string, page: string, page_path: string, section_type: string, section_id: string, detail: string}>
     * }
     */
    public function report(string $url): array
    {
        $url = trim($url);
        if ($url === '') {
            return [
                'total_places' => 0,
                'page_count' => 0,
                'block_count' => 0,
                'site_labels' => [],
                'entries' => [],
            ];
        }

        $siteLabels = [];
        $site = $this->site->getSite();
        foreach (['logo_url' => 'Logo du site', 'favicon_url' => 'Favicon', 'og_image_url' => 'Image de partage'] as $key => $label) {
            if (trim((string) ($site[$key] ?? '')) === $url) {
                $siteLabels[] = $label;
            }
        }

        $entries = [];
        foreach ($this->pages->all() as $page) {
            $pagePath = $page->slug === '' ? '/' : $page->routePath();
            $pageLabel = $page->slug === '' ? 'Accueil' : $page->routePath();
            foreach ($page->sections as $section) {
                if (!is_array($section)) {
                    continue;
                }
                if (!$this->sectionContainsUrl($section, $url)) {
                    continue;
                }
                $sectionType = (string) ($section['type'] ?? 'bloc');
                $sectionId = (string) ($section['id'] ?? '');
                $entries[] = [
                    'kind' => 'block',
                    'page' => $page->slug,
                    'page_path' => $pagePath,
                    'section_type' => $sectionType,
                    'section_id' => $sectionId,
                    'detail' => $pageLabel . ' · ' . $sectionType . ($sectionId !== '' ? ' (' . $sectionId . ')' : ''),
                ];
            }
        }

        $pagePaths = [];
        $blockKeys = [];
        foreach ($entries as $entry) {
            $pagePaths[$entry['page_path']] = true;
            $blockKeys[$entry['page'] . '|' . $entry['section_id']] = true;
        }

        return [
            'total_places' => count($siteLabels) + count($entries),
            'page_count' => count($pagePaths),
            'block_count' => count($blockKeys),
            'site_labels' => $siteLabels,
            'entries' => $entries,
        ];
    }

    public function isInUse(string $url): bool
    {
        return $this->report($url)['total_places'] > 0;
    }

    /**
     * @param array<string, mixed> $section
     */
    private function sectionContainsUrl(array $section, string $url): bool
    {
        $content = $section['content'] ?? null;

        return is_array($content) && $this->valueContainsUrl($content, $url);
    }

    /**
     * @param array<mixed> $value
     */
    private function valueContainsUrl(array $value, string $url): bool
    {
        foreach ($value as $item) {
            if (is_string($item) && trim($item) === $url) {
                return true;
            }
            if (is_array($item) && $this->valueContainsUrl($item, $url)) {
                return true;
            }
        }

        return false;
    }
}

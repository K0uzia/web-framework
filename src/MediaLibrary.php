<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Liste les visuels locaux disponibles pour les blocs (uploads, site, stock).
 */
final class MediaLibrary
{
    public function __construct(
        private readonly string $uploadsDir,
        private readonly string $publicBasePath = '/uploads/site',
        private readonly ?PageRepository $pages = null,
        private readonly ?SiteRepository $site = null,
    ) {
    }

    /**
     * @return list<string>
     */
    public function availableUrls(): array
    {
        $urls = [];

        if ($this->site !== null) {
            $site = $this->site->getSite();
            foreach (['logo_url', 'favicon_url', 'og_image_url'] as $key) {
                $url = trim((string) ($site[$key] ?? ''));
                if ($this->isAllowedUrl($url)) {
                    $urls[] = $url;
                }
            }
        }

        if (is_dir($this->uploadsDir)) {
            foreach (glob($this->uploadsDir . '/*') ?: [] as $file) {
                if (!is_file($file)) {
                    continue;
                }
                $urls[] = rtrim($this->publicBasePath, '/') . '/' . basename($file);
            }
        }

        if ($this->pages !== null) {
            foreach ($this->pages->all() as $page) {
                foreach ($page->sections as $section) {
                    if (is_array($section)) {
                        $this->collectFromSection($section, $urls);
                    }
                }
            }
        }

        foreach (StockImages::all() as $url) {
            $urls[] = $url;
        }

        $urls = array_values(array_unique(array_filter($urls, fn (string $url): bool => $this->isAllowedUrl($url))));
        sort($urls);

        return $urls;
    }

    public function isAllowedUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || str_contains($url, '..')) {
            return false;
        }

        $uploadPrefix = rtrim($this->publicBasePath, '/') . '/';

        return str_starts_with($url, $uploadPrefix)
            || str_starts_with($url, StockImages::BASE . '/');
    }

    /**
     * @param list<string> $urls
     */
    private function collectFromSection(array $section, array &$urls): void
    {
        $content = is_array($section['content'] ?? null) ? $section['content'] : [];
        foreach (['image_url', 'url'] as $key) {
            $url = trim((string) ($content[$key] ?? ''));
            if ($this->isAllowedUrl($url)) {
                $urls[] = $url;
            }
        }

        $items = is_array($content['items'] ?? null) ? $content['items'] : [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            foreach (['image_url', 'url'] as $key) {
                $url = trim((string) ($item[$key] ?? ''));
                if ($this->isAllowedUrl($url)) {
                    $urls[] = $url;
                }
            }
        }
    }
}

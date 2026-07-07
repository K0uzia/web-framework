<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Liste les médias disponibles pour les blocs (bibliothèque DB, uploads, stock).
 */
final class MediaLibrary
{
    public function __construct(
        private readonly MediaRepository $media,
        private readonly string $uploadsDir,
        private readonly string $publicBasePath = '/uploads/site',
        private readonly ?PageRepository $pages = null,
        private readonly ?SiteRepository $site = null,
    ) {
    }

    /**
     * @return list<string>
     */
    public function availableImageUrls(): array
    {
        return $this->mergeUrls(
            $this->media->urlsByKind('image'),
            StockImages::all(),
        );
    }

    /**
     * @return list<string>
     */
    public function availableVideoUrls(): array
    {
        return $this->media->urlsByKind('video');
    }

    /**
     * @return list<string>
     */
    public function availableUrls(): array
    {
        return $this->availableImageUrls();
    }

    /**
     * @return list<array{id: string, kind: string, url: string, filename: string, mime: string, size: int, label: string, created_at: string}>
     */
    public function allRecords(?string $kind = null): array
    {
        return $this->media->all($kind);
    }

    public function isAllowedUrl(string $url, string $kind = 'image'): bool
    {
        $url = trim($url);
        if ($url === '' || str_contains($url, '..')) {
            return false;
        }

        if ($kind === 'video') {
            if (preg_match('~^https?://~i', $url) === 1) {
                return true;
            }

            return str_starts_with($url, '/uploads/media/')
                || str_starts_with($url, rtrim($this->publicBasePath, '/') . '/');
        }

        $uploadPrefix = rtrim($this->publicBasePath, '/') . '/';

        return str_starts_with($url, $uploadPrefix)
            || str_starts_with($url, '/uploads/media/')
            || str_starts_with($url, StockImages::BASE . '/');
    }

    /**
     * @param list<string> $primary
     * @param list<string> $extra
     *
     * @return list<string>
     */
    private function mergeUrls(array $primary, array $extra): array
    {
        $urls = $primary;

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
                if (is_file($file)) {
                    $urls[] = rtrim($this->publicBasePath, '/') . '/' . basename($file);
                }
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

        foreach ($extra as $url) {
            $urls[] = $url;
        }

        $urls = array_values(array_unique(array_filter($urls, fn (string $url): bool => $this->isAllowedUrl($url))));
        sort($urls);

        return $urls;
    }

    /**
     * @param list<string> $urls
     */
    private function collectFromSection(array $section, array &$urls): void
    {
        $content = is_array($section['content'] ?? null) ? $section['content'] : [];
        foreach (['image_url', 'url', 'video_url'] as $key) {
            $url = trim((string) ($content[$key] ?? ''));
            if ($url !== '' && ($this->isAllowedUrl($url) || $this->isAllowedUrl($url, 'video'))) {
                $urls[] = $url;
            }
        }

        $items = is_array($content['items'] ?? null) ? $content['items'] : [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            foreach (['image_url', 'url', 'video_url'] as $key) {
                $url = trim((string) ($item[$key] ?? ''));
                if ($url !== '' && ($this->isAllowedUrl($url) || $this->isAllowedUrl($url, 'video'))) {
                    $urls[] = $url;
                }
            }
        }
    }
}

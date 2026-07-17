<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Liste les médias disponibles pour les blocs (bibliothèque DB, uploads).
 * Galerie dev (/uploads/media) et galerie client (/uploads/library) sont séparées.
 */
final class MediaLibrary
{
    /** @var list<string>|null */
    private ?array $imageUrlsCache = null;

    /** @var list<string>|null */
    private ?array $videoUrlsCache = null;

    /** @var list<string>|null */
    private ?array $clientImageUrlsCache = null;

    /** @var list<string>|null */
    private ?array $clientVideoUrlsCache = null;

    public function __construct(
        private readonly MediaRepository $media,
        private readonly string $uploadsDir,
        private readonly string $publicBasePath = '/uploads/site',
        private readonly ?PageRepository $pages = null,
        private readonly ?SiteRepository $site = null,
        private readonly string $libraryUploadsDir = '',
        private readonly string $publicRoot = '',
        private readonly string $clientLibraryUploadsDir = '',
    ) {
    }

    /**
     * @return list<string>
     */
    public function availableImageUrls(): array
    {
        if ($this->imageUrlsCache !== null) {
            return $this->imageUrlsCache;
        }

        $this->imageUrlsCache = $this->mergeDevUrls(
            $this->media->urlsByKind('image', MediaRepository::OWNER_DEV),
        );

        return $this->imageUrlsCache;
    }

    /**
     * @return list<string>
     */
    public function availableVideoUrls(): array
    {
        if ($this->videoUrlsCache !== null) {
            return $this->videoUrlsCache;
        }

        $this->videoUrlsCache = $this->media->urlsByKind('video', MediaRepository::OWNER_DEV);

        return $this->videoUrlsCache;
    }

    /**
     * @return list<string>
     */
    public function availableClientImageUrls(): array
    {
        if ($this->clientImageUrlsCache !== null) {
            return $this->clientImageUrlsCache;
        }

        $urls = $this->media->urlsByKind('image', MediaRepository::OWNER_CLIENT);
        if ($this->clientLibraryUploadsDir !== '' && is_dir($this->clientLibraryUploadsDir)) {
            foreach (glob($this->clientLibraryUploadsDir . '/*') ?: [] as $file) {
                if (is_file($file)) {
                    $urls[] = '/uploads/library/' . basename($file);
                }
            }
        }
        $urls = array_values(array_unique(array_filter(
            $urls,
            fn (string $url): bool => $this->isClientLibraryUrl($url) && $this->isAllowedUrl($url),
        )));
        sort($urls);
        $this->clientImageUrlsCache = $urls;

        return $this->clientImageUrlsCache;
    }

    /**
     * @return list<string>
     */
    public function availableClientVideoUrls(): array
    {
        if ($this->clientVideoUrlsCache !== null) {
            return $this->clientVideoUrlsCache;
        }

        $urls = $this->media->urlsByKind('video', MediaRepository::OWNER_CLIENT);
        if ($this->clientLibraryUploadsDir !== '' && is_dir($this->clientLibraryUploadsDir)) {
            foreach (glob($this->clientLibraryUploadsDir . '/*') ?: [] as $file) {
                if (!is_file($file) || !$this->isVideoPath($file)) {
                    continue;
                }
                $urls[] = '/uploads/library/' . basename($file);
            }
        }
        $urls = array_values(array_unique(array_filter(
            $urls,
            fn (string $url): bool => $this->isClientLibraryUrl($url) && $this->isAllowedUrl($url, 'video'),
        )));
        sort($urls);
        $this->clientVideoUrlsCache = $urls;

        return $this->clientVideoUrlsCache;
    }

    /**
     * @return list<string>
     */
    public function availableUrls(): array
    {
        return $this->availableImageUrls();
    }

    /**
     * @return list<array{id: string, kind: string, url: string, filename: string, mime: string, size: int, label: string, owner: string, created_at: string}>
     */
    public function allRecords(?string $kind = null, ?string $owner = null): array
    {
        return $this->media->all($kind, $owner);
    }

    public function syncDiscoveredRecords(?string $kind = null): void
    {
        if ($kind === null || $kind === 'image') {
            foreach ($this->availableImageUrls() as $url) {
                $this->registerUrlIfMissing('image', $url);
            }
        }

        if ($kind === null || $kind === 'video') {
            foreach ($this->availableVideoUrls() as $url) {
                $this->registerUrlIfMissing('video', $url);
            }
        }
    }

    public function syncClientRecords(?string $kind = null): void
    {
        if ($kind === null || $kind === 'image') {
            foreach ($this->availableClientImageUrls() as $url) {
                $this->registerUrlIfMissing('image', $url);
            }
        }

        if ($kind === null || $kind === 'video') {
            foreach ($this->availableClientVideoUrls() as $url) {
                $this->registerUrlIfMissing('video', $url);
            }
        }
    }

    public function publicUrlToPath(string $url): ?string
    {
        $url = trim($url);
        if ($url === '' || str_contains($url, '..') || preg_match('~^https?://~i', $url) === 1 || !str_starts_with($url, '/')) {
            return null;
        }

        if ($this->publicRoot === '') {
            return null;
        }

        $path = $this->publicRoot . $url;
        $resolved = realpath($path);

        if ($resolved === false || !is_file($resolved)) {
            return null;
        }

        $publicRoot = realpath($this->publicRoot);
        if ($publicRoot === false || !str_starts_with($resolved, $publicRoot . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $resolved;
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
                || str_starts_with($url, '/uploads/library/')
                || str_starts_with($url, rtrim($this->publicBasePath, '/') . '/');
        }

        $uploadPrefix = rtrim($this->publicBasePath, '/') . '/';

        return str_starts_with($url, $uploadPrefix)
            || str_starts_with($url, '/uploads/media/')
            || str_starts_with($url, '/uploads/library/')
            || str_starts_with($url, '/assets/sections/');
    }

    public function isBundledAsset(string $url): bool
    {
        $url = trim($url);

        return str_starts_with($url, '/assets/sections/');
    }

    public function isClientLibraryUrl(string $url): bool
    {
        return str_starts_with(trim($url), '/uploads/library/');
    }

    /**
     * @param list<string> $primary
     *
     * @return list<string>
     */
    private function mergeDevUrls(array $primary): array
    {
        $urls = $primary;

        if ($this->site !== null) {
            $site = $this->site->getSite();
            foreach (['logo_url', 'favicon_url', 'og_image_url'] as $key) {
                $url = trim((string) ($site[$key] ?? ''));
                if ($this->isAllowedUrl($url) && !$this->isClientLibraryUrl($url)) {
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

        if ($this->libraryUploadsDir !== '' && is_dir($this->libraryUploadsDir)) {
            foreach (glob($this->libraryUploadsDir . '/*') ?: [] as $file) {
                if (is_file($file)) {
                    $urls[] = '/uploads/media/' . basename($file);
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

        $urls = array_values(array_unique(array_filter(
            $urls,
            fn (string $url): bool => $this->isAllowedUrl($url) && !$this->isClientLibraryUrl($url),
        )));
        sort($urls);

        return $urls;
    }

    /**
     * @param list<string> $urls
     */
    private function collectFromSection(array $section, array &$urls): void
    {
        $content = is_array($section['content'] ?? null) ? $section['content'] : [];
        $this->collectUrlsFromValue($content, $urls);
    }

    /**
     * @param list<string> $urls
     */
    private function collectUrlsFromValue(mixed $value, array &$urls): void
    {
        if (is_string($value)) {
            $url = trim($value);
            if ($url !== '' && $this->isAllowedUrl($url) && !$this->isVideoPath($url) && !$this->isClientLibraryUrl($url)) {
                $urls[] = $url;
            }

            return;
        }

        if (!is_array($value)) {
            return;
        }

        foreach ($value as $item) {
            $this->collectUrlsFromValue($item, $urls);
        }
    }

    private function registerUrlIfMissing(string $kind, string $url): void
    {
        if ($this->media->findByUrl($url) !== null) {
            return;
        }

        if (!$this->isAllowedUrl($url, $kind) || preg_match('~^https?://~i', $url) === 1) {
            return;
        }

        if ($kind === 'image' && $this->isVideoPath($url)) {
            return;
        }

        if ($kind === 'video' && !$this->isAllowedUrl($url, 'video')) {
            return;
        }

        $path = $this->publicUrlToPath($url);
        if ($path === null) {
            return;
        }

        $size = (int) filesize($path);
        if ($size <= 0) {
            return;
        }

        $label = $this->isBundledAsset($url) ? 'Modèle : ' . basename($url) : '';

        $this->media->create(
            $kind,
            $url,
            basename($url),
            $this->detectMime($path),
            $size,
            $label,
            MediaRepository::ownerFromUrl($url),
        );
    }

    private function detectMime(string $path): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = finfo_file($finfo, $path);
                finfo_close($finfo);
                if (is_string($mime) && $mime !== '') {
                    return $mime;
                }
            }
        }

        return 'application/octet-stream';
    }

    private function isVideoPath(string $url): bool
    {
        $ext = strtolower((string) pathinfo($url, PATHINFO_EXTENSION));

        return in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'mkv'], true);
    }
}

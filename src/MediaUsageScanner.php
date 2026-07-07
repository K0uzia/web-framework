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
        $url = trim($url);
        if ($url === '') {
            return [];
        }

        $hits = [];
        $site = $this->site->getSite();
        foreach (['logo_url' => 'Logo du site', 'favicon_url' => 'Favicon', 'og_image_url' => 'Image de partage'] as $key => $label) {
            if (trim((string) ($site[$key] ?? '')) === $url) {
                $hits[] = $label;
            }
        }

        foreach ($this->pages->all() as $page) {
            $path = $page->slug === '' ? 'Accueil' : $page->routePath();
            foreach ($page->sections as $section) {
                if (!is_array($section)) {
                    continue;
                }
                $type = (string) ($section['type'] ?? 'bloc');
                $sectionLabel = $type . ' (' . (string) ($section['id'] ?? '') . ')';
                $content = is_array($section['content'] ?? null) ? $section['content'] : [];
                foreach (['image_url', 'video_url', 'url'] as $key) {
                    if (trim((string) ($content[$key] ?? '')) === $url) {
                        $hits[] = 'Page ' . $path . ', ' . $sectionLabel;
                    }
                }
                $items = is_array($content['items'] ?? null) ? $content['items'] : [];
                foreach ($items as $index => $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    foreach (['image_url', 'url', 'video_url'] as $key) {
                        if (trim((string) ($item[$key] ?? '')) === $url) {
                            $hits[] = 'Page ' . $path . ', ' . $sectionLabel . ', élément ' . ($index + 1);
                        }
                    }
                }
            }
        }

        return array_values(array_unique($hits));
    }

    public function isInUse(string $url): bool
    {
        return $this->usages($url) !== [];
    }
}

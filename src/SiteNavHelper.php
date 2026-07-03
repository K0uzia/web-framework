<?php

declare(strict_types=1);

namespace Capsule;

final class SiteNavHelper
{
    /**
     * @param list<array<string, mixed>> $items
     *
     * @return list<array<string, mixed>>
     */
    public static function normalize(array $items): array
    {
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $type = (string) ($item['type'] ?? 'page');
            if ($type === '' && isset($item['slug'])) {
                $type = 'page';
            }
            $out[] = [
                'id' => (string) ($item['id'] ?? ('nav-' . bin2hex(random_bytes(3)))),
                'type' => $type,
                'slug' => (string) ($item['slug'] ?? ''),
                'href' => (string) ($item['href'] ?? ''),
                'label' => (string) ($item['label'] ?? ''),
                'visible' => ($item['visible'] ?? true) !== false,
            ];
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function autoFromPages(PageRepository $pages, string $homeLabel): array
    {
        $published = $pages->allPublished();
        usort($published, static function (Page $a, Page $b): int {
            if ($a->slug === '' && $b->slug !== '') {
                return -1;
            }
            if ($a->slug !== '' && $b->slug === '') {
                return 1;
            }

            return strcasecmp($a->title, $b->title);
        });

        $items = [];
        foreach ($published as $page) {
            $items[] = [
                'id' => 'nav-page-' . ($page->slug === '' ? 'home' : $page->slug),
                'type' => 'page',
                'slug' => $page->slug,
                'href' => '',
                'label' => $page->slug === '' ? $homeLabel : $page->title,
                'visible' => true,
            ];
        }

        return $items;
    }

    /**
     * @param list<array<string, mixed>> $items
     *
     * @return list<array<string, mixed>>
     */
    public static function syncPages(array $items, PageRepository $pages, string $homeLabel): array
    {
        $custom = array_values(array_filter(
            $items,
            static fn (array $item): bool => ($item['type'] ?? 'page') !== 'page',
        ));

        $pageItems = self::autoFromPages($pages, $homeLabel);
        $existingBySlug = [];
        foreach ($items as $item) {
            if (($item['type'] ?? '') === 'page') {
                $existingBySlug[(string) ($item['slug'] ?? '')] = $item;
            }
        }

        foreach ($pageItems as $i => $pageItem) {
            $slug = (string) $pageItem['slug'];
            if (isset($existingBySlug[$slug])) {
                $pageItems[$i]['id'] = $existingBySlug[$slug]['id'];
                $label = trim((string) ($existingBySlug[$slug]['label'] ?? ''));
                if ($label !== '') {
                    $pageItems[$i]['label'] = $label;
                }
                $pageItems[$i]['visible'] = ($existingBySlug[$slug]['visible'] ?? true) !== false;
            }
        }

        return array_merge($pageItems, $custom);
    }

    /**
     * @param list<array<string, mixed>> $items
     *
     * @return list<array{path: string, label: string, type: string}>
     */
    public static function resolvePublicItems(array $items, PageRepository $pages, string $homeLabel): array
    {
        $pagesBySlug = [];
        foreach ($pages->allPublished() as $page) {
            $pagesBySlug[$page->slug] = $page;
        }

        $resolved = [];
        foreach ($items as $item) {
            if (($item['visible'] ?? true) === false) {
                continue;
            }

            $type = (string) ($item['type'] ?? 'page');
            $label = trim((string) ($item['label'] ?? ''));

            if ($type === 'page') {
                $slug = (string) ($item['slug'] ?? '');
                if (!isset($pagesBySlug[$slug])) {
                    continue;
                }
                $page = $pagesBySlug[$slug];
                if ($label === '') {
                    $label = $slug === '' ? $homeLabel : $page->title;
                }
                $resolved[] = ['path' => $page->routePath(), 'label' => $label, 'type' => 'page'];
                continue;
            }

            $href = trim((string) ($item['href'] ?? ''));
            if ($href === '' || $label === '') {
                continue;
            }
            $resolved[] = ['path' => $href, 'label' => $label, 'type' => $type];
        }

        return $resolved;
    }

    /**
     * @param list<array{path: string, label: string, type: string}> $items
     */
    public static function renderNavHtml(array $items, string $currentPath): string
    {
        $links = [];
        foreach ($items as $item) {
            $type = $item['type'] ?? 'page';
            $class = 'site-nav__link';
            if ($type === 'button') {
                $class .= ' site-nav__link--button';
            }
            $active = self::pathsMatch($item['path'], $currentPath) ? ' is-active' : '';
            $links[] = '<a class="' . $class . $active . '" href="'
                . htmlspecialchars($item['path'], ENT_QUOTES) . '">'
                . htmlspecialchars($item['label'], ENT_QUOTES) . '</a>';
        }

        return implode("\n            ", $links);
    }

  /**
     * @param array<string, mixed> $headerCta
     */
    public static function renderHeaderCtaHtml(array $headerCta): string
    {
        if (($headerCta['enabled'] ?? false) !== true) {
            return '';
        }
        $label = trim((string) ($headerCta['label'] ?? ''));
        $href = trim((string) ($headerCta['href'] ?? ''));
        if ($label === '' || $href === '') {
            return '';
        }

        return '<a class="site-header__cta" href="' . htmlspecialchars($href, ENT_QUOTES) . '">'
            . htmlspecialchars($label, ENT_QUOTES) . '</a>';
    }

    private static function pathsMatch(string $a, string $b): bool
    {
        if (str_starts_with($a, 'http://') || str_starts_with($a, 'https://')) {
            return false;
        }

        return rtrim($a, '/') === rtrim($b, '/');
    }
}

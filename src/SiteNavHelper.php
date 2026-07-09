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

            $entry = [
                'id' => (string) ($item['id'] ?? ('nav-' . bin2hex(random_bytes(3)))),
                'type' => $type,
                'slug' => (string) ($item['slug'] ?? ''),
                'href' => (string) ($item['href'] ?? ''),
                'label' => (string) ($item['label'] ?? ''),
                'visible' => ($item['visible'] ?? true) !== false,
            ];

            if ($type === 'group') {
                $entry['slug'] = '';
                $entry['href'] = '';
                $children = is_array($item['children'] ?? null) ? $item['children'] : [];
                $entry['children'] = self::normalizeChildren($children);
            }

            $out[] = $entry;
        }

        return $out;
    }

    /**
     * @param list<array<string, mixed>> $items
     *
     * @return list<array<string, mixed>>
     */
    private static function normalizeChildren(array $items): array
    {
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $type = (string) ($item['type'] ?? 'page');
            if ($type === 'group') {
                continue;
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
        $flat = [];
        foreach (self::resolvePublicTree($items, $pages, $homeLabel) as $item) {
            if (($item['type'] ?? '') === 'group') {
                foreach ($item['children'] ?? [] as $child) {
                    if (is_array($child)) {
                        $flat[] = $child;
                    }
                }
                continue;
            }
            $flat[] = $item;
        }

        return $flat;
    }

    /**
     * @param list<array<string, mixed>> $items
     *
     * @return list<array<string, mixed>>
     */
    public static function resolvePublicTree(array $items, PageRepository $pages, string $homeLabel): array
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

            if ($type === 'group') {
                if ($label === '') {
                    continue;
                }
                $children = [];
                foreach (is_array($item['children'] ?? null) ? $item['children'] : [] as $child) {
                    if (!is_array($child)) {
                        continue;
                    }
                    $resolvedChild = self::resolveLeafItem($child, $pagesBySlug, $homeLabel);
                    if ($resolvedChild !== null) {
                        $children[] = $resolvedChild;
                    }
                }
                if ($children === []) {
                    continue;
                }
                $resolved[] = [
                    'type' => 'group',
                    'label' => $label,
                    'children' => $children,
                ];
                continue;
            }

            $resolvedLeaf = self::resolveLeafItem($item, $pagesBySlug, $homeLabel);
            if ($resolvedLeaf !== null) {
                $resolved[] = $resolvedLeaf;
            }
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed>              $item
     * @param array<string, Page>               $pagesBySlug
     *
     * @return array{path: string, label: string, type: string}|null
     */
    private static function resolveLeafItem(array $item, array $pagesBySlug, string $homeLabel): ?array
    {
        if (($item['visible'] ?? true) === false) {
            return null;
        }

        $type = (string) ($item['type'] ?? 'page');
        $label = trim((string) ($item['label'] ?? ''));

        if ($type === 'page') {
            $slug = (string) ($item['slug'] ?? '');
            if (!isset($pagesBySlug[$slug])) {
                return null;
            }
            $page = $pagesBySlug[$slug];
            if ($label === '') {
                $label = $slug === '' ? $homeLabel : $page->title;
            }

            return ['path' => $page->routePath(), 'label' => $label, 'type' => 'page'];
        }

        $href = trim((string) ($item['href'] ?? ''));
        if ($href === '' || $label === '') {
            return null;
        }

        return ['path' => $href, 'label' => $label, 'type' => $type];
    }

    /**
     * @param list<array<string, mixed>> $tree
     */
    public static function renderNavHtml(array $tree, string $currentPath): string
    {
        if ($tree === []) {
            return '';
        }

        $items = [];
        foreach ($tree as $node) {
            if (!is_array($node)) {
                continue;
            }
            $rendered = self::renderNavNode($node, $currentPath);
            if ($rendered !== '') {
                $items[] = $rendered;
            }
        }

        if ($items === []) {
            return '';
        }

        return '<ul class="site-nav__list">' . implode('', $items) . '</ul>';
    }

    /**
     * @param array<string, mixed> $node
     */
    private static function renderNavNode(array $node, string $currentPath): string
    {
        $type = (string) ($node['type'] ?? 'page');
        if ($type === 'group') {
            return self::renderGroupNode($node, $currentPath);
        }

        $path = (string) ($node['path'] ?? '');
        $label = (string) ($node['label'] ?? '');
        if ($path === '' || $label === '') {
            return '';
        }

        return '<li class="site-nav__item">' . self::renderLink($node, $currentPath) . '</li>';
    }

    /**
     * @param array<string, mixed> $node
     */
    private static function renderGroupNode(array $node, string $currentPath): string
    {
        $label = htmlspecialchars((string) ($node['label'] ?? ''), ENT_QUOTES);
        $children = is_array($node['children'] ?? null) ? $node['children'] : [];
        $sub = [];
        $hasActive = false;
        foreach ($children as $child) {
            if (!is_array($child)) {
                continue;
            }
            $path = (string) ($child['path'] ?? '');
            $childLabel = (string) ($child['label'] ?? '');
            if ($path === '' || $childLabel === '') {
                continue;
            }
            if (self::pathsMatch($path, $currentPath)) {
                $hasActive = true;
            }
            $sub[] = '<li class="site-nav__subitem">' . self::renderLink($child, $currentPath, true) . '</li>';
        }
        if ($sub === []) {
            return '';
        }

        $active = $hasActive ? ' site-nav__item--active' : '';
        $submenuId = 'site-nav-submenu-' . substr(md5($label), 0, 8);

        return '<li class="site-nav__item site-nav__item--group' . $active . '">'
            . '<details class="site-nav__details">'
            . '<summary class="site-nav__trigger" aria-controls="' . $submenuId . '">'
            . $label
            . ' <i class="fa-solid fa-chevron-down site-nav__chevron" aria-hidden="true"></i>'
            . '</summary>'
            . '<ul class="site-nav__submenu" id="' . $submenuId . '">' . implode('', $sub) . '</ul>'
            . '</details></li>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function renderLink(array $item, string $currentPath, bool $sub = false): string
    {
        $type = (string) ($item['type'] ?? 'page');
        $class = $sub ? 'site-nav__sublink' : 'site-nav__link';
        if ($type === 'button') {
            $class .= ' site-nav__link--button';
        }
        $active = self::pathsMatch((string) ($item['path'] ?? ''), $currentPath) ? ' is-active' : '';

        return '<a class="' . $class . $active . '" href="'
            . htmlspecialchars((string) ($item['path'] ?? ''), ENT_QUOTES) . '">'
            . htmlspecialchars((string) ($item['label'] ?? ''), ENT_QUOTES) . '</a>';
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

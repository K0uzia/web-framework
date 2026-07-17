<?php

declare(strict_types=1);

namespace App\Http\Admin;

use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteRepository;

final class PageRowsBuilder
{
    /**
     * @return list<PageListRow>
     */
    public static function build(SiteRepository $site, PageRepository $pages): array
    {
        $config = $site->getClientDashboard();
        $siteData = $site->getSite();
        $siteName = is_string($siteData['name'] ?? null) && $siteData['name'] !== ''
            ? $siteData['name']
            : 'Mon site';

        $pagesBySlug = [];
        foreach ($pages->all() as $page) {
            $pagesBySlug[$page->slug] = $page;
        }

        $rows = [];
        foreach (array_keys($config['pages']) as $slug) {
            if (!is_string($slug)) {
                continue;
            }
            $sections = $config['pages'][$slug]['sections'] ?? [];
            if (!is_array($sections) || $sections === []) {
                continue;
            }

            $page = $pagesBySlug[$slug] ?? null;
            $title = $page instanceof Page ? $page->title : ($slug === '' ? 'Accueil' : $slug);
            $path = $slug === '' ? '/' : '/' . $slug;
            $slugEnc = rawurlencode($slug === '' ? '_' : $slug);

            $rows[] = new PageListRow(
                slug: $slug,
                title: $title,
                path: $path,
                published: $page instanceof Page ? $page->published : true,
                updatedAt: $page instanceof Page ? $page->updatedAt : '',
                authorLabel: $siteName,
                editUrl: '/admin/pages/' . $slugEnc,
            );
        }

        usort($rows, static fn (PageListRow $a, PageListRow $b): int => strcmp($a->path, $b->path));

        return $rows;
    }

    /**
     * @param list<PageListRow> $rows
     *
     * @return list<PageListRow>
     */
    public static function mostRecent(array $rows, int $limit): array
    {
        $sorted = $rows;
        usort($sorted, static fn (PageListRow $a, PageListRow $b): int => strcmp($b->updatedAt, $a->updatedAt));

        return array_slice($sorted, 0, $limit);
    }
}

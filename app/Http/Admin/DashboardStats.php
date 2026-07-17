<?php

declare(strict_types=1);

namespace App\Http\Admin;

use Capsule\ClientDashboardConfig;
use Capsule\MediaRepository;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteRepository;

/**
 * Compteurs du tableau de bord client (contenu utile, pas le statut publié).
 */
final class DashboardStats
{
    /** @var array<string, array{label: string, icon: string, plural: string}> */
    private const ITEM_TYPES = [
        'blog' => ['label' => 'Article', 'plural' => 'Articles', 'icon' => 'fa-newspaper'],
        'projects' => ['label' => 'Projet', 'plural' => 'Projets', 'icon' => 'fa-briefcase'],
        'gallery' => ['label' => 'Média galerie', 'plural' => 'Médias galerie', 'icon' => 'fa-images'],
        'features' => ['label' => 'Fonctionnalité', 'plural' => 'Fonctionnalités', 'icon' => 'fa-table-cells'],
        'services' => ['label' => 'Service', 'plural' => 'Services', 'icon' => 'fa-concierge-bell'],
        'team' => ['label' => 'Membre', 'plural' => 'Membres', 'icon' => 'fa-users'],
        'testimonial' => ['label' => 'Témoignage', 'plural' => 'Témoignages', 'icon' => 'fa-quote-left'],
        'faq' => ['label' => 'Question', 'plural' => 'Questions', 'icon' => 'fa-circle-question'],
        'pricing' => ['label' => 'Offre', 'plural' => 'Offres', 'icon' => 'fa-tags'],
    ];

    public function __construct(
        private readonly SiteRepository $site,
        private readonly PageRepository $pages,
        private readonly MediaRepository $media,
    ) {
    }

    /**
     * @param list<PageListRow> $pageRows
     *
     * @return list<array{value: string, label: string, icon: string, href: string}>
     */
    public function build(array $pageRows): array
    {
        $config = $this->site->getClientDashboard();
        $cards = [
            [
                'value' => (string) count($pageRows),
                'label' => count($pageRows) <= 1 ? 'Page' : 'Pages',
                'icon' => 'fa-layer-group',
                'href' => '/admin/pages',
            ],
        ];

        $itemCounts = $this->countEditableItems($config);
        foreach (self::ITEM_TYPES as $type => $meta) {
            $count = $itemCounts[$type] ?? 0;
            if ($count <= 0) {
                continue;
            }
            $cards[] = [
                'value' => (string) $count,
                'label' => $count <= 1 ? $meta['label'] : $meta['plural'],
                'icon' => $meta['icon'],
                'href' => '/admin/pages',
            ];
        }

        if (ClientDashboardConfig::isMediasEnabled($config)) {
            $mediaCount = count($this->media->all(null, MediaRepository::OWNER_CLIENT));
            $cards[] = [
                'value' => (string) $mediaCount,
                'label' => $mediaCount <= 1 ? 'Média' : 'Médias',
                'icon' => 'fa-photo-film',
                'href' => '/admin/medias',
            ];
        }

        return $cards;
    }

    /**
     * @param list<array{value: string, label: string, icon: string, href: string}> $cards
     */
    public function renderCards(array $cards): string
    {
        if ($cards === []) {
            return '';
        }

        $html = [];
        foreach ($cards as $card) {
            $value = htmlspecialchars($card['value'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $label = htmlspecialchars($card['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $icon = htmlspecialchars($card['icon'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $href = htmlspecialchars($card['href'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html[] = '<a class="admin-stat-card" href="' . $href . '">'
                . '<span class="admin-stat-card__icon" aria-hidden="true"><i class="fa-solid ' . $icon . '"></i></span>'
                . '<p class="admin-stat-card__value">' . $value . '</p>'
                . '<p class="admin-stat-card__label">' . $label . '</p>'
                . '</a>';
        }

        return '<div class="admin-stats">' . implode('', $html) . '</div>';
    }

    /**
     * @param array{pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     *
     * @return array<string, int>
     */
    private function countEditableItems(array $config): array
    {
        $counts = [];
        $pagesBySlug = [];
        foreach ($this->pages->all() as $page) {
            $pagesBySlug[$page->slug] = $page;
        }

        foreach ($config['pages'] as $slug => $pageConfig) {
            if (!is_string($slug) || !is_array($pageConfig)) {
                continue;
            }
            $allowedSections = $pageConfig['sections'] ?? [];
            if (!is_array($allowedSections) || $allowedSections === []) {
                continue;
            }
            $page = $pagesBySlug[$slug] ?? null;
            if (!$page instanceof Page) {
                continue;
            }
            foreach ($page->sections as $section) {
                if (!is_array($section)) {
                    continue;
                }
                $sectionId = is_string($section['id'] ?? null) ? $section['id'] : '';
                $type = is_string($section['type'] ?? null) ? $section['type'] : '';
                if ($sectionId === '' || $type === '' || !isset($allowedSections[$sectionId])) {
                    continue;
                }
                if (!isset(self::ITEM_TYPES[$type])) {
                    continue;
                }
                $fields = $allowedSections[$sectionId]['fields'] ?? [];
                if (!is_array($fields) || $fields === []) {
                    continue;
                }
                $items = is_array($section['content']['items'] ?? null)
                    ? array_values($section['content']['items'])
                    : [];
                $n = 0;
                foreach ($items as $item) {
                    if (is_array($item) && $item !== []) {
                        $n++;
                    }
                }
                if ($n === 0) {
                    continue;
                }
                $counts[$type] = ($counts[$type] ?? 0) + $n;
            }
        }

        return $counts;
    }
}

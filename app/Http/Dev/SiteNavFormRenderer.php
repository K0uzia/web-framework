<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\PageRepository;
use Capsule\SiteNavHelper;

/**
 * Formulaire d'édition de la navigation globale du site (header et footer).
 */
final class SiteNavFormRenderer
{
    public function __construct(
        private readonly PageRepository $pages,
    ) {
    }

    /**
     * @param array<string, mixed> $site
     *
     * @return list<array<string, mixed>>
     */
    public function currentNavItems(array $site): array
    {
        $homeLabel = (string) ($site['home_label'] ?? 'Accueil');
        $items = is_array($site['nav_items'] ?? null) ? $site['nav_items'] : [];
        if ($items === [] || ($site['nav_mode'] ?? 'auto') === 'auto') {
            return SiteNavHelper::autoFromPages($this->pages, $homeLabel);
        }

        return SiteNavHelper::normalize($items);
    }

    /**
     * @param array<string, mixed> $site
     */
    public function rowsHtml(array $site, string $formId = 'nav-form'): string
    {
        $items = $this->currentNavItems($site);
        if ($items === []) {
            return '<p class="dev-empty"><i class="fa-solid fa-signs-post" aria-hidden="true"></i>Aucun lien. Ajoutez un lien ou synchronisez depuis les pages.</p>';
        }

        $rows = [];
        foreach ($items as $item) {
            $id = (string) ($item['id'] ?? '');
            $safeId = htmlspecialchars($id, ENT_QUOTES);
            $type = (string) ($item['type'] ?? 'page');
            $label = (string) ($item['label'] ?? '');
            $visible = ($item['visible'] ?? true) !== false;
            $href = (string) ($item['href'] ?? '');
            $slug = (string) ($item['slug'] ?? '');
            $target = $type === 'page' ? $this->pagePathForSlug($slug) : $href;

            if ($type === 'group') {
                $rows[] = $this->groupRowHtml($id, $label, $visible, is_array($item['children'] ?? null) ? $item['children'] : [], $formId);
                continue;
            }

            $rows[] = '<div class="dev-nav-row" data-dev-sortable-item data-id="' . $safeId . '" draggable="true" data-nav-row>'
                . '<button type="button" class="dev-icon-btn dev-icon-btn--drag" aria-label="Réorganiser"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></button>'
                . '<div class="dev-nav-row__fields">'
                . '<input class="dev-input dev-input--sm" type="text" name="nav_item_' . $safeId . '_label" form="' . htmlspecialchars($formId, ENT_QUOTES) . '" value="'
                . htmlspecialchars($label, ENT_QUOTES) . '" aria-label="Libellé" placeholder="Libellé" />'
                . '<select class="dev-input dev-select dev-select--sm" name="nav_item_' . $safeId . '_type" form="' . htmlspecialchars($formId, ENT_QUOTES) . '" aria-label="Type de lien" data-nav-row-type>'
                . '<option value="page"' . ($type === 'page' ? ' selected' : '') . '>Page du site</option>'
                . '<option value="link"' . ($type === 'link' ? ' selected' : '') . '>URL externe</option>'
                . '<option value="button"' . ($type === 'button' ? ' selected' : '') . '>Bouton mis en avant</option>'
                . '<option value="group"' . ($type === 'group' ? ' selected' : '') . '>Menu déroulant</option>'
                . '</select>'
                . '<div class="dev-nav-row__target" data-nav-row-target data-nav-type="' . htmlspecialchars($type, ENT_QUOTES) . '">'
                . LinkPicker::render('nav-target-' . $safeId, 'nav_item_' . $safeId . '_target', $target, $this->pages, $formId, true)
                . '</div>'
                . '</div>'
                . '<label class="dev-switch dev-switch--sm" title="Visible dans le menu">'
                . '<input type="hidden" name="nav_item_' . $safeId . '_visible" form="' . htmlspecialchars($formId, ENT_QUOTES) . '" value="0" />'
                . '<input type="checkbox" name="nav_item_' . $safeId . '_visible" form="' . htmlspecialchars($formId, ENT_QUOTES) . '" value="1"'
                . ($visible ? ' checked' : '') . ' />'
                . '<span class="dev-switch__track" aria-hidden="true"></span>'
                . '<span class="visually-hidden">Visible</span></label>'
                . '<div class="dev-nav-row__danger">'
                . '<button type="submit" class="dev-icon-btn dev-icon-btn--danger" form="nav-delete-' . $safeId . '" title="Supprimer" aria-label="Supprimer ce lien">'
                . '<i class="fa-solid fa-trash" aria-hidden="true"></i></button>'
                . '</div></div>';
        }

        return '<div class="dev-nav-editor" data-dev-sortable data-dev-sortable-url="/dev/site/nav/reorder">' . implode('', $rows) . '</div>';
    }

    /**
     * @param array<string, mixed> $site
     */
    public function deleteFormsHtml(array $site): string
    {
        $forms = [];
        foreach ($this->currentNavItems($site) as $item) {
            $id = (string) ($item['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $safeId = htmlspecialchars($id, ENT_QUOTES);
            $forms[] = '<form id="nav-delete-' . $safeId . '" class="dev-inline-form visually-hidden" method="post" action="/dev/site/nav/'
                . rawurlencode($id) . '/delete" data-dev-ajax="nav" data-dev-toast-form="Lien supprimé"></form>';
            if ((string) ($item['type'] ?? '') === 'group') {
                foreach (is_array($item['children'] ?? null) ? $item['children'] : [] as $child) {
                    $childId = (string) ($child['id'] ?? '');
                    if ($childId === '') {
                        continue;
                    }
                    $forms[] = '<form id="nav-child-delete-' . $safeId . '-' . htmlspecialchars($childId, ENT_QUOTES)
                        . '" class="dev-inline-form visually-hidden" method="post" action="/dev/site/nav/'
                        . rawurlencode($id) . '/children/' . rawurlencode($childId) . '/delete" data-dev-ajax="nav" data-dev-toast-form="Sous-lien supprimé"></form>';
                }
            }
        }

        return implode('', $forms);
    }

    /**
     * @param array<string, mixed> $site
     */
    public function navModeLabel(array $site): string
    {
        return ($site['nav_mode'] ?? 'auto') === 'custom' ? 'Personnalisée' : 'Automatique (pages publiées)';
    }

    /**
     * @param array<string, mixed> $site
     */
    public function pageOptionsHtml(array $site): string
    {
        $usedSlugs = [];
        foreach ($this->currentNavItems($site) as $item) {
            if (($item['type'] ?? '') === 'page') {
                $usedSlugs[] = (string) ($item['slug'] ?? '');
            }
        }

        $options = [];
        foreach ($this->pages->allPublished() as $page) {
            if (in_array($page->slug, $usedSlugs, true)) {
                continue;
            }
            $options[] = '<option value="' . htmlspecialchars($page->slug, ENT_QUOTES) . '">'
                . htmlspecialchars($page->routePath() . ' : ' . $page->title, ENT_QUOTES) . '</option>';
        }

        return implode('', $options);
    }

    /**
     * @param array<string, mixed> $item
     */
    public function hrefFromItem(array $item): string
    {
        $href = trim((string) ($item['href'] ?? ''));
        if ($href !== '') {
            return $href;
        }

        if ((string) ($item['type'] ?? 'page') === 'page') {
            return $this->pagePathForSlug((string) ($item['slug'] ?? ''));
        }

        return '';
    }

    public function slugFromTarget(string $target): string
    {
        $target = trim($target);
        if ($target === '' || $target === '/') {
            return '';
        }

        $path = parse_url($target, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = $target;
        }

        return ltrim($path, '/');
    }

    public function pagePathForSlug(string $slug): string
    {
        return $slug === '' ? '/' : '/' . $slug;
    }

    /**
     * @param list<array<string, mixed>> $children
     */
    private function groupRowHtml(string $id, string $label, bool $visible, array $children, string $formId): string
    {
        $safeId = htmlspecialchars($id, ENT_QUOTES);
        $safeFormId = htmlspecialchars($formId, ENT_QUOTES);
        $childRows = '';
        foreach ($children as $child) {
            $childId = (string) ($child['id'] ?? '');
            if ($childId === '') {
                continue;
            }
            $safeChildId = htmlspecialchars($childId, ENT_QUOTES);
            $childType = (string) ($child['type'] ?? 'page');
            $childLabel = (string) ($child['label'] ?? '');
            $childVisible = ($child['visible'] ?? true) !== false;
            $childSlug = (string) ($child['slug'] ?? '');
            $childHref = (string) ($child['href'] ?? '');
            $childTarget = $childType === 'page' ? $this->pagePathForSlug($childSlug) : $childHref;
            $prefix = 'nav_child_' . $safeId . '_' . $safeChildId . '_';

            $childRows .= '<div class="dev-nav-child-row">'
                . '<input class="dev-input dev-input--sm" type="text" name="' . $prefix . 'label" form="' . $safeFormId . '" value="'
                . htmlspecialchars($childLabel, ENT_QUOTES) . '" aria-label="Libellé du sous-lien" placeholder="Sous-lien" />'
                . '<select class="dev-input dev-select dev-select--sm" name="' . $prefix . 'type" form="' . $safeFormId . '" aria-label="Type du sous-lien">'
                . '<option value="page"' . ($childType === 'page' ? ' selected' : '') . '>Page</option>'
                . '<option value="link"' . ($childType === 'link' ? ' selected' : '') . '>URL</option>'
                . '<option value="button"' . ($childType === 'button' ? ' selected' : '') . '>Bouton</option>'
                . '</select>'
                . LinkPicker::render('nav-child-' . $childId, $prefix . 'target', $childTarget, $this->pages, $formId, true)
                . '<input type="hidden" name="' . $prefix . 'visible" form="' . $safeFormId . '" value="0" />'
                . '<label class="dev-switch dev-switch--sm" title="Visible">'
                . '<input type="checkbox" name="' . $prefix . 'visible" form="' . $safeFormId . '" value="1"' . ($childVisible ? ' checked' : '') . ' />'
                . '<span class="dev-switch__track" aria-hidden="true"></span></label>'
                . '<button type="submit" class="dev-icon-btn dev-icon-btn--danger" form="nav-child-delete-'
                . $safeId . '-' . $safeChildId . '" title="Supprimer le sous-lien" aria-label="Supprimer le sous-lien">'
                . '<i class="fa-solid fa-trash" aria-hidden="true"></i></button>'
                . '</div>';
        }

        return '<div class="dev-nav-row dev-nav-row--group" data-dev-sortable-item data-id="' . $safeId . '" draggable="true" data-nav-row>'
            . '<button type="button" class="dev-icon-btn dev-icon-btn--drag" aria-label="Réorganiser"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></button>'
            . '<div class="dev-nav-row__fields dev-nav-row__fields--group">'
            . '<input class="dev-input dev-input--sm" type="text" name="nav_item_' . $safeId . '_label" form="' . $safeFormId . '" value="'
            . htmlspecialchars($label, ENT_QUOTES) . '" aria-label="Libellé du menu" placeholder="Menu déroulant" />'
            . '<input type="hidden" name="nav_item_' . $safeId . '_type" form="' . $safeFormId . '" value="group" />'
            . '<span class="dev-badge dev-badge--info">Menu déroulant</span>'
            . '<div class="dev-nav-children">' . $childRows
            . '<form method="post" action="/dev/site/nav/' . rawurlencode($id) . '/children" class="dev-nav-child-add" data-dev-ajax="nav" data-dev-toast-form="Sous-lien ajouté">'
            . '<input class="dev-input dev-input--sm" type="text" name="nav_child_label" placeholder="Nouveau sous-lien" aria-label="Libellé du nouveau sous-lien" required />'
            . '<select class="dev-input dev-select dev-select--sm" name="nav_child_type" aria-label="Type du nouveau sous-lien">'
            . '<option value="page">Page</option><option value="link">URL</option><option value="button">Bouton</option>'
            . '</select>'
            . LinkPicker::render('nav-child-new-' . $id, 'nav_child_target', '', $this->pages, '', false)
            . '<button type="submit" class="dev-button dev-button--ghost dev-button--sm"><i class="fa-solid fa-plus" aria-hidden="true"></i> Sous-lien</button>'
            . '</form></div></div>'
            . '<label class="dev-switch dev-switch--sm" title="Visible dans le menu">'
            . '<input type="hidden" name="nav_item_' . $safeId . '_visible" form="' . $safeFormId . '" value="0" />'
            . '<input type="checkbox" name="nav_item_' . $safeId . '_visible" form="' . $safeFormId . '" value="1"' . ($visible ? ' checked' : '') . ' />'
            . '<span class="dev-switch__track" aria-hidden="true"></span></label>'
            . '<div class="dev-nav-row__danger">'
            . '<button type="submit" class="dev-icon-btn dev-icon-btn--danger" form="nav-delete-' . $safeId . '" title="Supprimer" aria-label="Supprimer ce menu">'
            . '<i class="fa-solid fa-trash" aria-hidden="true"></i></button>'
            . '</div></div>';
    }
}

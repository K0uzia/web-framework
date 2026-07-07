<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\DevDashboard;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\FormData;
use Capsule\PageRepository;
use Capsule\SiteNavHelper;
use Capsule\SiteRepository;

final class SiteController
{
    use DevHx;

    public function __construct(
        private readonly DevDashboard $ui,
        private readonly SiteRepository $site,
        private readonly PageRepository $pages,
        private readonly MediaUploader $media,
    ) {
    }

    public function edit(Request $request): Response
    {
        $site = $this->site->getSite();

        return $this->ui->render('site-edit.html', [
            'title' => 'Site',
            'crumb_html' => Breadcrumb::render([['label' => 'Site']]),
            'site_name' => (string) ($site['name'] ?? ''),
            'site_tagline' => (string) ($site['tagline'] ?? ''),
            'home_label' => (string) ($site['home_label'] ?? 'Accueil'),
            'logo_uploader_html' => MediaFieldView::render('logo', (string) ($site['logo_url'] ?? ''), $this->media->acceptAttribute('logo')),
            'favicon_uploader_html' => MediaFieldView::render('favicon', (string) ($site['favicon_url'] ?? ''), $this->media->acceptAttribute('favicon')),
            'og_image_uploader_html' => MediaFieldView::render('og_image', (string) ($site['og_image_url'] ?? ''), $this->media->acceptAttribute('og_image')),
            'nav_mode_label' => ($site['nav_mode'] ?? 'auto') === 'custom' ? 'Personnalisée' : 'Automatique (pages publiées)',
            'nav_panel_html' => $this->ui->partialHtml('nav-panel.html', [
                'nav_rows_html' => $this->buildNavRowsHtml($site),
                'nav_delete_forms_html' => $this->buildNavDeleteFormsHtml($site),
                'message' => '',
            ]),
            'page_options_html' => $this->buildPageOptionsHtml($site),
            'nav_add_target_html' => LinkPicker::render('nav_target', 'nav_target', '', $this->pages),
            'flash' => $this->ui->flashFromRequest($request),
        ]);
    }

    public function update(Request $request): Response
    {
        $data = FormData::fromRequest($request);
        $site = $this->site->getSite();

        // Le site est édité via plusieurs formulaires indépendants qui s'auto-sauvegardent
        // séparément : chaque requête ne contient que les champs du formulaire actif.
        // Les champs absents conservent donc leur valeur existante. L'en-tête et le pied
        // de page se gèrent dans l'éditeur dédié (/dev/chrome).
        $site['name'] = trim($data['site_name'] ?? (string) ($site['name'] ?? ''));
        $site['tagline'] = trim($data['site_tagline'] ?? (string) ($site['tagline'] ?? ''));
        $site['home_label'] = trim($data['home_label'] ?? (string) ($site['home_label'] ?? 'Accueil'));
        $site['logo_url'] = trim($data['logo_url'] ?? (string) ($site['logo_url'] ?? ''));
        $site['favicon_url'] = trim($data['favicon_url'] ?? (string) ($site['favicon_url'] ?? ''));
        $site['og_image_url'] = trim($data['og_image_url'] ?? (string) ($site['og_image_url'] ?? ''));

        if (($data['update_nav'] ?? '0') === '1') {
            $site = $this->applyNavFormData($site, $data);
        }

        $this->site->setSite($site);

        return $this->respondSaved($request, 'Site enregistré');
    }

    public function updateNav(Request $request): Response
    {
        $data = FormData::fromRequest($request);
        $site = $this->site->getSite();
        $site = $this->applyNavFormData($site, $data);
        $this->site->setSite($site);

        return $this->respondNav($request, 'Navigation enregistrée');
    }

    public function addNav(Request $request): Response
    {
        $data = FormData::fromRequest($request);
        $site = $this->site->getSite();
        $items = $this->currentNavItems($site);

        $type = trim($data['nav_type'] ?? 'page');
        $label = trim($data['nav_label'] ?? '');
        $target = trim($data['nav_target'] ?? '');
        if ($label === '') {
            return $this->respondNav($request, '');
        }

        $entry = [
            'id' => 'nav-' . bin2hex(random_bytes(4)),
            'type' => $type,
            'slug' => '',
            'href' => '',
            'label' => $label,
            'visible' => true,
        ];

        if ($type === 'page') {
            $entry['slug'] = $this->slugFromTarget($target);
        } elseif ($type === 'group') {
            $entry['children'] = [];
        } elseif ($target === '') {
            return $this->respondNav($request, '');
        } else {
            $entry['href'] = $target;
        }

        $items[] = $entry;
        $site['nav_items'] = $items;
        $site['nav_mode'] = 'custom';
        $this->site->setSite($site);

        return $this->respondNav($request, 'Lien ajouté');
    }

    public function addNavChild(Request $request, string $groupId): Response
    {
        $data = FormData::fromRequest($request);
        $site = $this->site->getSite();
        $items = $this->currentNavItems($site);
        $index = $this->findNavIndex($items, $groupId);
        if ($index < 0 || (string) ($items[$index]['type'] ?? '') !== 'group') {
            return $this->respondNav($request, '');
        }

        $type = trim($data['nav_child_type'] ?? 'page');
        $label = trim($data['nav_child_label'] ?? '');
        $target = trim($data['nav_child_target'] ?? '');
        if ($label === '') {
            return $this->respondNav($request, '');
        }

        $child = [
            'id' => 'nav-' . bin2hex(random_bytes(4)),
            'type' => $type,
            'slug' => '',
            'href' => '',
            'label' => $label,
            'visible' => true,
        ];
        if ($type === 'page') {
            $child['slug'] = $this->slugFromTarget($target);
        } else {
            if ($target === '') {
                return $this->respondNav($request, '');
            }
            $child['href'] = $target;
        }

        $children = is_array($items[$index]['children'] ?? null) ? $items[$index]['children'] : [];
        $children[] = $child;
        $items[$index]['children'] = $children;
        $site['nav_items'] = $items;
        $site['nav_mode'] = 'custom';
        $this->site->setSite($site);

        return $this->respondNav($request, 'Sous-lien ajouté');
    }

    public function deleteNavChild(Request $request, string $groupId, string $childId): Response
    {
        $site = $this->site->getSite();
        $items = $this->currentNavItems($site);
        $index = $this->findNavIndex($items, $groupId);
        if ($index < 0) {
            return $this->respondNav($request, '');
        }

        $children = is_array($items[$index]['children'] ?? null) ? $items[$index]['children'] : [];
        $items[$index]['children'] = array_values(array_filter(
            $children,
            static fn (array $child): bool => ($child['id'] ?? '') !== $childId,
        ));
        $site['nav_items'] = $items;
        $site['nav_mode'] = 'custom';
        $this->site->setSite($site);

        return $this->respondNav($request, 'Sous-lien supprimé');
    }

    public function moveNav(Request $request, string $id): Response
    {
        $data = FormData::fromRequest($request);
        $site = $this->site->getSite();
        $items = $this->currentNavItems($site);
        $index = $this->findNavIndex($items, $id);
        if ($index < 0) {
            return $this->respondNav($request, '');
        }

        $swap = ($data['direction'] ?? '') === 'up' ? $index - 1 : $index + 1;
        if ($swap < 0 || $swap >= count($items)) {
            return $this->respondNav($request, '');
        }

        [$items[$index], $items[$swap]] = [$items[$swap], $items[$index]];
        $site['nav_items'] = $items;
        $site['nav_mode'] = 'custom';
        $this->site->setSite($site);

        return $this->respondNav($request, '');
    }

    public function deleteNav(Request $request, string $id): Response
    {
        $site = $this->site->getSite();
        $items = array_values(array_filter(
            $this->currentNavItems($site),
            static fn (array $item): bool => ($item['id'] ?? '') !== $id,
        ));
        $site['nav_items'] = $items;
        $site['nav_mode'] = 'custom';
        $this->site->setSite($site);

        return $this->respondNav($request, 'Lien supprimé');
    }

    public function syncNav(Request $request): Response
    {
        $site = $this->site->getSite();
        $homeLabel = (string) ($site['home_label'] ?? 'Accueil');
        $items = SiteNavHelper::syncPages($this->currentNavItems($site), $this->pages, $homeLabel);
        $site['nav_items'] = $items;
        $site['nav_mode'] = 'custom';
        $this->site->setSite($site);

        return $this->respondNav($request, 'Navigation synchronisée');
    }

    public function reorderNav(Request $request): Response
    {
        $data = FormData::fromRequest($request);
        $raw = $data['order'] ?? '';
        $order = $raw === '' ? [] : array_values(array_filter(array_map('trim', explode(',', $raw)), static fn ($v) => $v !== ''));

        if ($order !== []) {
            $site = $this->site->getSite();
            $items = $this->currentNavItems($site);

            $byId = [];
            foreach ($items as $item) {
                $byId[(string) ($item['id'] ?? '')] = $item;
            }

            $reordered = [];
            foreach ($order as $id) {
                if (isset($byId[$id])) {
                    $reordered[] = $byId[$id];
                    unset($byId[$id]);
                }
            }
            foreach ($byId as $remaining) {
                $reordered[] = $remaining;
            }

            $site['nav_items'] = $reordered;
            $site['nav_mode'] = 'custom';
            $this->site->setSite($site);
        }

        return $this->respondNav($request, '');
    }

    public function resetNav(Request $request): Response
    {
        $site = $this->site->getSite();
        $site['nav_mode'] = 'auto';
        $site['nav_items'] = [];
        $this->site->setSite($site);

        return $this->respondNav($request, 'Navigation automatique restaurée');
    }

    private function respondSaved(Request $request, string $message): Response
    {
        if ($this->isHx($request)) {
            return $this->ui->partial('saved.html', ['message' => $message]);
        }

        return $this->ui->withFlash($this->ui->redirect('/dev/site'), $message);
    }

    private function respondNav(Request $request, string $message): Response
    {
        if ($this->isHx($request)) {
            $site = $this->site->getSite();

            return $this->ui->partial('nav-panel.html', [
                'nav_rows_html' => $this->buildNavRowsHtml($site),
                'nav_delete_forms_html' => $this->buildNavDeleteFormsHtml($site),
                'message' => $message,
            ]);
        }

        if ($message !== '') {
            return $this->ui->withFlash($this->ui->redirect('/dev/site'), $message);
        }

        return $this->ui->redirect('/dev/site');
    }

    /**
     * @param array<string, mixed> $site
     *
     * @return list<array<string, mixed>>
     */
    private function currentNavItems(array $site): array
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
     * @param array<string, string>  $data
     *
     * @return array<string, mixed>
     */
    private function applyNavFormData(array $site, array $data): array
    {
        $items = $this->currentNavItems($site);
        foreach ($items as $i => $item) {
            $id = (string) ($item['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $prefix = 'nav_item_' . $id . '_';
            if (isset($data[$prefix . 'label'])) {
                $items[$i]['label'] = trim($data[$prefix . 'label']);
            }
            $items[$i]['visible'] = ($data[$prefix . 'visible'] ?? '1') === '1';

            if (isset($data[$prefix . 'type'])) {
                $type = trim($data[$prefix . 'type']);
                if (in_array($type, ['page', 'link', 'button', 'group'], true)) {
                    $items[$i]['type'] = $type;
                }
            }

            $type = (string) ($items[$i]['type'] ?? 'page');

            if ($type === 'group') {
                $items[$i]['children'] = $this->applyNavChildrenFormData(
                    is_array($items[$i]['children'] ?? null) ? $items[$i]['children'] : [],
                    $data,
                    $id,
                );
                continue;
            }

            if (isset($data[$prefix . 'target'])) {
                $target = trim($data[$prefix . 'target']);
                if ($type === 'page') {
                    $slug = $this->slugFromTarget($target);
                    if ($slug === '' && $target === '') {
                        $slug = trim((string) ($item['slug'] ?? ''));
                    }
                    $items[$i]['slug'] = $slug;
                    $items[$i]['href'] = '';
                } else {
                    $href = $target;
                    if ($href === '') {
                        $href = $this->hrefFromItem($item);
                    }
                    $items[$i]['href'] = $href;
                    $items[$i]['slug'] = '';
                }
            } elseif (isset($data[$prefix . 'type'])) {
                if ($type === 'page') {
                    $items[$i]['slug'] = trim($data[$prefix . 'slug'] ?? (string) ($item['slug'] ?? ''));
                    $items[$i]['href'] = '';
                } else {
                    $href = trim($data[$prefix . 'href'] ?? (string) ($item['href'] ?? ''));
                    if ($href === '') {
                        $href = $this->hrefFromItem($item);
                    }
                    $items[$i]['href'] = $href;
                    $items[$i]['slug'] = '';
                }
            }
        }

        $site['nav_items'] = $items;
        $site['nav_mode'] = 'custom';

        return $site;
    }

    /**
     * @param list<array<string, mixed>> $children
     * @param array<string, string>      $data
     *
     * @return list<array<string, mixed>>
     */
    private function applyNavChildrenFormData(array $children, array $data, string $groupId): array
    {
        foreach ($children as $i => $child) {
            $childId = (string) ($child['id'] ?? '');
            if ($childId === '') {
                continue;
            }
            $prefix = 'nav_child_' . $groupId . '_' . $childId . '_';
            if (isset($data[$prefix . 'label'])) {
                $children[$i]['label'] = trim($data[$prefix . 'label']);
            }
            $children[$i]['visible'] = ($data[$prefix . 'visible'] ?? '1') === '1';
            $type = (string) ($children[$i]['type'] ?? 'page');
            if (isset($data[$prefix . 'type']) && in_array($data[$prefix . 'type'], ['page', 'link', 'button'], true)) {
                $children[$i]['type'] = $data[$prefix . 'type'];
                $type = $data[$prefix . 'type'];
            }
            if (isset($data[$prefix . 'target'])) {
                $target = trim($data[$prefix . 'target']);
                if ($type === 'page') {
                    $children[$i]['slug'] = $this->slugFromTarget($target);
                    $children[$i]['href'] = '';
                } else {
                    $children[$i]['href'] = $target;
                    $children[$i]['slug'] = '';
                }
            }
        }

        return $children;
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private function findNavIndex(array $items, string $id): int
    {
        foreach ($items as $i => $item) {
            if (($item['id'] ?? '') === $id) {
                return $i;
            }
        }

        return -1;
    }

    /**
     * @param array<string, mixed> $site
     */
    private function buildNavRowsHtml(array $site): string
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
                $rows[] = $this->buildNavGroupRowHtml($id, $label, $visible, is_array($item['children'] ?? null) ? $item['children'] : []);
                continue;
            }

            $rows[] = '<div class="dev-nav-row" data-dev-sortable-item data-id="' . $safeId . '" draggable="true" data-nav-row>'
                . '<button type="button" class="dev-icon-btn dev-icon-btn--drag" aria-label="Réorganiser"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></button>'
                . '<div class="dev-nav-row__fields">'
                . '<input class="dev-input dev-input--sm" type="text" name="nav_item_' . $safeId . '_label" form="nav-form" value="'
                . htmlspecialchars($label, ENT_QUOTES) . '" aria-label="Libellé" placeholder="Libellé" />'
                . '<select class="dev-input dev-select dev-select--sm" name="nav_item_' . $safeId . '_type" form="nav-form" aria-label="Type de lien" data-nav-row-type>'
                . '<option value="page"' . ($type === 'page' ? ' selected' : '') . '>Page du site</option>'
                . '<option value="link"' . ($type === 'link' ? ' selected' : '') . '>URL externe</option>'
                . '<option value="button"' . ($type === 'button' ? ' selected' : '') . '>Bouton mis en avant</option>'
                . '<option value="group"' . ($type === 'group' ? ' selected' : '') . '>Menu déroulant</option>'
                . '</select>'
                . '<div class="dev-nav-row__target" data-nav-row-target data-nav-type="' . htmlspecialchars($type, ENT_QUOTES) . '">'
                . LinkPicker::render('nav-target-' . $safeId, 'nav_item_' . $safeId . '_target', $target, $this->pages, 'nav-form', true)
                . '</div>'
                . '</div>'
                . '<label class="dev-switch dev-switch--sm" title="Visible dans le menu">'
                . '<input type="hidden" name="nav_item_' . $safeId . '_visible" form="nav-form" value="0" />'
                . '<input type="checkbox" name="nav_item_' . $safeId . '_visible" form="nav-form" value="1"'
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
    private function buildNavDeleteFormsHtml(array $site): string
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
     * @param list<array<string, mixed>> $children
     */
    private function buildNavGroupRowHtml(string $id, string $label, bool $visible, array $children): string
    {
        $safeId = htmlspecialchars($id, ENT_QUOTES);
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
                . '<input class="dev-input dev-input--sm" type="text" name="' . $prefix . 'label" form="nav-form" value="'
                . htmlspecialchars($childLabel, ENT_QUOTES) . '" aria-label="Libellé du sous-lien" placeholder="Sous-lien" />'
                . '<select class="dev-input dev-select dev-select--sm" name="' . $prefix . 'type" form="nav-form" aria-label="Type du sous-lien">'
                . '<option value="page"' . ($childType === 'page' ? ' selected' : '') . '>Page</option>'
                . '<option value="link"' . ($childType === 'link' ? ' selected' : '') . '>URL</option>'
                . '<option value="button"' . ($childType === 'button' ? ' selected' : '') . '>Bouton</option>'
                . '</select>'
                . LinkPicker::render('nav-child-' . $childId, $prefix . 'target', $childTarget, $this->pages, 'nav-form', true)
                . '<input type="hidden" name="' . $prefix . 'visible" form="nav-form" value="0" />'
                . '<label class="dev-switch dev-switch--sm" title="Visible">'
                . '<input type="checkbox" name="' . $prefix . 'visible" form="nav-form" value="1"' . ($childVisible ? ' checked' : '') . ' />'
                . '<span class="dev-switch__track" aria-hidden="true"></span></label>'
                . '<button type="submit" class="dev-icon-btn dev-icon-btn--danger" form="nav-child-delete-'
                . $safeId . '-' . $safeChildId . '" title="Supprimer le sous-lien" aria-label="Supprimer le sous-lien">'
                . '<i class="fa-solid fa-trash" aria-hidden="true"></i></button>'
                . '</div>';
        }

        return '<div class="dev-nav-row dev-nav-row--group" data-dev-sortable-item data-id="' . $safeId . '" draggable="true" data-nav-row>'
            . '<button type="button" class="dev-icon-btn dev-icon-btn--drag" aria-label="Réorganiser"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></button>'
            . '<div class="dev-nav-row__fields dev-nav-row__fields--group">'
            . '<input class="dev-input dev-input--sm" type="text" name="nav_item_' . $safeId . '_label" form="nav-form" value="'
            . htmlspecialchars($label, ENT_QUOTES) . '" aria-label="Libellé du menu" placeholder="Menu déroulant" />'
            . '<input type="hidden" name="nav_item_' . $safeId . '_type" form="nav-form" value="group" />'
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
            . '<input type="hidden" name="nav_item_' . $safeId . '_visible" form="nav-form" value="0" />'
            . '<input type="checkbox" name="nav_item_' . $safeId . '_visible" form="nav-form" value="1"' . ($visible ? ' checked' : '') . ' />'
            . '<span class="dev-switch__track" aria-hidden="true"></span></label>'
            . '<div class="dev-nav-row__danger">'
            . '<button type="submit" class="dev-icon-btn dev-icon-btn--danger" form="nav-delete-' . $safeId . '" title="Supprimer" aria-label="Supprimer ce menu">'
            . '<i class="fa-solid fa-trash" aria-hidden="true"></i></button>'
            . '</div></div>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private function hrefFromItem(array $item): string
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

    /**
     * @param array<string, mixed> $site
     */
    private function buildPageOptionsHtml(array $site): string
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

    private function buildAllPageOptionsHtml(string $selectedSlug): string
    {
        $options = [];
        foreach ($this->pages->allPublished() as $page) {
            $options[] = '<option value="' . htmlspecialchars($page->slug, ENT_QUOTES) . '"'
                . ($page->slug === $selectedSlug ? ' selected' : '') . '>'
                . htmlspecialchars($page->routePath() . ' : ' . $page->title, ENT_QUOTES) . '</option>';
        }

        return implode('', $options);
    }

    private function pagePathForSlug(string $slug): string
    {
        return $slug === '' ? '/' : '/' . $slug;
    }

    private function slugFromTarget(string $target): string
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
}

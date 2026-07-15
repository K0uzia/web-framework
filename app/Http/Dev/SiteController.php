<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\DevDashboard;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\FormData;
use Capsule\MediaLibrary;
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
        private readonly MediaLibrary $library,
        private readonly SiteNavFormRenderer $navForms,
    ) {
    }

    public function edit(Request $request): Response
    {
        $site = $this->site->getSite();
        $this->library->syncDiscoveredRecords('image');
        $imageLibrary = $this->library->availableImageUrls();

        return $this->ui->render('site-edit.html', [
            'title' => 'Site',
            'crumb_html' => Breadcrumb::render([['label' => 'Site']]),
            'site_name' => (string) ($site['name'] ?? ''),
            'site_tagline' => (string) ($site['tagline'] ?? ''),
            'home_label' => (string) ($site['home_label'] ?? 'Accueil'),
            'logo_uploader_html' => MediaFieldView::render('logo', (string) ($site['logo_url'] ?? ''), $this->media->acceptAttribute('logo'), $imageLibrary),
            'favicon_uploader_html' => MediaFieldView::render('favicon', (string) ($site['favicon_url'] ?? ''), $this->media->acceptAttribute('favicon'), $imageLibrary),
            'og_image_uploader_html' => MediaFieldView::render('og_image', (string) ($site['og_image_url'] ?? ''), $this->media->acceptAttribute('og_image'), $imageLibrary),
            'nav_mode_label' => ($site['nav_mode'] ?? 'auto') === 'custom' ? 'Personnalisée' : 'Automatique (pages publiées)',
            'nav_panel_html' => $this->ui->partialHtml('nav-panel.html', [
                'nav_rows_html' => $this->navForms->rowsHtml($site),
                'nav_delete_forms_html' => $this->navForms->deleteFormsHtml($site),
                'message' => '',
            ]),
            'page_options_html' => $this->navForms->pageOptionsHtml($site),
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
        $items = $this->navForms->currentNavItems($site);

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
            $entry['slug'] = $this->navForms->slugFromTarget($target);
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
        $items = $this->navForms->currentNavItems($site);
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
            $child['slug'] = $this->navForms->slugFromTarget($target);
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
        $items = $this->navForms->currentNavItems($site);
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
        $items = $this->navForms->currentNavItems($site);
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
            $this->navForms->currentNavItems($site),
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
        $items = SiteNavHelper::syncPages($this->navForms->currentNavItems($site), $this->pages, $homeLabel);
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
            $items = $this->navForms->currentNavItems($site);

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
                'nav_rows_html' => $this->navForms->rowsHtml($site),
                'nav_delete_forms_html' => $this->navForms->deleteFormsHtml($site),
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
     * @param array<string, string>  $data
     *
     * @return array<string, mixed>
     */
    private function applyNavFormData(array $site, array $data): array
    {
        $items = $this->navForms->currentNavItems($site);
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
                    $slug = $this->navForms->slugFromTarget($target);
                    if ($slug === '' && $target === '') {
                        $slug = trim((string) ($item['slug'] ?? ''));
                    }
                    $items[$i]['slug'] = $slug;
                    $items[$i]['href'] = '';
                } else {
                    $href = $target;
                    if ($href === '') {
                        $href = $this->navForms->hrefFromItem($item);
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
                        $href = $this->navForms->hrefFromItem($item);
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
                    $children[$i]['slug'] = $this->navForms->slugFromTarget($target);
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
}

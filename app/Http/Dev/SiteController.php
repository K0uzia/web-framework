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
        $partials = is_array($site['partials'] ?? null) ? $site['partials'] : [];
        $headerCta = is_array($site['header_cta'] ?? null) ? $site['header_cta'] : [];

        return $this->ui->render('site-edit.html', [
            'title' => 'Site',
            'crumb_html' => Breadcrumb::render([['label' => 'Site']]),
            'site_name' => (string) ($site['name'] ?? ''),
            'site_tagline' => (string) ($site['tagline'] ?? ''),
            'footer_text' => (string) ($site['footer_text'] ?? ''),
            'home_label' => (string) ($site['home_label'] ?? 'Accueil'),
            'partial_header_checked' => ($partials['header'] ?? true) !== false ? 'checked' : '',
            'partial_footer_checked' => ($partials['footer'] ?? true) !== false ? 'checked' : '',
            'header_cta_enabled_checked' => ($headerCta['enabled'] ?? false) === true ? 'checked' : '',
            'header_cta_label' => (string) ($headerCta['label'] ?? ''),
            'header_cta_href' => (string) ($headerCta['href'] ?? ''),
            'header_cta_target_html' => LinkPicker::render(
                'header_cta_href',
                'header_cta_href',
                (string) ($headerCta['href'] ?? ''),
                $this->pages,
                'header-form',
            ),
            'show_tagline_checked' => ($site['show_tagline_in_header'] ?? false) === true ? 'checked' : '',
            'logo_url' => (string) ($site['logo_url'] ?? ''),
            'favicon_url' => (string) ($site['favicon_url'] ?? ''),
            'og_image_url' => (string) ($site['og_image_url'] ?? ''),
            'logo_uploader_html' => MediaFieldView::render('logo', (string) ($site['logo_url'] ?? ''), $this->media->acceptAttribute('logo')),
            'favicon_uploader_html' => MediaFieldView::render('favicon', (string) ($site['favicon_url'] ?? ''), $this->media->acceptAttribute('favicon')),
            'og_image_uploader_html' => MediaFieldView::render('og_image', (string) ($site['og_image_url'] ?? ''), $this->media->acceptAttribute('og_image')),
            'nav_mode_label' => ($site['nav_mode'] ?? 'auto') === 'custom' ? 'Personnalisée' : 'Automatique (pages publiées)',
            'nav_panel_html' => $this->ui->partialHtml('nav-panel.html', [
                'nav_rows_html' => $this->buildNavRowsHtml($site),
                'message' => '',
            ]),
            'page_options_html' => $this->buildPageOptionsHtml($site),
            'flash' => $this->ui->flashFromRequest($request),
        ]);
    }

    public function update(Request $request): Response
    {
        $data = FormData::fromRequest($request);
        $site = $this->site->getSite();

        // Le site est édité via plusieurs formulaires indépendants (un par onglet) qui
        // s'auto-sauvegardent séparément : chaque requête ne contient que les champs de
        // l'onglet actif. Les champs absents doivent donc conserver leur valeur existante
        // plutôt que d'être réinitialisés (cases à cocher en particulier).
        $existingPartials = is_array($site['partials'] ?? null) ? $site['partials'] : [];
        $existingCta = is_array($site['header_cta'] ?? null) ? $site['header_cta'] : [];

        $site['name'] = trim($data['site_name'] ?? (string) ($site['name'] ?? ''));
        $site['tagline'] = trim($data['site_tagline'] ?? (string) ($site['tagline'] ?? ''));
        $site['footer_text'] = trim($data['footer_text'] ?? (string) ($site['footer_text'] ?? ''));
        $site['home_label'] = trim($data['home_label'] ?? (string) ($site['home_label'] ?? 'Accueil'));
        $site['partials'] = [
            'header' => isset($data['partial_header'])
                ? $data['partial_header'] === '1'
                : (($existingPartials['header'] ?? true) !== false),
            'footer' => isset($data['partial_footer'])
                ? $data['partial_footer'] === '1'
                : (($existingPartials['footer'] ?? true) !== false),
        ];
        $site['header_cta'] = [
            'enabled' => isset($data['header_cta_enabled'])
                ? $data['header_cta_enabled'] === '1'
                : (($existingCta['enabled'] ?? false) === true),
            'label' => isset($data['header_cta_label']) ? trim($data['header_cta_label']) : (string) ($existingCta['label'] ?? ''),
            'href' => isset($data['header_cta_href']) ? trim($data['header_cta_href']) : (string) ($existingCta['href'] ?? ''),
        ];
        $site['show_tagline_in_header'] = isset($data['show_tagline_in_header'])
            ? $data['show_tagline_in_header'] === '1'
            : (($site['show_tagline_in_header'] ?? false) === true);
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

        $type = trim($data['nav_type'] ?? 'link');
        $label = trim($data['nav_label'] ?? '');
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
            $entry['slug'] = trim($data['nav_slug'] ?? '');
        } else {
            $entry['href'] = trim($data['nav_href'] ?? '');
            if ($entry['href'] === '') {
                return $this->respondNav($request, '');
            }
        }

        $items[] = $entry;
        $site['nav_items'] = $items;
        $site['nav_mode'] = 'custom';
        $this->site->setSite($site);

        return $this->respondNav($request, 'Lien ajouté');
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
                if (in_array($type, ['page', 'link', 'button'], true)) {
                    $items[$i]['type'] = $type;
                    if ($type === 'page') {
                        $items[$i]['slug'] = trim($data[$prefix . 'slug'] ?? (string) ($item['slug'] ?? ''));
                    } else {
                        $items[$i]['href'] = trim($data[$prefix . 'href'] ?? (string) ($item['href'] ?? ''));
                    }
                }
            }
        }

        $site['nav_items'] = $items;
        $site['nav_mode'] = 'custom';

        return $site;
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

            $rows[] = '<div class="dev-nav-row" data-dev-sortable-item data-id="' . $safeId . '" draggable="true" data-nav-row>'
                . '<button type="button" class="dev-icon-btn dev-icon-btn--drag" aria-label="Réorganiser"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></button>'
                . '<div class="dev-nav-row__fields">'
                . '<input class="dev-input dev-input--sm" type="text" name="nav_item_' . $safeId . '_label" form="nav-form" value="'
                . htmlspecialchars($label, ENT_QUOTES) . '" aria-label="Libellé" placeholder="Libellé" />'
                . '<select class="dev-input dev-select dev-select--sm" name="nav_item_' . $safeId . '_type" form="nav-form" aria-label="Style" data-nav-row-type>'
                . '<option value="page"' . ($type === 'page' ? ' selected' : '') . '>Page</option>'
                . '<option value="link"' . ($type === 'link' ? ' selected' : '') . '>Lien externe</option>'
                . '<option value="button"' . ($type === 'button' ? ' selected' : '') . '>Bouton</option>'
                . '</select>'
                . '<select class="dev-input dev-select dev-select--sm" name="nav_item_' . $safeId . '_slug" form="nav-form" aria-label="Page cible"'
                . ($type !== 'page' ? ' hidden' : '') . ' data-nav-row-slug>'
                . $this->buildAllPageOptionsHtml($slug)
                . '</select>'
                . '<input class="dev-input dev-input--sm" type="text" name="nav_item_' . $safeId . '_href" form="nav-form" value="'
                . htmlspecialchars($href, ENT_QUOTES) . '" placeholder="https://... ou /page" aria-label="URL cible"'
                . ($type === 'page' ? ' hidden' : '') . ' data-nav-row-href />'
                . '</div>'
                . '<label class="dev-switch dev-switch--sm" title="Visible dans le menu">'
                . '<input type="hidden" name="nav_item_' . $safeId . '_visible" form="nav-form" value="0" />'
                . '<input type="checkbox" name="nav_item_' . $safeId . '_visible" form="nav-form" value="1"'
                . ($visible ? ' checked' : '') . ' />'
                . '<span class="dev-switch__track" aria-hidden="true"></span>'
                . '<span class="visually-hidden">Visible</span></label>'
                . '<div class="dev-nav-row__danger">'
                // Bouton autonome (pas de <form> imbriqué : cette ligne vit déjà
                // à l'intérieur du formulaire de navigation "nav-form").
                . '<button type="button" class="dev-icon-btn dev-icon-btn--danger" title="Supprimer" aria-label="Supprimer ce lien"'
                . ' data-dev-ajax-action="/dev/site/nav/' . rawurlencode($id) . '/delete" data-dev-ajax-mode="nav"'
                . ' data-confirm="Supprimer ce lien de navigation ?">'
                . '<i class="fa-solid fa-trash" aria-hidden="true"></i></button>'
                . '</div></div>';
        }

        return '<div class="dev-nav-editor" data-dev-sortable data-dev-sortable-url="/dev/site/nav/reorder">' . implode('', $rows) . '</div>';
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
}

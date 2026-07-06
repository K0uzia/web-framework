<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\ChromeButtonRenderer;
use Capsule\ChromeVariants;
use Capsule\DevDashboard;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\FormData;
use Capsule\PageRepository;
use Capsule\SiteRepository;

/**
 * Éditeur des partials du chrome public : variantes d'en-tête et de pied de page.
 */
final class ChromeController
{
    use DevHx;

    private const HEADER_ZONES = ['left' => 'Gauche', 'center' => 'Centre', 'right' => 'Droite'];
    private const FOOTER_ZONES = ['left' => 'Gauche', 'right' => 'Droite'];

    public function __construct(
        private readonly DevDashboard $ui,
        private readonly SiteRepository $site,
        private readonly PageRepository $pages,
    ) {
    }

    public function index(Request $request): Response
    {
        $site = $this->materializeSite();
        $partials = is_array($site['partials'] ?? null) ? $site['partials'] : [];

        $headerVariants = ChromeVariants::headerVariants($site);
        $footerVariants = ChromeVariants::footerVariants($site);
        $activeHeaderId = ChromeVariants::activeHeaderId($site);
        $activeFooterId = ChromeVariants::activeFooterId($site);

        $tab = ($request->query['tab'] ?? '') === 'footer' ? 'footer' : 'header';
        $headerVisible = ($partials['header'] ?? true) !== false;
        $footerVisible = ($partials['footer'] ?? true) !== false;

        return $this->ui->render('chrome-index.html', [
            'title' => 'En-tête & pied de page',
            'crumb_html' => Breadcrumb::render([['label' => 'En-tête & pied de page']]),
            'flash' => $this->ui->flashFromRequest($request),
            'tab_header_active' => $tab === 'header' ? 'is-active' : '',
            'tab_header_selected' => $tab === 'header' ? 'true' : 'false',
            'tab_footer_active' => $tab === 'footer' ? 'is-active' : '',
            'tab_footer_selected' => $tab === 'footer' ? 'true' : 'false',
            'header_count' => (string) count($headerVariants),
            'footer_count' => (string) count($footerVariants),
            'header_rows_html' => $this->variantRowsHtml('header', $headerVariants, $activeHeaderId, $headerVisible),
            'footer_rows_html' => $this->variantRowsHtml('footer', $footerVariants, $activeFooterId, $footerVisible),
            'header_active_id' => htmlspecialchars($activeHeaderId, ENT_QUOTES),
            'footer_active_id' => htmlspecialchars($activeFooterId, ENT_QUOTES),
        ]);
    }

    public function edit(Request $request, string $type, string $id): Response
    {
        $type = $this->validType($type);
        $site = $this->materializeSite();
        $partials = is_array($site['partials'] ?? null) ? $site['partials'] : [];

        $variants = $type === 'header' ? ChromeVariants::headerVariants($site) : ChromeVariants::footerVariants($site);
        $variant = ChromeVariants::find($variants, rawurldecode($id));
        if ($variant === null) {
            return $this->redirectToList($type, 'Variante introuvable');
        }

        $variantId = (string) $variant['id'];
        $activeId = $type === 'header' ? ChromeVariants::activeHeaderId($site) : ChromeVariants::activeFooterId($site);
        $partialVisible = ($partials[$type] ?? true) !== false;

        // L'aperçu n'affiche que le partial édité (chrome_only) ; l'autre
        // partial reste sur sa variante active.
        $headerVariantId = $type === 'header' ? $variantId : ChromeVariants::activeHeaderId($site);
        $footerVariantId = $type === 'footer' ? $variantId : ChromeVariants::activeFooterId($site);
        $previewUrl = '/dev/preview/_?header_variant=' . rawurlencode($headerVariantId)
            . '&footer_variant=' . rawurlencode($footerVariantId)
            . '&chrome_only=' . $type;

        $labelType = $type === 'header' ? "Variante d'en-tête" : 'Variante de pied de page';

        $vars = [
            'title' => $labelType . ' : ' . (string) $variant['name'],
            'crumb_html' => Breadcrumb::render([
                ['label' => 'En-tête & pied de page', 'href' => '/dev/chrome?tab=' . $type],
                ['label' => (string) $variant['name']],
            ]),
            'flash' => $this->ui->flashFromRequest($request),
            'preview_url' => $previewUrl,
            'back_url' => '/dev/chrome?tab=' . $type,
            'type_label' => $labelType,
            'variant_id' => htmlspecialchars($variantId, ENT_QUOTES),
            'variant_id_url' => rawurlencode($variantId),
            'variant_name' => htmlspecialchars((string) $variant['name'], ENT_QUOTES),
            'is_active_badge' => $this->statusBadge($variantId === $activeId, $partialVisible),
            'brand_show_logo_checked' => ($variant['brand']['show_logo'] ?? true) ? 'checked' : '',
            'brand_show_name_checked' => ($variant['brand']['show_name'] ?? true) ? 'checked' : '',
            'brand_show_tagline_checked' => ($variant['brand']['show_tagline'] ?? false) ? 'checked' : '',
            'nav_visible_checked' => ($variant['nav']['visible'] ?? true) ? 'checked' : '',
            'login_enabled_checked' => ($variant['login']['enabled'] ?? false) ? 'checked' : '',
            'login_label' => htmlspecialchars((string) $variant['login']['label'], ENT_QUOTES),
            'login_style_options' => ChromeButtonRenderer::optionsHtml((string) ($variant['login']['style'] ?? 'outline')),
            'login_target_html' => LinkPicker::render($type . '_login_href', 'login_href', (string) $variant['login']['href'], $this->pages, 'chrome-variant-form'),
            'zone_brand_options' => $this->zoneOptions((string) $variant['layout']['brand'], $type === 'header' ? self::HEADER_ZONES : self::FOOTER_ZONES),
            'zone_nav_options' => $this->zoneOptions((string) $variant['layout']['nav'], $type === 'header' ? self::HEADER_ZONES : self::FOOTER_ZONES),
            'zone_login_options' => $this->zoneOptions((string) $variant['layout']['login'], $type === 'header' ? self::HEADER_ZONES : self::FOOTER_ZONES),
            'delete_form_html' => $this->deleteFormHtml($type, $variant, count($variants)),
        ];

        if ($type === 'header') {
            $vars['cta_enabled_checked'] = ($variant['cta']['enabled'] ?? false) ? 'checked' : '';
            $vars['cta_label'] = htmlspecialchars((string) $variant['cta']['label'], ENT_QUOTES);
            $vars['cta_style_options'] = ChromeButtonRenderer::optionsHtml((string) ($variant['cta']['style'] ?? 'primary'));
            $vars['cta_target_html'] = LinkPicker::render('header_cta_href', 'cta_href', (string) $variant['cta']['href'], $this->pages, 'chrome-variant-form');
            $vars['zone_cta_options'] = $this->zoneOptions((string) $variant['layout']['cta'], self::HEADER_ZONES);
        } else {
            $vars['brand_visible_checked'] = ($variant['brand']['visible'] ?? true) ? 'checked' : '';
            $vars['footer_text'] = htmlspecialchars((string) ($site['footer_text'] ?? ''), ENT_QUOTES);
        }

        return $this->ui->render('chrome-edit-' . $type . '.html', $vars);
    }

    public function update(Request $request, string $type): Response
    {
        $type = $this->validType($type);
        $data = FormData::fromRequest($request);
        $site = $this->materializeSite();

        $variants = $type === 'header' ? ChromeVariants::headerVariants($site) : ChromeVariants::footerVariants($site);
        $id = trim($data['variant_id'] ?? '');
        $index = $this->indexOf($variants, $id);
        if ($index < 0) {
            return $this->redirectToList($type, 'Variante introuvable');
        }

        $variants[$index] = $type === 'header'
            ? $this->applyHeaderForm($variants[$index], $data)
            : $this->applyFooterForm($variants[$index], $data);

        $site = $this->persistVariants($site, $type, $variants);

        // Réglage global porté par le même formulaire.
        if ($type === 'footer' && isset($data['footer_text'])) {
            $site['footer_text'] = trim($data['footer_text']);
        }

        $this->site->setSite($site);

        $label = $type === 'header' ? 'En-tête enregistré' : 'Pied de page enregistré';
        if ($this->isHx($request)) {
            return $this->ui->partial('saved.html', ['message' => $label]);
        }

        return $this->redirectToEdit($type, $id, $label);
    }

    public function create(Request $request, string $type): Response
    {
        $type = $this->validType($type);
        $data = FormData::fromRequest($request);
        $site = $this->materializeSite();
        $variants = $type === 'header' ? ChromeVariants::headerVariants($site) : ChromeVariants::footerVariants($site);
        $base = ChromeVariants::find($variants, trim($data['from'] ?? ''))
            ?? ChromeVariants::find($variants, $type === 'header' ? ChromeVariants::activeHeaderId($site) : ChromeVariants::activeFooterId($site))
            ?? $variants[0];

        $name = trim($data['variant_name'] ?? '');
        $new = $base;
        $new['id'] = ChromeVariants::newId();
        $new['name'] = $name !== '' ? $name : 'Nouvelle variante';
        $variants[] = $new;

        $site = $this->persistVariants($site, $type, $variants);
        $this->site->setSite($site);

        return $this->redirectToEdit($type, (string) $new['id'], 'Variante créée');
    }

    public function duplicate(Request $request, string $type, string $id): Response
    {
        $type = $this->validType($type);
        $site = $this->materializeSite();
        $variants = $type === 'header' ? ChromeVariants::headerVariants($site) : ChromeVariants::footerVariants($site);
        $base = ChromeVariants::find($variants, $id);
        if ($base === null) {
            return $this->redirectToList($type, 'Variante introuvable');
        }

        $new = $base;
        $new['id'] = ChromeVariants::newId();
        $new['name'] = $base['name'] . ' (copie)';
        $variants[] = $new;

        $site = $this->persistVariants($site, $type, $variants);
        $this->site->setSite($site);

        return $this->redirectToEdit($type, (string) $new['id'], 'Variante dupliquée');
    }

    /**
     * Active une variante sur le site (une seule active à la fois).
     * Avec active=0 sur la variante active, le partial est masqué du site.
     */
    public function activate(Request $request, string $type, string $id): Response
    {
        $type = $this->validType($type);
        $data = FormData::fromRequest($request);
        $site = $this->materializeSite();
        $variants = $type === 'header' ? ChromeVariants::headerVariants($site) : ChromeVariants::footerVariants($site);
        if (ChromeVariants::find($variants, $id) === null) {
            return $this->redirectToList($type, 'Variante introuvable');
        }

        $enable = ($data['active'] ?? '1') === '1';
        $site = $this->persistVariants($site, $type, $variants);
        $partials = is_array($site['partials'] ?? null) ? $site['partials'] : [];
        $labelType = $type === 'header' ? 'En-tête' : 'Pied de page';

        if ($enable) {
            $site['active_' . $type . '_variant'] = $id;
            $partials[$type] = true;
            $message = 'Variante activée sur le site';
        } else {
            $partials[$type] = false;
            $message = $labelType . ' masqué sur le site';
        }

        $site['partials'] = array_merge(['header' => true, 'footer' => true], $partials);
        $this->site->setSite($site);

        return $this->redirectToList($type, $message);
    }

    public function delete(Request $request, string $type, string $id): Response
    {
        $type = $this->validType($type);
        $site = $this->materializeSite();
        $variants = $type === 'header' ? ChromeVariants::headerVariants($site) : ChromeVariants::footerVariants($site);
        if (count($variants) <= 1) {
            return $this->redirectToList($type, 'Impossible de supprimer la dernière variante');
        }

        $remaining = array_values(array_filter(
            $variants,
            static fn (array $variant): bool => ($variant['id'] ?? '') !== $id,
        ));
        if (count($remaining) === count($variants)) {
            return $this->redirectToList($type, 'Variante introuvable');
        }

        $site = $this->persistVariants($site, $type, $remaining);
        $activeKey = 'active_' . $type . '_variant';
        if (($site[$activeKey] ?? '') === $id) {
            $site[$activeKey] = (string) ($remaining[0]['id'] ?? 'default');
        }
        $this->site->setSite($site);

        return $this->redirectToList($type, 'Variante supprimée');
    }

    /**
     * @param array<string, mixed>  $variant
     * @param array<string, string> $data
     *
     * @return array<string, mixed>
     */
    private function applyHeaderForm(array $variant, array $data): array
    {
        return ChromeVariants::normalizeHeader([
            'id' => $variant['id'],
            'name' => trim($data['variant_name'] ?? (string) $variant['name']),
            'brand' => [
                'show_logo' => ($data['brand_show_logo'] ?? '0') === '1',
                'show_name' => ($data['brand_show_name'] ?? '0') === '1',
                'show_tagline' => ($data['brand_show_tagline'] ?? '0') === '1',
            ],
            'nav' => ['visible' => ($data['nav_visible'] ?? '0') === '1'],
            'cta' => [
                'enabled' => ($data['cta_enabled'] ?? '0') === '1',
                'label' => trim($data['cta_label'] ?? ''),
                'href' => trim($data['cta_href'] ?? ''),
                'style' => $data['cta_style'] ?? 'primary',
            ],
            'login' => [
                'enabled' => ($data['login_enabled'] ?? '0') === '1',
                'label' => trim($data['login_label'] ?? ''),
                'href' => trim($data['login_href'] ?? ''),
                'style' => $data['login_style'] ?? 'outline',
            ],
            'layout' => [
                'brand' => $data['zone_brand'] ?? '',
                'nav' => $data['zone_nav'] ?? '',
                'cta' => $data['zone_cta'] ?? '',
                'login' => $data['zone_login'] ?? '',
            ],
        ]);
    }

    /**
     * @param array<string, mixed>  $variant
     * @param array<string, string> $data
     *
     * @return array<string, mixed>
     */
    private function applyFooterForm(array $variant, array $data): array
    {
        return ChromeVariants::normalizeFooter([
            'id' => $variant['id'],
            'name' => trim($data['variant_name'] ?? (string) $variant['name']),
            'brand' => [
                'visible' => ($data['brand_visible'] ?? '0') === '1',
                'show_logo' => ($data['brand_show_logo'] ?? '0') === '1',
                'show_name' => ($data['brand_show_name'] ?? '0') === '1',
                'show_tagline' => ($data['brand_show_tagline'] ?? '0') === '1',
            ],
            'nav' => ['visible' => ($data['nav_visible'] ?? '0') === '1'],
            'login' => [
                'enabled' => ($data['login_enabled'] ?? '0') === '1',
                'label' => trim($data['login_label'] ?? ''),
                'href' => trim($data['login_href'] ?? ''),
                'style' => $data['login_style'] ?? 'outline',
            ],
            'layout' => [
                'brand' => $data['zone_brand'] ?? '',
                'nav' => $data['zone_nav'] ?? '',
                'login' => $data['zone_login'] ?? '',
            ],
        ]);
    }

    /**
     * Matérialise la liste de variantes et garantit un id actif valide.
     *
     * @param array<string, mixed>       $site
     * @param list<array<string, mixed>> $variants
     *
     * @return array<string, mixed>
     */
    private function persistVariants(array $site, string $type, array $variants): array
    {
        $site[$type . '_variants'] = $variants;
        $activeKey = 'active_' . $type . '_variant';
        $activeId = (string) ($site[$activeKey] ?? '');
        if (ChromeVariants::find($variants, $activeId) === null) {
            $site[$activeKey] = (string) ($variants[0]['id'] ?? 'default');
        }

        return $site;
    }

    private function statusBadge(bool $isActive, bool $partialVisible): string
    {
        if ($isActive && $partialVisible) {
            return '<span class="dev-badge dev-badge--success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Active sur le site</span>';
        }
        if ($isActive) {
            return '<span class="dev-badge dev-badge--muted"><i class="fa-solid fa-eye-slash" aria-hidden="true"></i> Masquée sur le site</span>';
        }

        return '<span class="dev-badge dev-badge--muted"><i class="fa-solid fa-pen" aria-hidden="true"></i> Brouillon</span>';
    }

    /**
     * @param list<array<string, mixed>> $variants
     */
    private function variantRowsHtml(string $type, array $variants, string $activeId, bool $partialVisible): string
    {
        $labelType = $type === 'header' ? "d'en-tête" : 'de pied de page';
        $count = count($variants);

        $rows = [];
        foreach ($variants as $variant) {
            $id = (string) ($variant['id'] ?? '');
            $encodedId = rawurlencode($id);
            $name = htmlspecialchars((string) ($variant['name'] ?? ''), ENT_QUOTES);
            $isActive = $id === $activeId;
            $isOn = $isActive && $partialVisible;

            $editUrlAttr = htmlspecialchars('/dev/chrome/' . $type . '/' . $encodedId, ENT_QUOTES);
            $redirectUrl = htmlspecialchars('/dev/chrome?tab=' . $type, ENT_QUOTES);

            $toggleToast = $isOn
                ? ($type === 'header' ? 'En-tête masqué sur le site' : 'Pied de page masqué sur le site')
                : 'Variante activée sur le site';

            $toggleHtml = '<form class="dev-inline-form" method="post" action="/dev/chrome/' . $type . '/' . $encodedId . '/activate"'
                . ' data-dev-ajax="post-redirect" data-dev-redirect="' . $redirectUrl . '" data-dev-toast-form="' . $toggleToast . '">'
                . '<label class="dev-switch">'
                . '<input type="hidden" name="active" value="0" />'
                . '<input type="checkbox" name="active" value="1"' . ($isOn ? ' checked' : '') . ' data-dev-autosubmit />'
                . '<span class="dev-switch__track" aria-hidden="true"></span>'
                . '<span class="visually-hidden">Activer la variante « ' . $name . ' » sur le site</span>'
                . '</label></form>';

            $deleteHtml = '';
            if ($count > 1) {
                $deleteHtml = '<form method="post" action="/dev/chrome/' . $type . '/' . $encodedId . '/delete" role="none"'
                    . ' data-dev-confirm="Supprimer la variante ' . $labelType . ' « ' . $name . ' » ? Cette action est définitive.">'
                    . '<button type="submit" role="menuitem" class="dev-menu__danger"><i class="fa-solid fa-trash" aria-hidden="true"></i> Supprimer</button></form>';
            }

            $rows[] = '<tr>'
                . '<td><a class="dev-table__link" href="' . $editUrlAttr . '">'
                . '<span class="dev-table__title">' . $name . '</span>'
                . '</a></td>'
                . '<td>' . $this->statusBadge($isActive, $partialVisible) . '</td>'
                . '<td>' . $toggleHtml . '</td>'
                . '<td class="dev-table__actions"><div class="dev-table__actions-inner">'
                . '<a class="dev-icon-btn" href="' . $editUrlAttr . '" title="Éditer" aria-label="Éditer la variante ' . $name . '"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>'
                . '<details class="dev-menu">'
                . '<summary class="dev-icon-btn" aria-label="Plus d\'actions"><i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i></summary>'
                . '<div class="dev-menu__panel" role="menu">'
                . '<form method="post" action="/dev/chrome/' . $type . '/' . $encodedId . '/duplicate" role="none">'
                . '<button type="submit" role="menuitem"><i class="fa-solid fa-copy" aria-hidden="true"></i> Dupliquer</button></form>'
                . $deleteHtml
                . '</div></details>'
                . '</div></td></tr>';
        }

        return implode('', $rows);
    }

    /**
     * Formulaire de suppression affiché dans le menu d'actions de l'éditeur.
     *
     * @param array<string, mixed> $variant
     */
    private function deleteFormHtml(string $type, array $variant, int $variantCount): string
    {
        if ($variantCount <= 1) {
            return '';
        }

        $encodedId = rawurlencode((string) ($variant['id'] ?? ''));
        $name = htmlspecialchars((string) ($variant['name'] ?? ''), ENT_QUOTES);
        $labelType = $type === 'header' ? "d'en-tête" : 'de pied de page';

        return '<form method="post" action="/dev/chrome/' . $type . '/' . $encodedId . '/delete" role="none"'
            . ' data-dev-ajax="post-redirect" data-dev-redirect="/dev/chrome?tab=' . $type . '" data-dev-toast-form="Variante supprimée"'
            . ' data-dev-confirm="Supprimer la variante ' . $labelType . ' « ' . $name . ' » ? Cette action est définitive.">'
            . '<button type="submit" role="menuitem" class="dev-menu__danger"><i class="fa-solid fa-trash" aria-hidden="true"></i> Supprimer</button></form>';
    }

    /**
     * @param array<string, string> $zones
     */
    private function zoneOptions(string $current, array $zones): string
    {
        $options = [];
        foreach ($zones as $value => $label) {
            $selected = $value === $current ? ' selected' : '';
            $options[] = '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
        }

        return implode('', $options);
    }

    /**
     * @param list<array<string, mixed>> $variants
     */
    private function indexOf(array $variants, string $id): int
    {
        foreach ($variants as $i => $variant) {
            if (($variant['id'] ?? '') === $id) {
                return $i;
            }
        }

        return -1;
    }

    private function validType(string $type): string
    {
        return $type === 'footer' ? 'footer' : 'header';
    }

    private function redirectToList(string $type, string $message): Response
    {
        $response = $this->ui->redirect('/dev/chrome?tab=' . $type);

        return $message !== '' ? $this->ui->withFlash($response, $message) : $response;
    }

    private function redirectToEdit(string $type, string $id, string $message): Response
    {
        $response = $this->ui->redirect('/dev/chrome/' . $type . '/' . rawurlencode($id));

        return $message !== '' ? $this->ui->withFlash($response, $message) : $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function materializeSite(): array
    {
        $stored = $this->site->getSite();
        $needsWrite = !is_array($stored['footer_variants'] ?? null)
            || $stored['footer_variants'] === []
            || !isset($stored['active_footer_variant'])
            || !is_array($stored['header_variants'] ?? null)
            || $stored['header_variants'] === []
            || !isset($stored['active_header_variant']);
        $site = ChromeVariants::materialize($stored);
        if ($needsWrite) {
            $this->site->setSite($site);
        }

        return $site;
    }
}

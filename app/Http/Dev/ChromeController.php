<?php

declare(strict_types=1);

namespace App\Http\Dev;

use App\Http\Dev\FooterFormRenderer;
use App\Http\Dev\FooterTemplatePickerRenderer;
use App\Http\Dev\FooterTemplates;
use App\Http\Dev\HeaderFormRenderer;
use App\Http\Dev\HeaderTemplatePickerRenderer;
use App\Http\Dev\HeaderTemplates;
use App\Http\Dev\LoginBlockPicker;
use Capsule\ChromeAppearance;
use Capsule\ChromeButtonRenderer;
use Capsule\LoginBlockLibrary;
use Capsule\ChromeVariants;
use Capsule\DevDashboard;
use Capsule\FooterStyle;
use Capsule\HeaderStyle;
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
        private readonly SiteNavFormRenderer $navForms,
        private readonly FooterFormRenderer $footerForms = new FooterFormRenderer(),
        private readonly FooterTemplatePickerRenderer $footerTemplatePicker = new FooterTemplatePickerRenderer(),
        private readonly HeaderFormRenderer $headerForms = new HeaderFormRenderer(),
        private readonly HeaderTemplatePickerRenderer $headerTemplatePicker = new HeaderTemplatePickerRenderer(),
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
            'header_template_picker_html' => $this->headerTemplatePicker->renderPickerHtml(),
            'footer_template_picker_html' => $this->footerTemplatePicker->renderPickerHtml(),
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
            'show_border_checked' => ChromeAppearance::showBorder($variant) ? 'checked' : '',
            'footer_bg_options' => $this->chromeBgOptions(ChromeAppearance::footerBgToken($variant), 'footer'),
            'header_bg_options' => $this->chromeBgOptions(ChromeAppearance::headerBgToken($variant), 'header'),
            'login_display_options' => LoginBlockPicker::displayOptions((string) ($variant['login']['display'] ?? 'page')),
            'login_block_picker_html' => LoginBlockPicker::render(
                'chrome_login_block_ref',
                'login_block_ref',
                (string) ($variant['login']['block_ref'] ?? ''),
                $site,
                'chrome-variant-form',
            ),
            'login_target_html' => LinkPicker::render($type . '_login_href', 'login_href', (string) $variant['login']['href'], $this->pages, 'chrome-variant-form'),
            'zone_brand_options' => $this->zoneOptions((string) $variant['layout']['brand'], $type === 'header' ? self::HEADER_ZONES : self::FOOTER_ZONES),
            'zone_nav_options' => $this->zoneOptions((string) $variant['layout']['nav'], $type === 'header' ? self::HEADER_ZONES : self::FOOTER_ZONES),
            'zone_login_options' => $this->zoneOptions((string) $variant['layout']['login'], $type === 'header' ? self::HEADER_ZONES : self::FOOTER_ZONES),
            'delete_form_html' => $this->deleteFormHtml($type, $variant, count($variants)),
        ];
        $vars = array_merge($vars, $this->chromeContentPanels($site));

        if ($type === 'header') {
            $template = HeaderStyle::normalizeTemplate((string) ($variant['template'] ?? HeaderStyle::TEMPLATE_DEFAULT));
            if (HeaderStyle::isBlocksTemplate($template)) {
                $vars['header_template'] = htmlspecialchars($template, ENT_QUOTES);
                $vars['template_label'] = htmlspecialchars($template === 'navbar5' ? 'Navbar 5' : 'Navbar 1', ENT_QUOTES);
                $vars['header_blocks_form_html'] = $this->headerForms->render($variant, $site);

                return $this->ui->render('chrome-edit-header-blocks.html', $vars);
            }

            $vars['cta_enabled_checked'] = ($variant['cta']['enabled'] ?? false) ? 'checked' : '';
            $vars['cta_label'] = htmlspecialchars((string) $variant['cta']['label'], ENT_QUOTES);
            $vars['cta_style_options'] = ChromeButtonRenderer::optionsHtml((string) ($variant['cta']['style'] ?? 'primary'));
            $vars['cta_target_html'] = LinkPicker::render('header_cta_href', 'cta_href', (string) $variant['cta']['href'], $this->pages, 'chrome-variant-form');
            $vars['zone_cta_options'] = $this->zoneOptions((string) $variant['layout']['cta'], self::HEADER_ZONES);
        } else {
            $vars['brand_visible_checked'] = ($variant['brand']['visible'] ?? true) ? 'checked' : '';
            $vars['footer_text'] = htmlspecialchars((string) ($site['footer_text'] ?? ''), ENT_QUOTES);
            $vars['footer_legal_links_html'] = $this->footerForms->renderDefaultLegalLinks($variant);
            $template = FooterStyle::normalizeTemplate((string) ($variant['template'] ?? FooterStyle::TEMPLATE_DEFAULT));
            if (FooterStyle::isBlocksTemplate($template)) {
                $vars['footer_template'] = htmlspecialchars($template, ENT_QUOTES);
                $vars['template_label'] = htmlspecialchars($template === 'footer7' ? 'Footer 7' : 'Footer 2', ENT_QUOTES);
                $vars['footer_blocks_form_html'] = $this->footerForms->render($variant);

                return $this->ui->render('chrome-edit-footer-blocks.html', $vars);
            }
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
        if ($type === 'header') {
            $template = HeaderStyle::normalizeTemplate(trim($data['header_template'] ?? HeaderStyle::TEMPLATE_DEFAULT));
            if (HeaderStyle::isBlocksTemplate($template)) {
                $new = HeaderTemplates::create($template, $name !== '' ? $name : 'Nouvelle variante');
            } else {
                $new = $base;
                $new['id'] = ChromeVariants::newId();
                $new['name'] = $name !== '' ? $name : 'Nouvelle variante';
                $new['template'] = HeaderStyle::TEMPLATE_DEFAULT;
            }
        } elseif ($type === 'footer') {
            $template = FooterStyle::normalizeTemplate(trim($data['footer_template'] ?? FooterStyle::TEMPLATE_DEFAULT));
            if (FooterStyle::isBlocksTemplate($template)) {
                $new = FooterTemplates::create($template, $name !== '' ? $name : 'Nouvelle variante');
            } else {
                $new = $base;
                $new['id'] = ChromeVariants::newId();
                $new['name'] = $name !== '' ? $name : 'Nouvelle variante';
                $new['template'] = FooterStyle::TEMPLATE_DEFAULT;
            }
        } else {
            $new = $base;
            $new['id'] = ChromeVariants::newId();
            $new['name'] = $name !== '' ? $name : 'Nouvelle variante';
        }
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
        $template = HeaderStyle::normalizeTemplate((string) ($variant['template'] ?? HeaderStyle::TEMPLATE_DEFAULT));
        $isBlocks = HeaderStyle::isBlocksTemplate($template);
        $brand = is_array($variant['brand'] ?? null) ? $variant['brand'] : [];
        $nav = is_array($variant['nav'] ?? null) ? $variant['nav'] : [];
        $layout = is_array($variant['layout'] ?? null) ? $variant['layout'] : [];
        $existingAppearance = is_array($variant['appearance'] ?? null) ? $variant['appearance'] : [];

        $payload = [
            'id' => $variant['id'],
            'name' => trim($data['variant_name'] ?? (string) $variant['name']),
            'template' => $template,
            'menu_items' => $variant['menu_items'] ?? [],
            'features' => $variant['features'] ?? [],
            'nav_links' => $variant['nav_links'] ?? [],
            'mobile_links' => $variant['mobile_links'] ?? [],
            'features_label' => $variant['features_label'] ?? '',
            'brand' => [
                'show_logo' => $this->boolFromForm($data, 'brand_show_logo', ($brand['show_logo'] ?? true) === true),
                'show_name' => $this->boolFromForm($data, 'brand_show_name', ($brand['show_name'] ?? true) === true),
                'show_tagline' => $this->boolFromForm($data, 'brand_show_tagline', ($brand['show_tagline'] ?? false) === true),
            ],
            'nav' => ['visible' => $this->boolFromForm($data, 'nav_visible', ($nav['visible'] ?? true) === true)],
            'cta' => $this->parseChromeButton($data, 'cta', '', 'primary'),
            'login' => $this->parseChromeButton($data, 'login', '/login', 'outline', true),
            'appearance' => [
                'show_border' => $this->boolFromForm($data, 'show_border', ChromeAppearance::showBorder($variant)),
                'bg' => ChromeAppearance::normalizeHeaderBg(
                    array_key_exists('appearance_bg', $data)
                        ? (string) $data['appearance_bg']
                        : (string) ($existingAppearance['bg'] ?? 'theme'),
                ),
            ],
            'layout' => [
                'brand' => $data['zone_brand'] ?? (string) ($layout['brand'] ?? ''),
                'nav' => $data['zone_nav'] ?? (string) ($layout['nav'] ?? ''),
                'cta' => $data['zone_cta'] ?? (string) ($layout['cta'] ?? ''),
                'login' => $data['zone_login'] ?? (string) ($layout['login'] ?? ''),
            ],
        ];

        if ($isBlocks) {
            $blocks = $this->headerForms->parseForm($data, $template);
            $payload['menu_items'] = $blocks['menu_items'] ?? $payload['menu_items'];
            $payload['features'] = $blocks['features'] ?? $payload['features'];
            $payload['nav_links'] = $blocks['nav_links'] ?? $payload['nav_links'];
            $payload['mobile_links'] = $blocks['mobile_links'] ?? $payload['mobile_links'];
            $payload['features_label'] = $blocks['features_label'] ?? $payload['features_label'];
            $payload['login'] = $blocks['login'] ?? $payload['login'];
            $payload['cta'] = $blocks['cta'] ?? $payload['cta'];
        }

        return ChromeVariants::normalizeHeader($payload);
    }

    /**
     * @param array<string, string> $data
     */
    private function boolFromForm(array $data, string $key, bool $default): bool
    {
        if (!array_key_exists($key, $data)) {
            return $default;
        }

        return ($data[$key] ?? '0') === '1';
    }

    /**
     * @param array<string, string> $data
     *
     * @return array{enabled: bool, label: string, href: string, style: string, display?: string, block_ref?: string}
     */
    private function parseChromeButton(array $data, string $prefix, string $defaultHref, string $defaultStyle, bool $withLoginOptions = false): array
    {
        $button = [
            'enabled' => ($data[$prefix . '_enabled'] ?? '0') === '1',
            'label' => trim($data[$prefix . '_label'] ?? ''),
            'href' => trim($data[$prefix . '_href'] ?? ''),
            'style' => trim($data[$prefix . '_style'] ?? '') !== '' ? trim($data[$prefix . '_style'] ?? '') : $defaultStyle,
        ];
        if ($withLoginOptions) {
            $display = trim($data['login_display'] ?? 'page');
            $button['display'] = in_array($display, ['page', 'modal'], true) ? $display : 'page';
            $button['block_ref'] = trim($data['login_block_ref'] ?? '');
        }

        return $button;
    }

    /**
     * @param array<string, mixed>  $variant
     * @param array<string, string> $data
     *
     * @return array<string, mixed>
     */
    private function applyFooterForm(array $variant, array $data): array
    {
        $template = FooterStyle::normalizeTemplate((string) ($variant['template'] ?? FooterStyle::TEMPLATE_DEFAULT));
        $existingBrand = is_array($variant['brand'] ?? null) ? $variant['brand'] : [];
        $existingNav = is_array($variant['nav'] ?? null) ? $variant['nav'] : [];
        $existingLogin = is_array($variant['login'] ?? null) ? $variant['login'] : [];
        $existingLayout = is_array($variant['layout'] ?? null) ? $variant['layout'] : [];
        $existingAppearance = is_array($variant['appearance'] ?? null) ? $variant['appearance'] : [];
        $payload = [
            'id' => $variant['id'],
            'name' => trim($data['variant_name'] ?? (string) $variant['name']),
            'template' => $template,
            'description' => $variant['description'] ?? '',
            'sections' => $variant['sections'] ?? [],
            'legal_links' => $variant['legal_links'] ?? [],
            'social_links' => $variant['social_links'] ?? [],
            'brand' => [
                'visible' => $this->boolFromForm($data, 'brand_visible', ($existingBrand['visible'] ?? true) !== false),
                'show_logo' => $this->boolFromForm($data, 'brand_show_logo', ($existingBrand['show_logo'] ?? true) !== false),
                'show_name' => $this->boolFromForm($data, 'brand_show_name', ($existingBrand['show_name'] ?? true) !== false),
                'show_tagline' => $this->boolFromForm($data, 'brand_show_tagline', ($existingBrand['show_tagline'] ?? true) !== false),
            ],
            'nav' => ['visible' => $this->boolFromForm($data, 'nav_visible', ($existingNav['visible'] ?? true) !== false)],
            'login' => [
                'enabled' => $this->boolFromForm($data, 'login_enabled', ($existingLogin['enabled'] ?? false) === true),
                'label' => array_key_exists('login_label', $data)
                    ? trim($data['login_label'])
                    : trim((string) ($existingLogin['label'] ?? '')),
                'href' => array_key_exists('login_href', $data)
                    ? trim($data['login_href'])
                    : trim((string) ($existingLogin['href'] ?? '')),
                'style' => array_key_exists('login_style', $data)
                    ? trim($data['login_style'])
                    : trim((string) ($existingLogin['style'] ?? 'outline')),
            ],
            'appearance' => [
                'show_border' => $this->boolFromForm($data, 'show_border', ChromeAppearance::showBorder($variant)),
                'bg' => ChromeAppearance::normalizeFooterBg(
                    array_key_exists('appearance_bg', $data)
                        ? (string) $data['appearance_bg']
                        : (string) ($existingAppearance['bg'] ?? 'theme'),
                ),
            ],
            'layout' => [
                'brand' => array_key_exists('zone_brand', $data)
                    ? (string) $data['zone_brand']
                    : (string) ($existingLayout['brand'] ?? ''),
                'nav' => array_key_exists('zone_nav', $data)
                    ? (string) $data['zone_nav']
                    : (string) ($existingLayout['nav'] ?? ''),
                'login' => array_key_exists('zone_login', $data)
                    ? (string) $data['zone_login']
                    : (string) ($existingLayout['login'] ?? ''),
            ],
        ];

        if (FooterStyle::isBlocksTemplate($template)) {
            $blocks = $this->footerForms->parseForm($data, $template);
            $payload['description'] = $blocks['description'];
            $payload['sections'] = $blocks['sections'];
            $payload['legal_links'] = $blocks['legal_links'];
            $payload['social_links'] = $blocks['social_links'];
        } elseif (array_key_exists('legal_0_label', $data) || array_key_exists('legal_0_href', $data)
            || array_key_exists('legal_1_label', $data) || array_key_exists('legal_1_href', $data)) {
            $payload['legal_links'] = $this->footerForms->parseDefaultLegalLinks($data);
        }

        return ChromeVariants::normalizeFooter($payload);
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

    private function chromeBgOptions(string $current, string $zone): string
    {
        $themeLabel = $zone === 'header' ? 'Thème (couleur En-tête)' : 'Thème (couleur Pied de page)';
        $choices = [
            'theme' => $themeLabel,
            'background' => 'Fond de page',
            'surface' => 'Surface',
            'muted' => 'Surface atténuée',
            'primary' => 'Primaire',
        ];
        $options = [];
        foreach ($choices as $value => $label) {
            $selected = $value === $current ? ' selected' : '';
            $options[] = '<option value="' . htmlspecialchars($value, ENT_QUOTES) . '"' . $selected . '>'
                . htmlspecialchars($label, ENT_QUOTES) . '</option>';
        }

        return implode('', $options);
    }

    /**
     * @param array<string, mixed> $site
     *
     * @return array<string, string>
     */
    private function chromeContentPanels(array $site): array
    {
        return [
            'chrome_identity_panel_html' => $this->ui->partialHtml('chrome-identity-panel.html', [
                'site_name' => htmlspecialchars((string) ($site['name'] ?? ''), ENT_QUOTES),
                'site_tagline' => htmlspecialchars((string) ($site['tagline'] ?? ''), ENT_QUOTES),
            ]),
            'chrome_nav_section_html' => $this->ui->partialHtml('chrome-nav-section.html', [
                'nav_mode_label' => htmlspecialchars($this->navForms->navModeLabel($site), ENT_QUOTES),
                'home_label' => htmlspecialchars((string) ($site['home_label'] ?? 'Accueil'), ENT_QUOTES),
                'nav_panel_html' => $this->ui->partialHtml('nav-panel.html', [
                    'nav_rows_html' => $this->navForms->rowsHtml($site),
                    'nav_delete_forms_html' => $this->navForms->deleteFormsHtml($site),
                    'message' => '',
                ]),
                'nav_add_target_html' => LinkPicker::render('nav_target', 'nav_target', '', $this->pages),
            ]),
        ];
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
            || !isset($stored['active_header_variant'])
            || !is_array($stored['login_blocks'] ?? null)
            || $stored['login_blocks'] === [];
        $site = ChromeVariants::materialize($stored);
        $site = LoginBlockLibrary::materialize($site);
        if ($needsWrite) {
            $this->site->setSite($site);
        }

        return $site;
    }
}

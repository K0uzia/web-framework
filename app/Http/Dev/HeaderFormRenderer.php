<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\ChromeButtonRenderer;
use Capsule\HeaderStyle;

/**
 * Formulaire d'édition des en-têtes shadcnblocks (navbar1, navbar5).
 */
final class HeaderFormRenderer
{
    /**
     * @param array<string, mixed> $variant
     */
    public function render(array $variant, array $site): string
    {
        $template = HeaderStyle::normalizeTemplate((string) ($variant['template'] ?? HeaderStyle::TEMPLATE_DEFAULT));
        $login = is_array($variant['login'] ?? null) ? $variant['login'] : [];

        $html = '<div class="dev-panel dev-panel--compact"><h2 class="dev-panel__title">Boutons d\'action</h2><div class="dev-form--grid dev-form--grid-2">'
            . $this->loginFields($login, $site)
            . $this->buttonFields('cta', 'Appel à l\'action', is_array($variant['cta'] ?? null) ? $variant['cta'] : [], 'primary')
            . '</div></div>';

        if ($template === 'navbar5') {
            return $html . $this->renderNavbar5Fields($variant);
        }

        return $html . $this->renderNavbar1Fields($variant);
    }

    /**
     * @param array<string, mixed> $variant
     */
    private function renderNavbar1Fields(array $variant): string
    {
        $items = is_array($variant['menu_items'] ?? null) ? $variant['menu_items'] : [];
        $html = '<div class="dev-panel dev-panel--compact"><h2 class="dev-panel__title">Menu principal</h2>';
        for ($i = 0; $i < 5; $i++) {
            $item = is_array($items[$i] ?? null) ? $items[$i] : [];
            $childrenText = '';
            $children = is_array($item['children'] ?? null) ? $item['children'] : [];
            foreach ($children as $child) {
                if (!is_array($child)) {
                    continue;
                }
                $label = trim((string) ($child['label'] ?? ''));
                if ($label === '') {
                    continue;
                }
                $description = trim((string) ($child['description'] ?? ''));
                $href = trim((string) ($child['href'] ?? ''));
                $icon = trim((string) ($child['icon'] ?? ''));
                $childrenText .= $label . ' | ' . $description . ' | ' . ($href !== '' ? $href : '#') . ' | ' . $icon . "\n";
            }
            $html .= '<div class="dev-field"><label class="dev-label" for="menu_' . $i . '_label">Entrée ' . ($i + 1) . '</label>'
                . '<div class="dev-form--grid dev-form--grid-2">'
                . '<input class="dev-input" id="menu_' . $i . '_label" type="text" name="menu_' . $i . '_label" value="'
                . htmlspecialchars((string) ($item['label'] ?? ''), ENT_QUOTES) . '" placeholder="Libellé" />'
                . '<input class="dev-input" id="menu_' . $i . '_href" type="text" name="menu_' . $i . '_href" value="'
                . htmlspecialchars((string) ($item['href'] ?? ''), ENT_QUOTES) . '" placeholder="Lien" />'
                . '</div>'
                . '<label class="dev-label" for="menu_' . $i . '_children">Sous-menu (optionnel)</label>'
                . '<textarea class="dev-input dev-textarea" id="menu_' . $i . '_children" name="menu_' . $i . '_children" rows="4" placeholder="Libellé | Description | /url | icône">'
                . htmlspecialchars(rtrim($childrenText), ENT_QUOTES) . '</textarea>'
                . '<span class="dev-hint">Une ligne par lien enfant. Icônes : book, tree, sun, zap.</span>'
                . '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $variant
     */
    private function renderNavbar5Fields(array $variant): string
    {
        $features = is_array($variant['features'] ?? null) ? $variant['features'] : [];
        $navLinks = is_array($variant['nav_links'] ?? null) ? $variant['nav_links'] : [];
        $mobileLinks = is_array($variant['mobile_links'] ?? null) ? $variant['mobile_links'] : [];

        $html = '<div class="dev-panel dev-panel--compact"><h2 class="dev-panel__title">Méga-menu</h2>'
            . '<div class="dev-field"><label class="dev-label" for="features_label">Libellé du menu déroulant</label>'
            . '<input class="dev-input" id="features_label" type="text" name="features_label" value="'
            . htmlspecialchars((string) ($variant['features_label'] ?? 'Fonctionnalités'), ENT_QUOTES) . '" /></div>';

        for ($i = 0; $i < 6; $i++) {
            $feature = is_array($features[$i] ?? null) ? $features[$i] : [];
            $html .= '<div class="dev-field"><label class="dev-label" for="feature_' . $i . '_title">Fonctionnalité ' . ($i + 1) . '</label>'
                . '<div class="dev-form--grid dev-form--grid-2">'
                . '<input class="dev-input" id="feature_' . $i . '_title" type="text" name="feature_' . $i . '_title" value="'
                . htmlspecialchars((string) ($feature['title'] ?? ''), ENT_QUOTES) . '" placeholder="Titre" />'
                . '<input class="dev-input" id="feature_' . $i . '_href" type="text" name="feature_' . $i . '_href" value="'
                . htmlspecialchars((string) ($feature['href'] ?? ''), ENT_QUOTES) . '" placeholder="Lien" />'
                . '</div>'
                . '<input class="dev-input" id="feature_' . $i . '_description" type="text" name="feature_' . $i . '_description" value="'
                . htmlspecialchars((string) ($feature['description'] ?? ''), ENT_QUOTES) . '" placeholder="Description" />'
                . '</div>';
        }
        $html .= '</div>';

        $html .= '<div class="dev-panel dev-panel--compact"><h2 class="dev-panel__title">Liens de navigation</h2>';
        for ($i = 0; $i < 4; $i++) {
            $link = is_array($navLinks[$i] ?? null) ? $navLinks[$i] : [];
            $html .= $this->linkPairFields('nav_' . $i, (string) ($link['label'] ?? ''), (string) ($link['href'] ?? ''));
        }
        $html .= '</div>';

        $html .= '<div class="dev-panel dev-panel--compact"><h2 class="dev-panel__title">Liens mobile supplémentaires</h2>';
        for ($i = 0; $i < 3; $i++) {
            $link = is_array($mobileLinks[$i] ?? null) ? $mobileLinks[$i] : [];
            $html .= $this->linkPairFields('mobile_' . $i, (string) ($link['label'] ?? ''), (string) ($link['href'] ?? ''));
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $button
     */
    private function loginFields(array $button, array $site): string
    {
        $enabled = ($button['enabled'] ?? false) === true ? 'checked' : '';
        $display = (string) ($button['display'] ?? 'page');

        return '<div class="dev-field dev-form__full"><h3 class="dev-panel__subtitle">Connexion</h3>'
            . '<label class="dev-checkbox"><input type="hidden" name="login_enabled" value="0" />'
            . '<input type="checkbox" name="login_enabled" value="1" ' . $enabled . ' /> Activé</label>'
            . '<div class="dev-form--grid dev-form--grid-2">'
            . '<div class="dev-field"><label class="dev-label" for="login_display">Affichage</label>'
            . '<select class="dev-input dev-select" id="login_display" name="login_display" data-login-display-select>'
            . LoginBlockPicker::displayOptions($display)
            . '</select></div>'
            . '<div class="dev-field"><label class="dev-label" for="login_label">Libellé</label>'
            . '<input class="dev-input" id="login_label" type="text" name="login_label" value="'
            . htmlspecialchars((string) ($button['label'] ?? ''), ENT_QUOTES) . '" /></div>'
            . LoginBlockPicker::render('login_block_ref', 'login_block_ref', (string) ($button['block_ref'] ?? ''), $site, 'chrome-variant-form')
            . '<div class="dev-field" data-login-href-field><label class="dev-label" for="login_href">Lien (page dédiée)</label>'
            . '<input class="dev-input" id="login_href" type="text" name="login_href" value="'
            . htmlspecialchars((string) ($button['href'] ?? ''), ENT_QUOTES) . '" placeholder="/login" /></div>'
            . '<div class="dev-field"><label class="dev-label" for="login_style">Style</label>'
            . '<select class="dev-input dev-select" id="login_style" name="login_style">'
            . ChromeButtonRenderer::optionsHtml((string) ($button['style'] ?? 'outline'))
            . '</select></div></div></div>';
    }

    /**
     * @param array<string, mixed> $button
     */
    private function buttonFields(string $prefix, string $title, array $button, string $defaultStyle = 'outline'): string
    {
        $enabled = ($button['enabled'] ?? false) === true ? 'checked' : '';

        return '<div class="dev-field dev-form__full"><h3 class="dev-panel__subtitle">' . htmlspecialchars($title, ENT_QUOTES) . '</h3>'
            . '<label class="dev-checkbox"><input type="hidden" name="' . $prefix . '_enabled" value="0" />'
            . '<input type="checkbox" name="' . $prefix . '_enabled" value="1" ' . $enabled . ' /> Activé</label>'
            . '<div class="dev-form--grid dev-form--grid-2">'
            . '<div class="dev-field"><label class="dev-label" for="' . $prefix . '_label">Libellé</label>'
            . '<input class="dev-input" id="' . $prefix . '_label" type="text" name="' . $prefix . '_label" value="'
            . htmlspecialchars((string) ($button['label'] ?? ''), ENT_QUOTES) . '" /></div>'
            . '<div class="dev-field"><label class="dev-label" for="' . $prefix . '_href">Lien</label>'
            . '<input class="dev-input" id="' . $prefix . '_href" type="text" name="' . $prefix . '_href" value="'
            . htmlspecialchars((string) ($button['href'] ?? ''), ENT_QUOTES) . '" /></div>'
            . '<div class="dev-field"><label class="dev-label" for="' . $prefix . '_style">Style</label>'
            . '<select class="dev-input dev-select" id="' . $prefix . '_style" name="' . $prefix . '_style">'
            . ChromeButtonRenderer::optionsHtml((string) ($button['style'] ?? $defaultStyle))
            . '</select></div></div></div>';
    }

    private function linkPairFields(string $prefix, string $label, string $href): string
    {
        return '<div class="dev-field"><label class="dev-label" for="' . $prefix . '_label">Libellé</label>'
            . '<input class="dev-input" id="' . $prefix . '_label" type="text" name="' . $prefix . '_label" value="'
            . htmlspecialchars($label, ENT_QUOTES) . '" /></div>'
            . '<div class="dev-field"><label class="dev-label" for="' . $prefix . '_href">Lien</label>'
            . '<input class="dev-input" id="' . $prefix . '_href" type="text" name="' . $prefix . '_href" value="'
            . htmlspecialchars($href, ENT_QUOTES) . '" /></div>';
    }

    /**
     * @param array<string, string> $data
     *
     * @return array<string, mixed>
     */
    public function parseForm(array $data, string $template): array
    {
        $template = HeaderStyle::normalizeTemplate($template);
        $payload = [
            'login' => $this->parseButton($data, 'login', '/login', 'outline', true),
            'cta' => $this->parseButton($data, 'cta', '#', 'primary'),
        ];

        if ($template === 'navbar5') {
            $payload['features_label'] = trim($data['features_label'] ?? 'Fonctionnalités');
            $features = [];
            for ($i = 0; $i < 6; $i++) {
                $title = trim($data['feature_' . $i . '_title'] ?? '');
                $description = trim($data['feature_' . $i . '_description'] ?? '');
                $href = trim($data['feature_' . $i . '_href'] ?? '');
                if ($title !== '') {
                    $features[] = ['title' => $title, 'description' => $description, 'href' => $href !== '' ? $href : '#'];
                }
            }
            $payload['features'] = $features;
            $payload['nav_links'] = $this->parseLinkPairs($data, 'nav_', 4);
            $payload['mobile_links'] = $this->parseLinkPairs($data, 'mobile_', 3);

            return $payload;
        }

        $menuItems = [];
        for ($i = 0; $i < 5; $i++) {
            $label = trim($data['menu_' . $i . '_label'] ?? '');
            $href = trim($data['menu_' . $i . '_href'] ?? '');
            $children = self::parseChildrenText($data['menu_' . $i . '_children'] ?? '');
            if ($label === '') {
                continue;
            }
            $item = ['label' => $label, 'href' => $href !== '' ? $href : '#'];
            if ($children !== []) {
                $item['children'] = $children;
            }
            $menuItems[] = $item;
        }
        $payload['menu_items'] = $menuItems;

        return $payload;
    }

    /**
     * @param array<string, string> $data
     *
     * @return array{enabled: bool, label: string, href: string, style: string}
     */
    private function parseButton(array $data, string $prefix, string $defaultHref, string $defaultStyle, bool $withLoginOptions = false): array
    {
        $button = [
            'enabled' => ($data[$prefix . '_enabled'] ?? '0') === '1',
            'label' => trim($data[$prefix . '_label'] ?? ''),
            'href' => trim($data[$prefix . '_href'] ?? '') !== '' ? trim($data[$prefix . '_href'] ?? '') : $defaultHref,
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
     * @param array<string, string> $data
     *
     * @return list<array{label: string, href: string}>
     */
    private function parseLinkPairs(array $data, string $prefix, int $count): array
    {
        $links = [];
        for ($i = 0; $i < $count; $i++) {
            $label = trim($data[$prefix . $i . '_label'] ?? '');
            $href = trim($data[$prefix . $i . '_href'] ?? '');
            if ($label !== '') {
                $links[] = ['label' => $label, 'href' => $href !== '' ? $href : '#'];
            }
        }

        return $links;
    }

    /**
     * @return list<array{label: string, description: string, href: string, icon: string}>
     */
    private static function parseChildrenText(string $text): array
    {
        $children = [];
        foreach (preg_split('/\r\n|\n|\r/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line));
            $label = $parts[0] ?? '';
            if ($label === '') {
                continue;
            }
            $children[] = [
                'label' => $label,
                'description' => $parts[1] ?? '',
                'href' => ($parts[2] ?? '') !== '' ? ($parts[2] ?? '#') : '#',
                'icon' => $parts[3] ?? '',
            ];
        }

        return $children;
    }
}

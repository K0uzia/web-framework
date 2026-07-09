<?php

declare(strict_types=1);

namespace Capsule;

final class ThemePalette
{
    public const GROUP_BASE = 'base';
    public const GROUP_ACTION = 'action';
    public const GROUP_STATE = 'state';

    public const KIND_BRAND = 'brand';
    public const KIND_BACKGROUND = 'background';
    public const KIND_SURFACE = 'surface';
    public const KIND_TEXT = 'text';
    public const KIND_BORDER = 'border';
    public const KIND_BUTTON_BG = 'button-bg';
    public const KIND_BUTTON_TEXT = 'button-text';
    public const KIND_LINK = 'link';
    public const KIND_SUCCESS = 'success';
    public const KIND_WARNING = 'warning';
    public const KIND_ERROR = 'error';
    public const KIND_INFO = 'info';
    public const KIND_FOCUS = 'focus';
    public const KIND_DISABLED = 'disabled';

    /**
     * @return list<array{key: string, label: string, description: string, default: string, group: string, kind: string}>
     */
    public static function definitions(): array
    {
        return [
            [
                'key' => 'primary',
                'label' => 'Primaire',
                'description' => 'Couleur principale de la marque, CTA importants, liens actifs.',
                'default' => '#3b82f6',
                'group' => self::GROUP_BASE,
                'kind' => self::KIND_BRAND,
            ],
            [
                'key' => 'primary_hover',
                'label' => 'Primaire au survol',
                'description' => 'Variante plus foncée ou saturée au survol du primaire.',
                'default' => '#2563eb',
                'group' => self::GROUP_BASE,
                'kind' => self::KIND_BRAND,
            ],
            [
                'key' => 'secondary',
                'label' => 'Secondaire',
                'description' => 'Accent ou soutien, pour les actions moins prioritaires.',
                'default' => '#64748b',
                'group' => self::GROUP_BASE,
                'kind' => self::KIND_BRAND,
            ],
            [
                'key' => 'secondary_hover',
                'label' => 'Secondaire au survol',
                'description' => 'Variante au survol du secondaire.',
                'default' => '#475569',
                'group' => self::GROUP_BASE,
                'kind' => self::KIND_BRAND,
            ],
            [
                'key' => 'background',
                'label' => 'Fond de page',
                'description' => 'Couleur de fond générale de la page (corps du site, bandes « fond de page »).',
                'default' => '#ffffff',
                'group' => self::GROUP_BASE,
                'kind' => self::KIND_BACKGROUND,
            ],
            [
                'key' => 'surface',
                'label' => 'Surface',
                'description' => 'Fond des cartes, panneaux, pied de page, menus déroulants et bandes atténuées.',
                'default' => '#f8fafc',
                'group' => self::GROUP_BASE,
                'kind' => self::KIND_SURFACE,
            ],
            [
                'key' => 'border',
                'label' => 'Bordures',
                'description' => 'Bordures, séparateurs et contours d\'inputs.',
                'default' => '#e2e8f0',
                'group' => self::GROUP_BASE,
                'kind' => self::KIND_BORDER,
            ],
            [
                'key' => 'text',
                'label' => 'Texte',
                'description' => 'Couleur principale du texte.',
                'default' => '#0f172a',
                'group' => self::GROUP_BASE,
                'kind' => self::KIND_TEXT,
            ],
            [
                'key' => 'text_muted',
                'label' => 'Texte atténué',
                'description' => 'Texte secondaire, descriptions et labels moins importants.',
                'default' => '#64748b',
                'group' => self::GROUP_BASE,
                'kind' => self::KIND_TEXT,
            ],
            [
                'key' => 'button_primary_bg',
                'label' => 'Bouton principal, fond',
                'description' => 'Fond du bouton principal.',
                'default' => '#3b82f6',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_BG,
            ],
            [
                'key' => 'button_primary_hover',
                'label' => 'Bouton principal, survol',
                'description' => 'Fond du bouton principal au survol.',
                'default' => '#2563eb',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_BG,
            ],
            [
                'key' => 'button_primary_text',
                'label' => 'Bouton principal, texte',
                'description' => 'Texte du bouton principal.',
                'default' => '#ffffff',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_TEXT,
            ],
            [
                'key' => 'button_secondary_bg',
                'label' => 'Bouton secondaire, fond',
                'description' => 'Fond du bouton secondaire.',
                'default' => 'transparent',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_BG,
            ],
            [
                'key' => 'button_secondary_hover',
                'label' => 'Bouton secondaire, survol',
                'description' => 'Fond du bouton secondaire au survol.',
                'default' => 'color-mix(in srgb, var(--color-text) 6%, transparent)',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_BG,
            ],
            [
                'key' => 'button_secondary_text',
                'label' => 'Bouton secondaire, texte',
                'description' => 'Texte du bouton secondaire.',
                'default' => '#0f172a',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_TEXT,
            ],
            [
                'key' => 'link',
                'label' => 'Liens',
                'description' => 'Couleur des liens.',
                'default' => '#3b82f6',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_LINK,
            ],
            [
                'key' => 'link_hover',
                'label' => 'Liens au survol',
                'description' => 'Couleur des liens au survol.',
                'default' => '#2563eb',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_LINK,
            ],
            [
                'key' => 'success',
                'label' => 'Succès',
                'description' => 'Validation, succès, messages positifs.',
                'default' => '#16a34a',
                'group' => self::GROUP_STATE,
                'kind' => self::KIND_SUCCESS,
            ],
            [
                'key' => 'warning',
                'label' => 'Avertissement',
                'description' => 'Alerte modérée, attention.',
                'default' => '#d97706',
                'group' => self::GROUP_STATE,
                'kind' => self::KIND_WARNING,
            ],
            [
                'key' => 'error',
                'label' => 'Erreur',
                'description' => 'Erreur, problème, validation négative.',
                'default' => '#dc2626',
                'group' => self::GROUP_STATE,
                'kind' => self::KIND_ERROR,
            ],
            [
                'key' => 'info',
                'label' => 'Information',
                'description' => 'Information neutre ou pédagogique.',
                'default' => '#0284c7',
                'group' => self::GROUP_STATE,
                'kind' => self::KIND_INFO,
            ],
            [
                'key' => 'focus_ring',
                'label' => 'Contour de focus',
                'description' => 'Contour de focus clavier et accessibilité.',
                'default' => '#3b82f6',
                'group' => self::GROUP_STATE,
                'kind' => self::KIND_FOCUS,
            ],
            [
                'key' => 'disabled',
                'label' => 'Désactivé',
                'description' => 'État désactivé, généralement grisé.',
                'default' => '#94a3b8',
                'group' => self::GROUP_STATE,
                'kind' => self::KIND_DISABLED,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        $defaults = [];
        foreach (self::definitions() as $definition) {
            $defaults[$definition['key']] = $definition['default'];
        }

        return $defaults;
    }

    /**
     * @param array<string, mixed> $colors
     *
     * @return array<string, string>
     */
    public static function normalize(array $colors): array
    {
        $normalized = [];
        foreach (self::definitions() as $definition) {
            $key = $definition['key'];
            $raw = $colors[$key] ?? '';
            if (!is_string($raw) || trim($raw) === '') {
                $raw = self::legacyFallback($key, $colors) ?? $definition['default'];
            }
            $normalized[$key] = ThemeColor::normalize($raw, $definition['default']);
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $colors
     *
     * @return array<string, string>
     */
    public static function fromForm(array $data, array $colors = []): array
    {
        $merged = self::normalize($colors);
        foreach (self::definitions() as $definition) {
            $field = self::formFieldName($definition['key']);
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $merged[$definition['key']] = ThemeColor::normalize(
                (string) $data[$field],
                $definition['default'],
            );
        }

        return $merged;
    }

    public static function formFieldName(string $key): string
    {
        return 'color_' . $key;
    }

    /**
     * @param array<string, string> $colors
     */
    public static function renderFieldsHtml(array $colors): string
    {
        $groups = [
            self::GROUP_BASE => [
                'title' => 'Palette de base',
                'description' => 'Marque, fonds, texte et bordures.',
                'icon' => 'fa-palette',
            ],
            self::GROUP_ACTION => [
                'title' => 'Couleurs d\'action',
                'description' => 'Boutons et liens du site.',
                'icon' => 'fa-hand-pointer',
            ],
            self::GROUP_STATE => [
                'title' => 'États UI',
                'description' => 'Retours utilisateur et accessibilité.',
                'icon' => 'fa-shield-halved',
            ],
        ];

        $html = '<div class="dev-color-accordion" data-dev-color-accordion>';
        foreach ($groups as $groupKey => $group) {
            $title = htmlspecialchars($group['title'], ENT_QUOTES);
            $description = htmlspecialchars($group['description'], ENT_QUOTES);
            $groupIcon = htmlspecialchars($group['icon'], ENT_QUOTES);
            $html .= '<details class="dev-color-group" name="theme-colors">';
            $html .= '<summary class="dev-color-group__summary">';
            $html .= '<span class="dev-color-group__heading">';
            $html .= '<span class="dev-color-group__title-row">';
            $html .= '<i class="fa-solid ' . $groupIcon . ' dev-color-group__title-icon" aria-hidden="true"></i>';
            $html .= '<span class="dev-color-group__title">' . $title . '</span>';
            $html .= '</span>';
            $html .= '<span class="dev-color-group__desc">' . $description . '</span>';
            $html .= '</span>';
            $html .= '<i class="fa-solid fa-chevron-down dev-color-group__icon" aria-hidden="true"></i>';
            $html .= '</summary>';
            $html .= '<div class="dev-color-list">';
            foreach (self::definitions() as $definition) {
                if ($definition['group'] !== $groupKey) {
                    continue;
                }
                $html .= self::renderFieldHtml($definition, $colors[$definition['key']] ?? $definition['default']);
            }
            $html .= '</div></details>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array{key: string, label: string, description: string, default: string, group: string, kind: string} $definition
     */
    private static function renderFieldHtml(array $definition, string $value): string
    {
        $field = self::formFieldName($definition['key']);
        $normalized = ThemeColor::normalize($value, $definition['default']);
        $label = htmlspecialchars($definition['label'], ENT_QUOTES);
        $description = htmlspecialchars($definition['description'], ENT_QUOTES);
        $helpLabel = htmlspecialchars($definition['label'] . ' : ' . $definition['description'], ENT_QUOTES);
        $ariaHex = htmlspecialchars('Valeur hexadécimale ' . $definition['label'], ENT_QUOTES);
        $kind = $definition['kind'];
        $kindLabel = htmlspecialchars(self::kindLabel($kind), ENT_QUOTES);
        $icon = htmlspecialchars(self::iconForKind($kind), ENT_QUOTES);

        return '<div class="dev-color-row dev-color-row--' . htmlspecialchars($kind, ENT_QUOTES) . '">'
            . '<span class="dev-color-row__type" title="' . $kindLabel . '">'
            . '<i class="fa-solid ' . $icon . '" aria-hidden="true"></i>'
            . '<span class="visually-hidden">' . $kindLabel . '</span>'
            . '</span>'
            . '<div class="dev-color-row__label-wrap">'
            . '<label class="dev-color-row__label" for="' . $field . '">' . $label . '</label>'
            . '<button type="button" class="dev-icon-btn dev-color-row__help" aria-label="' . $helpLabel . '" title="' . $description . '">'
            . '<i class="fa-solid fa-circle-info" aria-hidden="true"></i></button>'
            . '</div>'
            . '<div class="dev-color-field" data-color-sync>'
            . '<input type="color" id="' . $field . '" name="' . $field . '" value="' . htmlspecialchars($normalized, ENT_QUOTES) . '" />'
            . '<input class="dev-input dev-input--sm" type="text" data-color-text value="' . htmlspecialchars($normalized, ENT_QUOTES) . '" aria-label="' . $ariaHex . '" />'
            . '<button type="button" class="dev-icon-btn dev-color-field__pick" data-color-eyedropper aria-label="Prélever une couleur" title="Prélever la couleur d\'un élément"><i class="fa-solid fa-eyedropper" aria-hidden="true"></i></button>'
            . '</div></div>';
    }

    private static function iconForKind(string $kind): string
    {
        return match ($kind) {
            self::KIND_BRAND => 'fa-droplet',
            self::KIND_BACKGROUND => 'fa-panorama',
            self::KIND_SURFACE => 'fa-layer-group',
            self::KIND_TEXT => 'fa-font',
            self::KIND_BORDER => 'fa-border-all',
            self::KIND_BUTTON_BG => 'fa-square',
            self::KIND_BUTTON_TEXT => 'fa-a',
            self::KIND_LINK => 'fa-link',
            self::KIND_SUCCESS => 'fa-circle-check',
            self::KIND_WARNING => 'fa-triangle-exclamation',
            self::KIND_ERROR => 'fa-circle-xmark',
            self::KIND_INFO => 'fa-circle-info',
            self::KIND_FOCUS => 'fa-bullseye',
            self::KIND_DISABLED => 'fa-ban',
            default => 'fa-palette',
        };
    }

    private static function kindLabel(string $kind): string
    {
        return match ($kind) {
            self::KIND_BRAND => 'Couleur de marque',
            self::KIND_BACKGROUND => 'Fond de page',
            self::KIND_SURFACE => 'Surface',
            self::KIND_TEXT => 'Texte',
            self::KIND_BORDER => 'Bordure',
            self::KIND_BUTTON_BG => 'Fond de bouton',
            self::KIND_BUTTON_TEXT => 'Texte de bouton',
            self::KIND_LINK => 'Lien',
            self::KIND_SUCCESS => 'Succès',
            self::KIND_WARNING => 'Avertissement',
            self::KIND_ERROR => 'Erreur',
            self::KIND_INFO => 'Information',
            self::KIND_FOCUS => 'Focus clavier',
            self::KIND_DISABLED => 'Élément désactivé',
            default => 'Couleur',
        };
    }

    /**
     * @param array<string, mixed> $colors
     */
    private static function legacyFallback(string $key, array $colors): ?string
    {
        if ($key === 'surface' && isset($colors['muted']) && is_string($colors['muted'])) {
            return $colors['muted'];
        }

        return null;
    }
}

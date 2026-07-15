<?php

declare(strict_types=1);

namespace Capsule;

final class ThemePalette
{
    public const GROUP_BASE = 'base';
    public const GROUP_ACTION = 'action';
    public const GROUP_HEADER = 'header';
    public const GROUP_FOOTER = 'footer';
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

    /** @var list<string> */
    private const BUTTON_COLOR_KEYS = [
        'button_primary_bg',
        'button_primary_border',
        'button_primary_text',
        'button_primary_hover',
        'button_primary_border_hover',
        'button_primary_text_hover',
        'button_secondary_bg',
        'button_secondary_border',
        'button_secondary_text',
        'button_secondary_hover',
        'button_secondary_border_hover',
        'button_secondary_text_hover',
        'button_outline_border',
        'button_outline_text',
        'button_outline_border_hover',
        'button_outline_text_hover',
    ];

    /** @var list<string> */
    private const NAV_LINK_COLOR_KEYS = [
        'nav_link_text',
        'nav_link_bg',
        'nav_link_text_hover',
        'nav_link_bg_hover',
        'nav_link_text_active',
        'nav_link_bg_active',
    ];

    /** @var list<string> */
    private const CONTENT_LINK_COLOR_KEYS = [
        'link',
        'link_hover',
    ];

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
                'description' => 'Fond du bouton principal à l\'état normal.',
                'default' => '#3b82f6',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_BG,
            ],
            [
                'key' => 'button_primary_border',
                'label' => 'Bouton principal, bordure',
                'description' => 'Bordure du bouton principal à l\'état normal.',
                'default' => '#3b82f6',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BORDER,
            ],
            [
                'key' => 'button_primary_text',
                'label' => 'Bouton principal, texte',
                'description' => 'Texte du bouton principal à l\'état normal.',
                'default' => '#ffffff',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_TEXT,
            ],
            [
                'key' => 'button_primary_hover',
                'label' => 'Bouton principal, fond au survol',
                'description' => 'Fond du bouton principal au survol.',
                'default' => '#2563eb',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_BG,
            ],
            [
                'key' => 'button_primary_border_hover',
                'label' => 'Bouton principal, bordure au survol',
                'description' => 'Bordure du bouton principal au survol.',
                'default' => '#2563eb',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BORDER,
            ],
            [
                'key' => 'button_primary_text_hover',
                'label' => 'Bouton principal, texte au survol',
                'description' => 'Texte du bouton principal au survol.',
                'default' => '#ffffff',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_TEXT,
            ],
            [
                'key' => 'button_secondary_bg',
                'label' => 'Bouton secondaire, fond',
                'description' => 'Fond du bouton secondaire à l\'état normal.',
                'default' => 'transparent',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_BG,
            ],
            [
                'key' => 'button_secondary_border',
                'label' => 'Bouton secondaire, bordure',
                'description' => 'Bordure du bouton secondaire à l\'état normal.',
                'default' => '#e2e8f0',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BORDER,
            ],
            [
                'key' => 'button_secondary_text',
                'label' => 'Bouton secondaire, texte',
                'description' => 'Texte du bouton secondaire à l\'état normal.',
                'default' => '#0f172a',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_TEXT,
            ],
            [
                'key' => 'button_secondary_hover',
                'label' => 'Bouton secondaire, fond au survol',
                'description' => 'Fond du bouton secondaire au survol.',
                'default' => '#f1f5f9',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_BG,
            ],
            [
                'key' => 'button_secondary_border_hover',
                'label' => 'Bouton secondaire, bordure au survol',
                'description' => 'Bordure du bouton secondaire au survol.',
                'default' => '#cbd5e1',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BORDER,
            ],
            [
                'key' => 'button_secondary_text_hover',
                'label' => 'Bouton secondaire, texte au survol',
                'description' => 'Texte du bouton secondaire au survol.',
                'default' => '#0f172a',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_TEXT,
            ],
            [
                'key' => 'button_outline_border',
                'label' => 'Bouton contour, bordure',
                'description' => 'Bordure du bouton contour à l\'état normal.',
                'default' => '#cbd5e1',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BORDER,
            ],
            [
                'key' => 'button_outline_text',
                'label' => 'Bouton contour, texte',
                'description' => 'Texte du bouton contour à l\'état normal.',
                'default' => '#0f172a',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_TEXT,
            ],
            [
                'key' => 'button_outline_border_hover',
                'label' => 'Bouton contour, bordure au survol',
                'description' => 'Bordure du bouton contour au survol.',
                'default' => '#94a3b8',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BORDER,
            ],
            [
                'key' => 'button_outline_text_hover',
                'label' => 'Bouton contour, texte au survol',
                'description' => 'Texte du bouton contour au survol.',
                'default' => '#0f172a',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_TEXT,
            ],
            [
                'key' => 'nav_link_text',
                'label' => 'Navigation, texte',
                'description' => 'Texte des liens de menu à l\'état normal.',
                'default' => '#64748b',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_TEXT,
            ],
            [
                'key' => 'nav_link_bg',
                'label' => 'Navigation, fond',
                'description' => 'Fond des liens de menu à l\'état normal.',
                'default' => 'transparent',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_BG,
            ],
            [
                'key' => 'nav_link_text_hover',
                'label' => 'Navigation, texte au survol',
                'description' => 'Texte des liens de menu au survol.',
                'default' => '#2563eb',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_TEXT,
            ],
            [
                'key' => 'nav_link_bg_hover',
                'label' => 'Navigation, fond au survol',
                'description' => 'Fond des liens de menu au survol.',
                'default' => '#eff6ff',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_BG,
            ],
            [
                'key' => 'nav_link_text_active',
                'label' => 'Navigation, texte actif',
                'description' => 'Texte du lien de menu actif ou de la page courante.',
                'default' => '#3b82f6',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_TEXT,
            ],
            [
                'key' => 'nav_link_bg_active',
                'label' => 'Navigation, fond actif',
                'description' => 'Fond du lien de menu actif ou de la page courante.',
                'default' => '#dbeafe',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_BUTTON_BG,
            ],
            [
                'key' => 'link',
                'label' => 'Liens de contenu',
                'description' => 'Couleur des liens dans le texte des pages.',
                'default' => '#3b82f6',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_LINK,
            ],
            [
                'key' => 'link_hover',
                'label' => 'Liens de contenu au survol',
                'description' => 'Couleur des liens de contenu au survol.',
                'default' => '#2563eb',
                'group' => self::GROUP_ACTION,
                'kind' => self::KIND_LINK,
            ],
            [
                'key' => 'header_background',
                'label' => 'En-tête, fond',
                'description' => 'Fond de l\'en-tête par défaut (surcharge possible par variante dans En-tête & pied).',
                'default' => '#ffffff',
                'group' => self::GROUP_HEADER,
                'kind' => self::KIND_SURFACE,
            ],
            [
                'key' => 'header_text',
                'label' => 'En-tête, texte',
                'description' => 'Nom de marque et texte principal de l\'en-tête.',
                'default' => '#0f172a',
                'group' => self::GROUP_HEADER,
                'kind' => self::KIND_TEXT,
            ],
            [
                'key' => 'header_text_muted',
                'label' => 'En-tête, texte atténué',
                'description' => 'Accroche et libellés secondaires de l\'en-tête.',
                'default' => '#64748b',
                'group' => self::GROUP_HEADER,
                'kind' => self::KIND_TEXT,
            ],
            [
                'key' => 'header_link',
                'label' => 'En-tête, liens',
                'description' => 'Liens de navigation et actions secondaires de l\'en-tête.',
                'default' => '#64748b',
                'group' => self::GROUP_HEADER,
                'kind' => self::KIND_LINK,
            ],
            [
                'key' => 'header_link_hover',
                'label' => 'En-tête, liens au survol',
                'description' => 'Couleur des liens de l\'en-tête au survol.',
                'default' => '#2563eb',
                'group' => self::GROUP_HEADER,
                'kind' => self::KIND_LINK,
            ],
            [
                'key' => 'footer_background',
                'label' => 'Pied de page, fond',
                'description' => 'Fond du pied de page par défaut (surcharge possible par variante dans En-tête & pied).',
                'default' => '#f8fafc',
                'group' => self::GROUP_FOOTER,
                'kind' => self::KIND_SURFACE,
            ],
            [
                'key' => 'footer_text',
                'label' => 'Pied de page, texte',
                'description' => 'Titres de colonnes, nom de marque et texte principal du pied de page.',
                'default' => '#0f172a',
                'group' => self::GROUP_FOOTER,
                'kind' => self::KIND_TEXT,
            ],
            [
                'key' => 'footer_text_muted',
                'label' => 'Pied de page, texte atténué',
                'description' => 'Accroche, copyright, descriptions et libellés secondaires.',
                'default' => '#64748b',
                'group' => self::GROUP_FOOTER,
                'kind' => self::KIND_TEXT,
            ],
            [
                'key' => 'footer_link',
                'label' => 'Pied de page, liens',
                'description' => 'Liens de navigation, colonnes, réseaux sociaux et mentions légales.',
                'default' => '#64748b',
                'group' => self::GROUP_FOOTER,
                'kind' => self::KIND_LINK,
            ],
            [
                'key' => 'footer_link_hover',
                'label' => 'Pied de page, liens au survol',
                'description' => 'Couleur des liens du pied de page au survol.',
                'default' => '#2563eb',
                'group' => self::GROUP_FOOTER,
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
                'description' => 'Boutons (repos et survol) et liens.',
                'icon' => 'fa-hand-pointer',
            ],
            self::GROUP_HEADER => [
                'title' => 'En-tête',
                'description' => 'Fond, textes et liens de l\'en-tête.',
                'icon' => 'fa-bars',
            ],
            self::GROUP_FOOTER => [
                'title' => 'Pied de page',
                'description' => 'Fond, textes et liens du pied de page.',
                'icon' => 'fa-table-columns',
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
            if ($groupKey === self::GROUP_ACTION) {
                $html .= self::renderActionGroupHtml($colors);
            } else {
                $html .= '<div class="dev-color-list">';
                foreach (self::definitions() as $definition) {
                    if ($definition['group'] !== $groupKey) {
                        continue;
                    }
                    $html .= self::renderFieldHtml($definition, $colors[$definition['key']] ?? $definition['default']);
                }
                $html .= '</div>';
            }
            $html .= '</details>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string, string> $colors
     */
    private static function renderActionGroupHtml(array $colors): string
    {
        $definitionsByKey = [];
        foreach (self::definitions() as $definition) {
            $definitionsByKey[$definition['key']] = $definition;
        }

        $html = '<div class="dev-color-list dev-color-list--action">';
        $html .= '<div class="dev-action-colors">';
        foreach (self::buttonVariantLayouts() as $variant) {
            $html .= self::renderVariantMatrix($variant, $definitionsByKey, $colors);
        }
        $html .= self::renderVariantMatrix(self::navLinkVariantLayout(), $definitionsByKey, $colors);
        $html .= '</div>';
        $html .= '<div class="dev-action-colors__links">';
        $html .= '<p class="dev-action-colors__links-title">Liens de contenu</p>';
        foreach (self::definitions() as $definition) {
            if ($definition['group'] !== self::GROUP_ACTION || !self::isContentLinkColorKey($definition['key'])) {
                continue;
            }
            $html .= self::renderFieldHtml($definition, $colors[$definition['key']] ?? $definition['default']);
        }
        $html .= '</div></div>';

        return $html;
    }

    /**
     * @return list<array{title: string, description: string, columns: list<array{label: string, icon: string, kind: string}>, rows: list<array{label: string, keys: list<string>}>}>
     */
    private static function buttonVariantLayouts(): array
    {
        $fullColumns = [
            ['label' => 'Fond', 'icon' => 'fa-square', 'kind' => self::KIND_BUTTON_BG],
            ['label' => 'Bordure', 'icon' => 'fa-border-all', 'kind' => self::KIND_BORDER],
            ['label' => 'Texte', 'icon' => 'fa-font', 'kind' => self::KIND_BUTTON_TEXT],
        ];
        $outlineColumns = [
            ['label' => 'Bordure', 'icon' => 'fa-border-all', 'kind' => self::KIND_BORDER],
            ['label' => 'Texte', 'icon' => 'fa-font', 'kind' => self::KIND_BUTTON_TEXT],
        ];

        return [
            [
                'title' => 'Bouton principal',
                'description' => 'CTA et actions prioritaires.',
                'columns' => $fullColumns,
                'rows' => [
                    ['label' => 'Repos', 'keys' => ['button_primary_bg', 'button_primary_border', 'button_primary_text']],
                    ['label' => 'Survol', 'keys' => ['button_primary_hover', 'button_primary_border_hover', 'button_primary_text_hover']],
                ],
            ],
            [
                'title' => 'Bouton secondaire',
                'description' => 'Ghost, navigation et actions de soutien.',
                'columns' => $fullColumns,
                'rows' => [
                    ['label' => 'Repos', 'keys' => ['button_secondary_bg', 'button_secondary_border', 'button_secondary_text']],
                    ['label' => 'Survol', 'keys' => ['button_secondary_hover', 'button_secondary_border_hover', 'button_secondary_text_hover']],
                ],
            ],
            [
                'title' => 'Bouton contour',
                'description' => 'Bordure et texte uniquement, sans fond.',
                'columns' => $outlineColumns,
                'rows' => [
                    ['label' => 'Repos', 'keys' => ['button_outline_border', 'button_outline_text']],
                    ['label' => 'Survol', 'keys' => ['button_outline_border_hover', 'button_outline_text_hover']],
                ],
            ],
        ];
    }

    /**
     * @return array{title: string, description: string, columns: list<array{label: string, icon: string, kind: string}>, rows: list<array{label: string, keys: list<string>}>}
     */
    private static function navLinkVariantLayout(): array
    {
        return [
            'title' => 'Liens de navigation',
            'description' => 'Menu en-tête, sous-menus et navigation du pied de page.',
            'columns' => [
                ['label' => 'Texte', 'icon' => 'fa-font', 'kind' => self::KIND_TEXT],
                ['label' => 'Fond', 'icon' => 'fa-square', 'kind' => self::KIND_BUTTON_BG],
            ],
            'rows' => [
                ['label' => 'Repos', 'keys' => ['nav_link_text', 'nav_link_bg']],
                ['label' => 'Survol', 'keys' => ['nav_link_text_hover', 'nav_link_bg_hover']],
                ['label' => 'Actif', 'keys' => ['nav_link_text_active', 'nav_link_bg_active']],
            ],
        ];
    }

    /**
     * @param array{title: string, description: string, columns: list<array{label: string, icon: string, kind: string}>, rows: list<array{label: string, keys: list<string>}>} $variant
     * @param array<string, array{key: string, label: string, description: string, default: string, group: string, kind: string}> $definitionsByKey
     * @param array<string, string> $colors
     */
    private static function renderVariantMatrix(array $variant, array $definitionsByKey, array $colors): string
    {
        $title = htmlspecialchars($variant['title'], ENT_QUOTES);
        $description = htmlspecialchars($variant['description'], ENT_QUOTES);
        $columnCount = count($variant['columns']);
        $matrixClass = 'dev-action-matrix dev-action-matrix--cols-' . $columnCount;

        $html = '<section class="dev-action-matrix__block" aria-label="' . $title . '">';
        $html .= '<div class="dev-action-matrix__intro">';
        $html .= '<h3 class="dev-action-matrix__title">' . $title . '</h3>';
        $html .= '<p class="dev-action-matrix__desc">' . $description . '</p>';
        $html .= '</div>';
        $html .= '<div class="' . $matrixClass . '" role="group">';
        $html .= '<div class="dev-action-matrix__corner" aria-hidden="true"></div>';
        foreach ($variant['columns'] as $column) {
            $html .= '<div class="dev-action-matrix__colhead dev-action-matrix__colhead--' . htmlspecialchars($column['kind'], ENT_QUOTES) . '">';
            $html .= '<i class="fa-solid ' . htmlspecialchars($column['icon'], ENT_QUOTES) . '" aria-hidden="true"></i>';
            $html .= '<span>' . htmlspecialchars($column['label'], ENT_QUOTES) . '</span>';
            $html .= '</div>';
        }
        foreach ($variant['rows'] as $row) {
            $html .= self::renderMatrixRow($row['label'], $row['keys'], $definitionsByKey, $colors);
        }
        $html .= '</div></section>';

        return $html;
    }

    /**
     * @param list<string> $keys
     * @param array<string, array{key: string, label: string, description: string, default: string, group: string, kind: string}> $definitionsByKey
     * @param array<string, string> $colors
     */
    private static function renderMatrixRow(string $stateLabel, array $keys, array $definitionsByKey, array $colors): string
    {
        $html = '<div class="dev-action-matrix__rowhead">' . htmlspecialchars($stateLabel, ENT_QUOTES) . '</div>';
        foreach ($keys as $key) {
            $definition = $definitionsByKey[$key] ?? null;
            if ($definition === null) {
                $html .= '<div class="dev-action-matrix__cell"></div>';
                continue;
            }
            $html .= '<div class="dev-action-matrix__cell">' . self::renderMatrixColorField($definition, $colors[$key] ?? $definition['default']) . '</div>';
        }

        return $html;
    }

    /**
     * @param array{key: string, label: string, description: string, default: string, group: string, kind: string} $definition
     */
    private static function renderMatrixColorField(array $definition, string $value): string
    {
        return self::renderColorFieldControls($definition, $value, 'dev-color-field--matrix');
    }

    private static function supportsTransparentBackground(array $definition): bool
    {
        return in_array($definition['kind'], [self::KIND_BUTTON_BG, self::KIND_BACKGROUND], true);
    }

    /**
     * @param array{key: string, label: string, description: string, default: string, group: string, kind: string} $definition
     */
    private static function renderColorFieldControls(array $definition, string $value, string $extraClass = ''): string
    {
        $field = self::formFieldName($definition['key']);
        $normalized = ThemeColor::normalize($value, $definition['default']);
        $pickerHex = ThemeColor::pickerHex($value, $definition['default']);
        $description = htmlspecialchars($definition['description'], ENT_QUOTES);
        $helpLabel = htmlspecialchars($definition['label'] . ' : ' . $definition['description'], ENT_QUOTES);
        $ariaHex = htmlspecialchars('Valeur hexadécimale ' . $definition['label'], ENT_QUOTES);
        $supportsTransparent = self::supportsTransparentBackground($definition);
        $isTransparent = ThemeColor::isTransparent($normalized);
        $wrapClass = trim('dev-color-field ' . $extraClass . ($supportsTransparent ? ' dev-color-field--has-transparent' : '') . ($isTransparent ? ' is-transparent' : ''));

        $html = '<div class="' . htmlspecialchars($wrapClass, ENT_QUOTES) . '" data-color-sync title="' . $description . '">';
        $html .= '<input type="color" id="' . $field . '" value="' . htmlspecialchars($pickerHex, ENT_QUOTES) . '" aria-label="' . $helpLabel . '"' . ($isTransparent ? ' disabled' : '') . ' />';
        $html .= '<input class="dev-input dev-input--sm" type="text" name="' . $field . '" data-color-text value="' . htmlspecialchars($normalized, ENT_QUOTES) . '" aria-label="' . $ariaHex . '" />';
        if ($supportsTransparent) {
            $pressed = $isTransparent ? 'true' : 'false';
            $html .= '<button type="button" class="dev-icon-btn dev-color-field__transparent" data-color-transparent aria-pressed="' . $pressed . '" aria-label="Fond transparent : ' . $helpLabel . '" title="Fond transparent">';
            $html .= '<i class="fa-solid fa-ban" aria-hidden="true"></i></button>';
        }
        $html .= '<button type="button" class="dev-icon-btn dev-color-field__pick" data-color-eyedropper aria-label="Prélever : ' . $helpLabel . '" title="Prélever une couleur">';
        $html .= '<i class="fa-solid fa-eyedropper" aria-hidden="true"></i></button>';
        $html .= '</div>';

        return $html;
    }

    private static function isButtonColorKey(string $key): bool
    {
        return in_array($key, self::BUTTON_COLOR_KEYS, true);
    }

    private static function isNavLinkColorKey(string $key): bool
    {
        return in_array($key, self::NAV_LINK_COLOR_KEYS, true);
    }

    private static function isContentLinkColorKey(string $key): bool
    {
        return in_array($key, self::CONTENT_LINK_COLOR_KEYS, true);
    }

    /**
     * @param array{key: string, label: string, description: string, default: string, group: string, kind: string} $definition
     */
    private static function renderFieldHtml(array $definition, string $value): string
    {
        $field = self::formFieldName($definition['key']);
        $label = htmlspecialchars($definition['label'], ENT_QUOTES);
        $description = htmlspecialchars($definition['description'], ENT_QUOTES);
        $helpLabel = htmlspecialchars($definition['label'] . ' : ' . $definition['description'], ENT_QUOTES);
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
            . self::renderColorFieldControls($definition, $value)
            . '</div>';
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

        $fallbacks = [
            'button_primary_border' => ['button_primary_bg', 'primary'],
            'button_primary_text_hover' => ['button_primary_text'],
            'button_primary_border_hover' => ['button_primary_hover', 'primary_hover'],
            'button_secondary_border' => ['border'],
            'button_secondary_text_hover' => ['button_secondary_text', 'text'],
            'button_secondary_border_hover' => ['border'],
            'button_outline_text_hover' => ['button_outline_text', 'button_secondary_text'],
            'button_outline_border_hover' => ['button_outline_border', 'border'],
            'nav_link_text' => ['text_muted', 'text'],
            'nav_link_bg' => ['background'],
            'nav_link_text_hover' => ['link_hover', 'link'],
            'nav_link_bg_hover' => ['surface'],
            'nav_link_text_active' => ['link', 'primary'],
            'nav_link_bg_active' => ['surface'],
        ];

        if (!isset($fallbacks[$key])) {
            return null;
        }

        foreach ($fallbacks[$key] as $fallbackKey) {
            if (!isset($colors[$fallbackKey]) || !is_string($colors[$fallbackKey])) {
                continue;
            }
            $candidate = trim($colors[$fallbackKey]);
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }
}

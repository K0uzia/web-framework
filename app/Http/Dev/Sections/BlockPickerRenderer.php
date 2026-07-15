<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections;

use Capsule\SectionRegistry;

/**
 * Catalogue de blocs pour l'éditeur (taxonomie Marketing).
 */
final class BlockPickerRenderer
{
    /** @var array<string, string> */
    private const GROUP_LABELS = [
        'hero' => 'Hero',
        'feature' => 'Fonctionnalités',
        'integration' => 'Intégrations',
        'about' => 'À propos',
        'content' => 'Contenu',
        'gallery' => 'Galerie',
        'pricing' => 'Tarifs',
        'rate-card' => 'Grilles tarifaires',
        'compare' => 'Comparaison',
        'cta' => 'Appel à l\'action',
        'newsletter' => 'Inscription',
        'testimonial' => 'Témoignages',
        'stats' => 'Chiffres clés',
        'logos' => 'Logos',
        'team' => 'Équipe',
        'faq' => 'FAQ',
        'contact' => 'Contact',
        'blog' => 'Blog',
        'project' => 'Projets',
        'timeline' => 'Chronologie',
        'service' => 'Services',
        'auth' => 'Authentification',
        'career' => 'Carrières',
        'compliance' => 'Conformité',
        'case-study' => 'Études de cas',
        'changelog' => 'Journal des versions',
        'community' => 'Communauté',
        'download' => 'Téléchargements',
        'industry' => 'Secteurs',
        'list' => 'Listes',
        'experience' => 'Expérience',
        'process' => 'Processus',
        'waitlist' => 'Liste d\'attente',
        'award' => 'Récompenses',
        'resource' => 'Ressources',
        'code' => 'Exemples de code',
        'demo' => 'Démo',
        'ui' => 'Composants UI',
    ];

    public function __construct(private readonly SectionRegistry $registry)
    {
    }

    public function renderPickerHtml(): string
    {
        return '<div class="dev-block-picker__layout">'
            . '<aside class="dev-block-picker__sidebar" aria-label="Catégories de blocs">'
            . $this->renderFiltersHtml()
            . '</aside>'
            . '<div class="dev-block-picker__main">'
            . $this->renderSearchHtml()
            . '<div class="dev-block-picker__grid" id="dev-block-picker-grid" role="listbox" aria-label="Blocs disponibles">'
            . $this->renderCardsHtml()
            . '</div>'
            . '<p class="dev-block-picker__empty" id="dev-block-picker-empty" hidden>'
            . 'Aucun bloc ne correspond à votre recherche.'
            . '</p>'
            . '</div>'
            . '</div>';
    }

    public function countPickerCards(): int
    {
        $count = 0;
        foreach ($this->registry->getTypes() as $type) {
            if (!$this->registry->isVisibleInPagePicker($type)) {
                continue;
            }
            $variants = $this->registry->getVariants($type);
            $count += count($variants) > 1 ? count($variants) : 1;
        }

        return $count;
    }

    private function renderSearchHtml(): string
    {
        return '<div class="dev-block-picker__search">'
            . '<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>'
            . '<input type="search" id="dev-block-picker-search" placeholder="Rechercher un bloc..." '
            . 'autocomplete="off" aria-label="Rechercher un bloc" />'
            . '</div>';
    }

    private function renderFiltersHtml(): string
    {
        $groups = $this->registry->getGroups();
        $buttons = ['<button type="button" class="dev-block-nav-item is-active" data-block-filter="all">Tous</button>'];
        foreach ($groups as $group) {
            $label = self::GROUP_LABELS[$group] ?? ucfirst($group);
            $buttons[] = '<button type="button" class="dev-block-nav-item" data-block-filter="'
                . htmlspecialchars($group, ENT_QUOTES) . '">'
                . htmlspecialchars($label, ENT_QUOTES) . '</button>';
        }

        return '<nav class="dev-block-picker__nav" role="toolbar" aria-label="Filtrer les blocs">'
            . implode('', $buttons) . '</nav>';
    }

    private function renderCardsHtml(): string
    {
        $cards = [];
        foreach ($this->registry->getTypes() as $type) {
            if (!$this->registry->isVisibleInPagePicker($type)) {
                continue;
            }
            $def = $this->registry->getTypeDefinition($type);
            $label = is_string($def['label'] ?? null) ? $def['label'] : $type;
            $icon = is_string($def['icon'] ?? null) ? $def['icon'] : 'fa-solid fa-square';
            $desc = is_string($def['description'] ?? null) ? $def['description'] : '';
            $group = $this->registry->getGroup($type);
            $variants = $this->registry->getVariants($type);

            if (count($variants) > 1) {
                foreach ($variants as $variantKey => $variantDef) {
                    $variantKey = (string) $variantKey;
                    $variantLabel = is_array($variantDef) && is_string($variantDef['label'] ?? null)
                        ? $variantDef['label']
                        : $variantKey;
                    $variantDesc = is_array($variantDef) && is_string($variantDef['description'] ?? null)
                        ? $variantDef['description']
                        : $desc;
                    $cardTitle = $label . ' : ' . $variantLabel;

                    $cards[] = $this->renderCard(
                        $type,
                        $group,
                        $icon,
                        $cardTitle,
                        $variantDesc,
                        $variantKey,
                    );
                }
                continue;
            }

            $cards[] = $this->renderCard($type, $group, $icon, $label, $desc, '');
        }

        return implode('', $cards);
    }

    private function renderCard(
        string $type,
        string $group,
        string $icon,
        string $title,
        string $description,
        string $variant,
    ): string {
        $variantAttr = $variant !== ''
            ? ' data-block-variant="' . htmlspecialchars($variant, ENT_QUOTES) . '"'
            : '';

        return '<button type="button" class="dev-block-card" role="option" data-block-type="'
            . htmlspecialchars($type, ENT_QUOTES) . '" data-block-group="'
            . htmlspecialchars($group, ENT_QUOTES) . '"' . $variantAttr . '>'
            . '<span class="dev-block-card__icon" aria-hidden="true"><i class="' . htmlspecialchars($icon, ENT_QUOTES) . '"></i></span>'
            . '<span class="dev-block-card__content">'
            . '<span class="dev-block-card__title">' . htmlspecialchars($title, ENT_QUOTES) . '</span>'
            . '<span class="dev-block-card__desc">' . htmlspecialchars($description, ENT_QUOTES) . '</span>'
            . '</span>'
            . '</button>';
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Dev;

/**
 * Grille visuelle des modèles de page (taxonomie shadcnblocks Pages).
 */
final class PageTemplatePickerRenderer
{
    /** @var array<string, string> */
    private const CATEGORY_LABELS = [
        'blank' => 'Vide',
        'landing' => 'Landing',
        'blog' => 'Blog',
        'pricing' => 'Tarifs',
        'changelog' => 'Changelog',
        'about' => 'À propos',
        'contact' => 'Contact',
        'integrations' => 'Intégrations',
        'faq' => 'FAQ',
        'product' => 'Produit',
        'feature' => 'Fonctionnalités',
    ];

    public function renderPickerHtml(string $selected = 'blank'): string
    {
        $filters = $this->renderFiltersHtml();
        $cards = $this->renderCardsHtml($selected);

        return $filters
            . '<div class="dev-page-template-grid" id="dev-page-template-grid" role="listbox" aria-label="Modèles de page">'
            . $cards
            . '</div>';
    }

    private function renderFiltersHtml(): string
    {
        $buttons = ['<button type="button" class="dev-block-filter is-active" data-page-template-filter="all">Tous</button>'];
        foreach (PageTemplates::categories() as $category) {
            $label = self::CATEGORY_LABELS[$category] ?? ucfirst($category);
            $buttons[] = '<button type="button" class="dev-block-filter" data-page-template-filter="'
                . htmlspecialchars($category, ENT_QUOTES) . '">'
                . htmlspecialchars($label, ENT_QUOTES) . '</button>';
        }

        return '<div class="dev-block-picker__filters dev-page-template-filters" role="toolbar" aria-label="Filtrer les modèles">'
            . implode('', $buttons) . '</div>';
    }

    private function renderCardsHtml(string $selected): string
    {
        $cards = [];
        foreach (PageTemplates::definitions() as $def) {
            $id = $def['id'];
            $category = $def['category'];
            $isSelected = $id === $selected;
            $cards[] = '<button type="button" class="dev-page-template-card' . ($isSelected ? ' is-selected' : '') . '"'
                . ' data-page-template="' . htmlspecialchars($id, ENT_QUOTES) . '"'
                . ' data-page-template-category="' . htmlspecialchars($category, ENT_QUOTES) . '"'
                . ' role="option" aria-selected="' . ($isSelected ? 'true' : 'false') . '">'
                . '<span class="dev-block-card__icon" aria-hidden="true"><i class="' . htmlspecialchars($def['icon'], ENT_QUOTES) . '"></i></span>'
                . '<span class="dev-block-card__title">' . htmlspecialchars($def['label'], ENT_QUOTES) . '</span>'
                . '<span class="dev-block-card__desc">' . htmlspecialchars($def['description'], ENT_QUOTES) . '</span>'
                . '</button>';
        }

        return implode('', $cards);
    }
}

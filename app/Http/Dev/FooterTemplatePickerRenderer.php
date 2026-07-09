<?php

declare(strict_types=1);

namespace App\Http\Dev;

/**
 * Sélecteur de modèle pour la création d'une variante de pied de page.
 */
final class FooterTemplatePickerRenderer
{
    public function renderPickerHtml(string $selected = 'default'): string
    {
        $cards = [];
        foreach (FooterTemplates::definitions() as $def) {
            $id = $def['id'];
            $isSelected = $id === $selected;
            $cards[] = '<button type="button" class="dev-page-template-card dev-footer-template-card'
                . ($isSelected ? ' is-selected' : '') . '"'
                . ' data-footer-template="' . htmlspecialchars($id, ENT_QUOTES) . '"'
                . ' role="option" aria-selected="' . ($isSelected ? 'true' : 'false') . '">'
                . '<span class="dev-block-card__icon" aria-hidden="true"><i class="' . htmlspecialchars($def['icon'], ENT_QUOTES) . '"></i></span>'
                . '<span class="dev-block-card__title">' . htmlspecialchars($def['label'], ENT_QUOTES) . '</span>'
                . '<span class="dev-block-card__desc">' . htmlspecialchars($def['description'], ENT_QUOTES) . '</span>'
                . '</button>';
        }

        return '<div class="dev-page-template-grid dev-footer-template-grid" id="dev-footer-template-grid" role="listbox" aria-label="Modèles de pied de page">'
            . implode('', $cards)
            . '</div>';
    }
}

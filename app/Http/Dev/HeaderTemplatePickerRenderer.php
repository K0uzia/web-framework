<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\HeaderStyle;

/**
 * Sélecteur de modèle pour la création d'une variante d'en-tête.
 */
final class HeaderTemplatePickerRenderer
{
    public function renderPickerHtml(string $selected = 'default'): string
    {
        $cards = [];
        foreach (HeaderTemplates::definitions() as $def) {
            $id = $def['id'];
            $isSelected = $id === $selected;
            $cards[] = '<button type="button" class="dev-page-template-card dev-header-template-card'
                . ($isSelected ? ' is-selected' : '') . '"'
                . ' data-header-template="' . htmlspecialchars($id, ENT_QUOTES) . '"'
                . ' role="option" aria-selected="' . ($isSelected ? 'true' : 'false') . '">'
                . '<span class="dev-block-card__icon" aria-hidden="true"><i class="' . htmlspecialchars($def['icon'], ENT_QUOTES) . '"></i></span>'
                . '<span class="dev-block-card__title">' . htmlspecialchars($def['label'], ENT_QUOTES) . '</span>'
                . '<span class="dev-block-card__desc">' . htmlspecialchars($def['description'], ENT_QUOTES) . '</span>'
                . '</button>';
        }

        return '<div class="dev-page-template-grid dev-header-template-grid" id="dev-header-template-grid" role="listbox" aria-label="Modèles d\'en-tête">'
            . implode('', $cards)
            . '</div>';
    }
}

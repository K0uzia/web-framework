<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\LoginBlockResolver;

/**
 * Sélecteur de blocs de connexion du site (login1, login2).
 */
final class LoginBlockPicker
{
    /**
     * @param array<string, mixed> $site
     */
    public static function render(
        string $selectId,
        string $selectName,
        string $currentValue,
        array $site,
        string $formAttr = '',
    ): string {
        $safeId = htmlspecialchars($selectId, ENT_QUOTES);
        $safeName = htmlspecialchars($selectName, ENT_QUOTES);
        $formAttribute = $formAttr !== '' ? ' form="' . htmlspecialchars($formAttr, ENT_QUOTES) . '"' : '';

        return '<div class="dev-field" data-login-block-picker>'
            . '<label class="dev-label" for="' . $safeId . '">Bloc de connexion</label>'
            . '<select class="dev-input dev-select" id="' . $safeId . '" name="' . $safeName . '"'
            . $formAttribute
            . ' data-login-block-select>'
            . LoginBlockResolver::buildSelectOptions($site, $currentValue)
            . '</select>'
            . '<span class="dev-hint">Ces blocs ne sont pas ajoutés aux pages. Ils servent au bouton de connexion de l\'en-tête.</span>'
            . '</div>';
    }

    /**
     * @return array<string, string>
     */
    public static function displayOptions(string $current): string
    {
        $options = [
            'page' => 'Page dédiée',
            'modal' => 'Modale',
        ];
        $html = '';
        foreach ($options as $value => $label) {
            $selected = $value === $current ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($value, ENT_QUOTES) . '"' . $selected . '>'
                . htmlspecialchars($label, ENT_QUOTES) . '</option>';
        }

        return $html;
    }
}

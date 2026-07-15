<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections;

use Capsule\ShaderLibrary;

/**
 * Galerie de presets shader pour les fonds animés.
 */
final class SectionShaderFieldView
{
    /**
     * @param array<string, mixed> $content
     */
    public static function render(string $sectionId, array $content): string
    {
        $currentId = ShaderLibrary::normalizeId((string) ($content['background_shader_id'] ?? ''));
        $color = ShaderLibrary::normalizeColor(
            (string) ($content['background_shader_color'] ?? ''),
            ShaderLibrary::colorFor($currentId),
        );
        $fieldId = (preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) ?? $sectionId) . '-shader';

        $items = [];
        foreach (ShaderLibrary::presets() as $preset) {
            $id = (string) ($preset['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $selected = $id === $currentId ? ' dev-shader-library__pick--selected' : '';
            $name = htmlspecialchars((string) ($preset['name'] ?? $id), ENT_QUOTES);
            $preview = htmlspecialchars((string) ($preset['preview'] ?? ''), ENT_QUOTES);
            $presetColor = htmlspecialchars((string) ($preset['color'] ?? '#bbffcc'), ENT_QUOTES);
            $items[] = '<button type="button" class="dev-shader-library__pick' . $selected . '" data-dev-shader-pick data-shader-id="'
                . htmlspecialchars($id, ENT_QUOTES) . '" data-shader-color="' . $presetColor
                . '" title="' . $name . '" aria-label="Utiliser ' . $name . '" style="background:' . $preview . ';">'
                . '<span class="dev-shader-library__pick-label">' . $name . '</span></button>';
        }

        return '<div class="dev-shader-field" data-dev-shader-field>'
            . '<input type="hidden" name="content_background_shader_id" id="' . htmlspecialchars($fieldId . '-id', ENT_QUOTES) . '" value="'
            . htmlspecialchars($currentId, ENT_QUOTES) . '" data-dev-shader-id-input />'
            . '<p class="dev-label">Shader</p>'
            . '<p class="dev-hint">Animation WebGL inspirée du bloc shader3. Choisissez un preset puis ajustez la couleur accent.</p>'
            . '<div class="dev-shader-library__grid" role="listbox" aria-label="Presets shader">' . implode('', $items) . '</div>'
            . '<div class="dev-field dev-shader-field__color">'
            . '<label class="dev-label" for="' . htmlspecialchars($fieldId . '-color', ENT_QUOTES) . '">Couleur accent</label>'
            . '<input class="dev-input" type="color" id="' . htmlspecialchars($fieldId . '-color', ENT_QUOTES)
            . '" name="content_background_shader_color" value="' . htmlspecialchars($color, ENT_QUOTES)
            . '" data-dev-shader-color-input />'
            . '</div>'
            . '</div>';
    }
}

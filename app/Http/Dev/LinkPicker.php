<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\Page;
use Capsule\PageRepository;

/**
 * Sélecteur de cible réutilisable (page publiée, section ancrée d'une page, ou URL libre).
 *
 * Rend un <select> qui recopie le chemin (ou l'ancre) choisi dans le champ
 * texte associé ; ce champ reste modifiable pour saisir un lien externe.
 */
final class LinkPicker
{
    /** @var array<string, string> */
    private const TYPE_LABELS = [
        'hero' => 'Hero',
        'features' => 'Fonctionnalités',
        'cta' => "Appel à l'action",
    ];

    public static function render(
        string $inputId,
        string $inputName,
        string $currentValue,
        PageRepository $pages,
        string $formAttr = '',
    ): string {
        $safeId = htmlspecialchars($inputId, ENT_QUOTES);
        $safeName = htmlspecialchars($inputName, ENT_QUOTES);
        $formAttribute = $formAttr !== '' ? ' form="' . htmlspecialchars($formAttr, ENT_QUOTES) . '"' : '';

        $options = ['<option value="">URL personnalisée…</option>'];
        foreach ($pages->allPublished() as $page) {
            $path = $page->routePath();
            $label = $page->slug === '' ? 'Accueil' : ($path . ' : ' . $page->title);
            $options[] = '<option value="' . htmlspecialchars($path, ENT_QUOTES) . '">'
                . htmlspecialchars($label, ENT_QUOTES) . '</option>';

            $sectionOptions = self::sectionOptions($page);
            if ($sectionOptions !== '') {
                $options[] = '<optgroup label="' . htmlspecialchars('Sections : ' . $label, ENT_QUOTES) . '">'
                    . $sectionOptions . '</optgroup>';
            }
        }

        return '<div class="dev-link-picker" data-link-picker>'
            . '<select class="dev-input dev-select" aria-label="Choisir une page ou une section du site" data-link-picker-select>'
            . implode('', $options)
            . '</select>'
            . '<input class="dev-input" type="text" id="' . $safeId . '" name="' . $safeName . '" value="'
            . htmlspecialchars($currentValue, ENT_QUOTES) . '" placeholder="https://exemple.fr ou /page"'
            . $formAttribute . ' data-link-picker-input />'
            . '</div>';
    }

    private static function sectionOptions(Page $page): string
    {
        $basePath = $page->routePath();
        $anchorBase = $basePath === '/' ? '' : $basePath;

        $options = [];
        foreach ($page->sections as $section) {
            if (!is_array($section)) {
                continue;
            }
            $id = is_string($section['id'] ?? null) ? $section['id'] : '';
            if ($id === '') {
                continue;
            }
            if (($section['visible'] ?? true) === false) {
                continue;
            }

            $type = is_string($section['type'] ?? null) ? $section['type'] : '';
            $content = is_array($section['content'] ?? null) ? $section['content'] : [];
            $title = is_string($content['title'] ?? null) ? trim($content['title']) : '';
            $label = $title !== '' ? $title : (self::TYPE_LABELS[$type] ?? ucfirst($type !== '' ? $type : $id));

            $anchor = $anchorBase . '#' . $id;
            $options[] = '<option value="' . htmlspecialchars($anchor, ENT_QUOTES) . '">'
                . htmlspecialchars($label, ENT_QUOTES) . '</option>';
        }

        return implode('', $options);
    }
}

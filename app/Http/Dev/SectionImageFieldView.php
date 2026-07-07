<?php

declare(strict_types=1);

namespace App\Http\Dev;

/**
 * Champ image de bloc : import, aperçu, bibliothèque des visuels du site.
 */
final class SectionImageFieldView
{
    private const RASTER_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp', 'gif'];

    /**
     * @param list<string> $libraryUrls
     */
    public static function render(
        string $slug,
        string $sectionId,
        string $fieldName,
        string $url,
        array $libraryUrls,
        string $accept,
        string $error = '',
    ): string {
        $safeSlug = rawurlencode($slug);
        $safeSectionId = rawurlencode($sectionId);
        $inputName = 'content_' . $fieldName;
        $fieldId = preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) ?? $sectionId;
        $fieldId .= '-' . $fieldName;
        $baseAction = '/dev/pages/' . $safeSlug . '/sections/' . $safeSectionId . '/image';

        if ($url !== '') {
            $safeUrl = htmlspecialchars($url, ENT_QUOTES);
            $extension = strtolower((string) pathinfo($url, PATHINFO_EXTENSION));
            $thumb = in_array($extension, self::RASTER_EXTENSIONS, true) || $extension === 'svg'
                ? '<img src="' . $safeUrl . '" alt="" />'
                : '<i class="fa-solid fa-image" aria-hidden="true"></i>';

            $preview = '<div class="dev-uploader__preview">'
                . '<div class="dev-uploader__thumb">' . $thumb . '</div>'
                . '<span class="dev-uploader__name">' . htmlspecialchars(basename($url), ENT_QUOTES) . '</span>'
                . '<button type="button" class="dev-icon-btn dev-icon-btn--danger" data-dev-section-image-remove aria-label="Retirer cette image" title="Retirer">'
                . '<i class="fa-solid fa-trash" aria-hidden="true"></i></button>'
                . '</div>';
        } else {
            $preview = '<div class="dev-uploader__empty"><i class="fa-solid fa-image" aria-hidden="true"></i> Aucune image</div>';
        }

        $errorHtml = $error !== ''
            ? '<p class="dev-uploader__error">' . htmlspecialchars($error, ENT_QUOTES) . '</p>'
            : '';

        $libraryHtml = self::renderLibrary($libraryUrls, $url);

        $safeInputId = htmlspecialchars($fieldId . '-input', ENT_QUOTES);

        return '<div class="dev-section-image" id="section-image-' . htmlspecialchars($fieldId, ENT_QUOTES) . '" data-dev-section-image data-section-image-base="' . htmlspecialchars($baseAction, ENT_QUOTES) . '">'
            . '<div class="dev-field">'
            . '<label class="dev-label" for="' . $safeInputId . '">Image</label>'
            . '<input class="dev-input" type="text" id="' . $safeInputId . '" name="' . htmlspecialchars($inputName, ENT_QUOTES) . '" value="'
            . htmlspecialchars($url, ENT_QUOTES) . '" placeholder="/uploads/site/mon-image.webp" />'
            . '</div>'
            . '<div class="dev-uploader dev-uploader--section">'
            . $preview
            . '<label class="dev-button dev-button--ghost dev-button--sm dev-uploader__browse">'
            . '<i class="fa-solid fa-upload" aria-hidden="true"></i> ' . ($url !== '' ? 'Remplacer' : 'Importer une image')
            . '<input type="file" accept="' . htmlspecialchars($accept, ENT_QUOTES) . '" class="visually-hidden" data-dev-section-image-file aria-label="Importer une image" />'
            . '</label>'
            . $errorHtml
            . '</div>'
            . $libraryHtml
            . '</div>';
    }

    /**
     * @param list<string> $libraryUrls
     */
    private static function renderLibrary(array $libraryUrls, string $currentUrl): string
    {
        if ($libraryUrls === []) {
            return '<p class="dev-hint">Importez une image pour l\'ajouter à la bibliothèque du site.</p>';
        }

        $items = [];
        foreach ($libraryUrls as $url) {
            $safeUrl = htmlspecialchars($url, ENT_QUOTES);
            $label = htmlspecialchars(basename($url), ENT_QUOTES);
            $selected = $url === $currentUrl ? ' dev-media-library__pick--selected' : '';
            $extension = strtolower((string) pathinfo($url, PATHINFO_EXTENSION));
            $thumb = in_array($extension, self::RASTER_EXTENSIONS, true) || $extension === 'svg'
                ? '<img src="' . $safeUrl . '" alt="" loading="lazy" decoding="async" />'
                : '<i class="fa-solid fa-image" aria-hidden="true"></i>';

            $items[] = '<button type="button" class="dev-media-library__pick' . $selected . '" data-dev-section-image-select data-url="' . $safeUrl . '" title="' . $label . '" aria-label="Utiliser ' . $label . '">'
                . $thumb
                . '</button>';
        }

        return '<div class="dev-media-library">'
            . '<p class="dev-label dev-media-library__title">Bibliothèque du site</p>'
            . '<div class="dev-media-library__grid">' . implode('', $items) . '</div>'
            . '</div>';
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Dev;

/**
 * Construit le fragment HTML de l'uploader d'un visuel du site (logo, favicon, image de partage).
 */
final class MediaFieldView
{
    private const RASTER_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp', 'gif'];

    /**
     * @param list<string> $libraryUrls
     */
    public static function render(string $field, string $url, string $accept, array $libraryUrls, string $error = ''): string
    {
        $safeField = htmlspecialchars($field, ENT_QUOTES);

        if ($url !== '') {
            $safeUrl = htmlspecialchars($url, ENT_QUOTES);
            $extension = strtolower((string) pathinfo($url, PATHINFO_EXTENSION));
            $thumb = in_array($extension, self::RASTER_EXTENSIONS, true) || $extension === 'svg'
                ? '<img src="' . $safeUrl . '" alt="" />'
                : '<i class="fa-solid fa-image" aria-hidden="true"></i>';

            $preview = '<div class="dev-uploader__preview">'
                . '<div class="dev-uploader__thumb">' . $thumb . '</div>'
                . '<span class="dev-uploader__name">' . htmlspecialchars(basename($url), ENT_QUOTES) . '</span>'
                . '<form class="dev-inline-form" method="post" action="/dev/media/' . $safeField . '/remove" data-dev-ajax="media-' . $safeField . '" data-dev-toast-form="Fichier supprimé">'
                . '<button type="submit" class="dev-icon-btn dev-icon-btn--danger" aria-label="Supprimer ce fichier" title="Supprimer">'
                . '<i class="fa-solid fa-trash" aria-hidden="true"></i></button></form>'
                . '</div>';
        } else {
            $preview = '<div class="dev-uploader__empty"><i class="fa-solid fa-image" aria-hidden="true"></i> Aucun fichier</div>';
        }

        $errorHtml = $error !== ''
            ? '<p class="dev-uploader__error">' . htmlspecialchars($error, ENT_QUOTES) . '</p>'
            : '';

        return '<div class="dev-uploader dev-site-media-field" id="uploader-' . $safeField . '" data-dev-site-media-field data-site-media-field="' . $safeField . '">'
            . $preview
            . '<form class="dev-inline-form" method="post" action="/dev/media/' . $safeField . '/upload" enctype="multipart/form-data" data-dev-ajax="media-' . $safeField . '" data-dev-toast-form="Fichier importé">'
            . '<label class="dev-button dev-button--ghost dev-button--sm dev-uploader__browse">'
            . '<i class="fa-solid fa-upload" aria-hidden="true"></i> ' . ($url !== '' ? 'Remplacer' : 'Importer un fichier')
            . '<input type="file" name="file" accept="' . htmlspecialchars($accept, ENT_QUOTES) . '" class="visually-hidden" data-dev-autosubmit aria-label="Importer un fichier" />'
            . '</label></form>'
            . $errorHtml
            . self::renderLibrary($libraryUrls, $url, $field)
            . '</div>';
    }

    /**
     * @param list<string> $libraryUrls
     */
    private static function renderLibrary(array $libraryUrls, string $currentUrl, string $field): string
    {
        if ($libraryUrls === []) {
            return '<p class="dev-hint">Aucune image disponible. Importez un fichier ou ajoutez des visuels dans <a href="/dev/medias">Médias</a>.</p>';
        }

        $safeField = htmlspecialchars($field, ENT_QUOTES);
        $items = [];
        foreach (array_slice($libraryUrls, 0, 24) as $libraryUrl) {
            $safeUrl = htmlspecialchars($libraryUrl, ENT_QUOTES);
            $label = htmlspecialchars(basename($libraryUrl), ENT_QUOTES);
            $selected = $libraryUrl === $currentUrl ? ' dev-media-library__pick--selected' : '';
            $extension = strtolower((string) pathinfo($libraryUrl, PATHINFO_EXTENSION));
            if (in_array($extension, self::RASTER_EXTENSIONS, true) || $extension === 'svg') {
                $thumb = '<img src="' . $safeUrl . '" alt="" loading="lazy" decoding="async" />';
            } else {
                $thumb = '<i class="fa-solid fa-image" aria-hidden="true"></i>';
            }
            $items[] = '<button type="button" class="dev-media-library__pick' . $selected . '" data-dev-site-media-select data-field="' . $safeField . '" data-url="' . $safeUrl . '" title="' . $label . '" aria-label="Utiliser ' . $label . '">' . $thumb . '</button>';
        }

        $more = count($libraryUrls) > 24
            ? '<button type="button" class="dev-media-library__more dev-button dev-button--ghost dev-button--sm" data-dev-media-library-open>Voir toute la bibliothèque (' . count($libraryUrls) . ')</button>'
            : '';

        $urlsAttr = htmlspecialchars(json_encode(array_values($libraryUrls), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES);

        return '<div class="dev-media-library" data-media-library-urls="' . $urlsAttr . '" data-media-library-kind="image" data-media-library-current="' . htmlspecialchars($currentUrl, ENT_QUOTES) . '"><p class="dev-label dev-media-library__title">Bibliothèque</p><div class="dev-media-library__grid">' . implode('', $items) . '</div>' . $more . '</div>';
    }
}

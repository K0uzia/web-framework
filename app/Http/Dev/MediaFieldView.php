<?php

declare(strict_types=1);

namespace App\Http\Dev;

/**
 * Construit le fragment HTML de l'uploader d'un visuel du site (logo, favicon, image de partage).
 */
final class MediaFieldView
{
    private const RASTER_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp', 'gif'];

    public static function render(string $field, string $url, string $accept, string $error = ''): string
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

        return '<div class="dev-uploader" id="uploader-' . $safeField . '">'
            . $preview
            . '<form class="dev-inline-form" method="post" action="/dev/media/' . $safeField . '/upload" enctype="multipart/form-data" data-dev-ajax="media-' . $safeField . '" data-dev-toast-form="Fichier importé">'
            . '<label class="dev-button dev-button--ghost dev-button--sm dev-uploader__browse">'
            . '<i class="fa-solid fa-upload" aria-hidden="true"></i> ' . ($url !== '' ? 'Remplacer' : 'Importer un fichier')
            . '<input type="file" name="file" accept="' . htmlspecialchars($accept, ENT_QUOTES) . '" class="visually-hidden" data-dev-autosubmit aria-label="Importer un fichier" />'
            . '</label></form>'
            . $errorHtml
            . '</div>';
    }
}

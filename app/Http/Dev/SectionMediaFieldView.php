<?php

declare(strict_types=1);

namespace App\Http\Dev;

/**
 * Champ média de bloc (image ou vidéo) : import, aperçu, bibliothèque.
 */
final class SectionMediaFieldView
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
        string $kind,
        array $libraryUrls,
        string $accept,
        string $error = '',
    ): string {
        return self::renderInternal($slug, $sectionId, $fieldName, $url, $kind, $libraryUrls, $accept, false, $error);
    }

    /**
     * @param list<string> $libraryUrls
     */
    public static function renderCompact(
        string $slug,
        string $sectionId,
        string $fieldName,
        string $url,
        string $kind,
        array $libraryUrls,
        string $accept,
    ): string {
        return self::renderInternal($slug, $sectionId, $fieldName, $url, $kind, $libraryUrls, $accept, true, '');
    }

    /**
     * @param list<string> $libraryUrls
     */
    private static function renderInternal(
        string $slug,
        string $sectionId,
        string $fieldName,
        string $url,
        string $kind,
        array $libraryUrls,
        string $accept,
        bool $compact,
        string $error,
    ): string {
        $safeSlug = rawurlencode($slug);
        $safeSectionId = rawurlencode($sectionId);
        $safeField = htmlspecialchars($fieldName, ENT_QUOTES);
        $inputName = 'content_' . $fieldName;
        $fieldId = preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) ?? $sectionId;
        $fieldId .= '-' . $fieldName;
        $baseAction = '/dev/pages/' . $safeSlug . '/sections/' . $safeSectionId . '/media/' . rawurlencode($fieldName);
        $label = $kind === 'video' ? 'Vidéo' : 'Image';
        $placeholder = $kind === 'video'
            ? 'https://www.youtube.com/watch?v=... ou /uploads/media/video-abc.mp4'
            : '/uploads/media/image-abc.webp';

        $preview = self::renderPreview($url, $kind);
        $errorHtml = $error !== ''
            ? '<p class="dev-uploader__error">' . htmlspecialchars($error, ENT_QUOTES) . '</p>'
            : '';
        $libraryHtml = self::renderLibrary($libraryUrls, $url, $kind, $compact);
        $safeInputId = htmlspecialchars($fieldId . '-input', ENT_QUOTES);
        $compactClass = $compact ? ' dev-section-media--compact' : '';

        $uploadHtml = $compact
            ? '<a class="dev-button dev-button--ghost dev-button--sm" href="/dev/medias"><i class="fa-solid fa-photo-film" aria-hidden="true"></i> Gérer les médias</a>'
            : '<label class="dev-button dev-button--ghost dev-button--sm dev-uploader__browse">'
                . '<i class="fa-solid fa-upload" aria-hidden="true"></i> ' . ($url !== '' ? 'Remplacer' : 'Importer')
                . '<input type="file" accept="' . htmlspecialchars($accept, ENT_QUOTES) . '" class="visually-hidden" data-dev-section-media-file aria-label="Importer un fichier" />'
                . '</label>';

        return '<div class="dev-section-media' . $compactClass . '" id="section-media-' . htmlspecialchars($fieldId, ENT_QUOTES) . '" data-dev-section-media data-section-media-base="' . htmlspecialchars($baseAction, ENT_QUOTES) . '" data-section-media-kind="' . htmlspecialchars($kind, ENT_QUOTES) . '">'
            . '<div class="dev-field">'
            . '<label class="dev-label" for="' . $safeInputId . '">' . htmlspecialchars($label, ENT_QUOTES) . '</label>'
            . '<input class="dev-input" type="text" id="' . $safeInputId . '" name="' . htmlspecialchars($inputName, ENT_QUOTES) . '" value="'
            . htmlspecialchars($url, ENT_QUOTES) . '" placeholder="' . htmlspecialchars($placeholder, ENT_QUOTES) . '" />'
            . ($kind === 'video' && !$compact ? '<p class="dev-hint">YouTube, Vimeo ou fichier .mp4 local importé dans la bibliothèque.</p>' : '')
            . '</div>'
            . '<div class="dev-uploader dev-uploader--section">'
            . $preview
            . $uploadHtml
            . $errorHtml
            . '</div>'
            . $libraryHtml
            . '</div>';
    }

    private static function renderPreview(string $url, string $kind): string
    {
        if ($url === '') {
            $icon = $kind === 'video' ? 'fa-video' : 'fa-image';
            $label = $kind === 'video' ? 'Aucune vidéo' : 'Aucune image';

            return '<div class="dev-uploader__empty"><i class="fa-solid ' . $icon . '" aria-hidden="true"></i> ' . $label . '</div>';
        }

        $safeUrl = htmlspecialchars($url, ENT_QUOTES);
        if ($kind === 'video' && preg_match('~^https?://~i', $url) === 1) {
            return '<div class="dev-uploader__preview dev-uploader__preview--video-url">'
                . '<div class="dev-uploader__thumb"><i class="fa-brands fa-youtube" aria-hidden="true"></i></div>'
                . '<span class="dev-uploader__name">' . htmlspecialchars($url, ENT_QUOTES) . '</span>'
                . '<button type="button" class="dev-icon-btn dev-icon-btn--danger" data-dev-section-media-remove aria-label="Retirer cette vidéo" title="Retirer">'
                . '<i class="fa-solid fa-trash" aria-hidden="true"></i></button>'
                . '</div>';
        }

        $extension = strtolower((string) pathinfo($url, PATHINFO_EXTENSION));
        if ($kind === 'video') {
            $thumb = '<video src="' . $safeUrl . '" muted playsinline preload="metadata"></video>';
        } elseif (in_array($extension, self::RASTER_EXTENSIONS, true) || $extension === 'svg') {
            $thumb = '<img src="' . $safeUrl . '" alt="" />';
        } else {
            $thumb = '<i class="fa-solid fa-image" aria-hidden="true"></i>';
        }

        return '<div class="dev-uploader__preview">'
            . '<div class="dev-uploader__thumb">' . $thumb . '</div>'
            . '<span class="dev-uploader__name">' . htmlspecialchars(basename($url), ENT_QUOTES) . '</span>'
            . '<button type="button" class="dev-icon-btn dev-icon-btn--danger" data-dev-section-media-remove aria-label="Retirer ce fichier" title="Retirer">'
            . '<i class="fa-solid fa-trash" aria-hidden="true"></i></button>'
            . '</div>';
    }

    /**
     * @param list<string> $libraryUrls
     */
    private static function renderLibrary(array $libraryUrls, string $currentUrl, string $kind, bool $compact): string
    {
        if ($libraryUrls === []) {
            return '<p class="dev-hint">Aucun média en bibliothèque. <a href="/dev/medias">Importer des ' . ($kind === 'video' ? 'vidéos' : 'images') . '</a>.</p>';
        }

        $limit = $compact ? 8 : 24;
        $visible = array_slice($libraryUrls, 0, $limit);
        $items = [];
        foreach ($visible as $url) {
            $safeUrl = htmlspecialchars($url, ENT_QUOTES);
            $label = htmlspecialchars(basename($url), ENT_QUOTES);
            $selected = $url === $currentUrl ? ' dev-media-library__pick--selected' : '';
            $extension = strtolower((string) pathinfo($url, PATHINFO_EXTENSION));
            if ($kind === 'video') {
                $thumb = '<i class="fa-solid fa-file-video" aria-hidden="true"></i>';
            } elseif (in_array($extension, self::RASTER_EXTENSIONS, true) || $extension === 'svg') {
                $thumb = '<img src="' . $safeUrl . '" alt="" loading="lazy" decoding="async" />';
            } else {
                $thumb = '<i class="fa-solid fa-image" aria-hidden="true"></i>';
            }

            $items[] = '<button type="button" class="dev-media-library__pick' . $selected . '" data-dev-section-media-select data-url="' . $safeUrl . '" title="' . $label . '" aria-label="Utiliser ' . $label . '">'
                . $thumb
                . '</button>';
        }

        $more = count($libraryUrls) > $limit
            ? '<a class="dev-hint dev-media-library__more" href="/dev/medias">Voir toute la bibliothèque (' . count($libraryUrls) . ')</a>'
            : '';

        return '<div class="dev-media-library">'
            . '<p class="dev-label dev-media-library__title">Bibliothèque</p>'
            . '<div class="dev-media-library__grid">' . implode('', $items) . '</div>'
            . $more
            . '</div>';
    }
}

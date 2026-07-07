<?php

declare(strict_types=1);

namespace App\Http\Dev;

final class MediaUploadException extends \RuntimeException
{
}

/**
 * Gère l'upload et l'optimisation des visuels du site (logo, favicon, image de partage).
 *
 * Convertit automatiquement les images matricielles en WebP quand l'extension GD
 * est disponible (comportement standard des hébergements PHP en production).
 * Le favicon est conservé dans son format d'origine (.ico, .png ou .svg).
 */
class MediaUploader
{
    private const MAX_BYTES = 5 * 1024 * 1024;

    /** @var array<string, list<string>> */
    private const ALLOWED_MIME = [
        'logo' => ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'],
        'og_image' => ['image/png', 'image/jpeg', 'image/webp'],
        'favicon' => ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/svg+xml'],
        'section_image' => ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'],
        'library_image' => ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'],
        'library_video' => ['video/mp4', 'video/webm', 'video/ogg'],
    ];

    /** @var array<string, string> */
    private const EXTENSION_BY_MIME = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'image/x-icon' => 'ico',
        'image/vnd.microsoft.icon' => 'ico',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/ogg' => 'ogg',
    ];

    /** @var array<string, bool> convertible vers webp */
    private const CONVERTIBLE = [
        'image/png' => true,
        'image/jpeg' => true,
    ];

    public function __construct(
        private readonly string $uploadsDir,
        private readonly string $publicBasePath = '/uploads/site',
    ) {
    }

    /**
     * @param array{name?: string, type?: string, tmp_name?: string, error?: int, size?: int} $file
     *
     * @return string URL publique du fichier stocké
     */
    public function store(string $field, array $file): string
    {
        $allowed = self::ALLOWED_MIME[$field] ?? null;
        if ($allowed === null) {
            throw new MediaUploadException('Champ de média inconnu.');
        }

        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new MediaUploadException('Aucun fichier reçu.');
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new MediaUploadException('Échec du transfert du fichier.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new MediaUploadException('Fichier invalide.');
        }

        $size = (int) ($file['size'] ?? 0);
        $maxBytes = $field === 'library_video' ? 50 * 1024 * 1024 : self::MAX_BYTES;
        if ($size <= 0 || $size > $maxBytes) {
            throw new MediaUploadException('Le fichier dépasse la taille maximale autorisée (' . (int) ($maxBytes / 1024 / 1024) . ' Mo).');
        }

        $mime = $this->detectMime($tmpName);
        if (!in_array($mime, $allowed, true)) {
            throw new MediaUploadException('Format de fichier non pris en charge pour ce champ.');
        }

        $extension = self::EXTENSION_BY_MIME[$mime] ?? match (true) {
            str_starts_with($mime, 'video/') => substr($mime, 6),
            default => 'bin',
        };
        $shouldConvertToWebp = $field !== 'favicon'
            && $field !== 'library_video'
            && ($this::CONVERTIBLE[$mime] ?? false)
            && $this->webpSupportAvailable();

        if (!is_dir($this->uploadsDir) && !mkdir($this->uploadsDir, 0775, true) && !is_dir($this->uploadsDir)) {
            throw new MediaUploadException('Impossible de créer le dossier de stockage.');
        }

        $filename = match ($field) {
            'section_image' => 'section-' . bin2hex(random_bytes(8)) . '.' . ($shouldConvertToWebp ? 'webp' : $extension),
            'library_image' => 'image-' . bin2hex(random_bytes(8)) . '.' . ($shouldConvertToWebp ? 'webp' : $extension),
            'library_video' => 'video-' . bin2hex(random_bytes(8)) . '.' . $extension,
            default => $field . '-' . bin2hex(random_bytes(8)) . '.' . ($shouldConvertToWebp ? 'webp' : $extension),
        };
        $destination = $this->uploadsDir . '/' . $filename;

        if ($shouldConvertToWebp) {
            $this->convertToWebp($tmpName, $destination, $mime);
        } elseif (!move_uploaded_file($tmpName, $destination)) {
            throw new MediaUploadException('Impossible d\'enregistrer le fichier.');
        }

        return rtrim($this->publicBasePath, '/') . '/' . $filename;
    }

    public function isManagedUrl(string $url): bool
    {
        $prefix = rtrim($this->publicBasePath, '/') . '/';

        return str_starts_with($url, $prefix);
    }

    /**
     * Supprime le fichier précédemment stocké si son URL pointe bien vers le dossier géré ici.
     */
    public function delete(string $url): void
    {
        $prefix = rtrim($this->publicBasePath, '/') . '/';
        if (!str_starts_with($url, $prefix)) {
            return;
        }

        $filename = basename($url);
        if ($filename === '' || str_contains($filename, '..')) {
            return;
        }

        $path = $this->uploadsDir . '/' . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public function webpSupportAvailable(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromstring');
    }

    public function acceptAttribute(string $field): string
    {
        return match ($field) {
            'logo' => '.png,.jpg,.jpeg,.webp,.svg,image/png,image/jpeg,image/webp,image/svg+xml',
            'og_image' => '.png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp',
            'section_image' => '.png,.jpg,.jpeg,.webp,.svg,image/png,image/jpeg,image/webp,image/svg+xml',
            'library_image' => '.png,.jpg,.jpeg,.webp,.svg,image/png,image/jpeg,image/webp,image/svg+xml',
            'library_video' => '.mp4,.webm,.ogg,video/mp4,video/webm,video/ogg',
            'favicon' => '.ico,.png,.svg,image/x-icon,image/png,image/svg+xml',
            default => implode(',', self::ALLOWED_MIME[$field] ?? []),
        };
    }

    private function detectMime(string $path): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = finfo_file($finfo, $path);
                finfo_close($finfo);
                if (is_string($mime) && $mime !== '') {
                    if ($mime === 'text/plain' || $mime === 'text/html' || $mime === 'application/xml') {
                        $head = (string) @file_get_contents($path, false, null, 0, 512);
                        if (str_contains($head, '<svg')) {
                            return 'image/svg+xml';
                        }
                    }

                    return $mime;
                }
            }
        }

        return 'application/octet-stream';
    }

    private function convertToWebp(string $sourcePath, string $destination, string $mime): void
    {
        $data = file_get_contents($sourcePath);
        $image = $data !== false ? @imagecreatefromstring($data) : false;
        if ($image === false) {
            throw new MediaUploadException('Impossible de lire l\'image envoyée.');
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        if (!imagewebp($image, $destination, 82)) {
            imagedestroy($image);

            throw new MediaUploadException('Échec de la conversion WebP.');
        }

        imagedestroy($image);
    }
}

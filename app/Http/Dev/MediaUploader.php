<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\ProcessRunner;

final class MediaUploadException extends \RuntimeException
{
}

/**
 * Gère l'upload et l'optimisation des visuels du site (logo, favicon, image de partage).
 *
 * Convertit automatiquement les images matricielles (PNG/JPEG) en WebP :
 * 1. via l'extension GD si disponible (cas typique en production) ;
 * 2. sinon via ffmpeg (déjà requis pour les imports vidéo).
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

    private readonly ProcessRunner $processes;

    public function __construct(
        private readonly string $uploadsDir,
        private readonly string $publicBasePath = '/uploads/site',
        ?ProcessRunner $processes = null,
        private readonly string $ffmpegBin = 'ffmpeg',
    ) {
        $this->processes = $processes ?? new ProcessRunner();
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
            && (self::CONVERTIBLE[$mime] ?? false)
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
            $this->convertToWebp($tmpName, $destination);
            @unlink($tmpName);
        } elseif (!move_uploaded_file($tmpName, $destination)) {
            throw new MediaUploadException('Impossible d\'enregistrer le fichier.');
        }

        return rtrim($this->publicBasePath, '/') . '/' . $filename;
    }

    /**
     * Métadonnées du fichier réellement stocké (après conversion éventuelle).
     *
     * @return array{mime: string, size: int}
     */
    public function storedFileMeta(string $publicUrl, string $fallbackMime = '', int $fallbackSize = 0): array
    {
        $filename = basename($publicUrl);
        $path = $this->uploadsDir . '/' . $filename;
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'webp' => 'image/webp',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
            default => $fallbackMime,
        };
        $size = is_file($path) ? (int) filesize($path) : $fallbackSize;

        return ['mime' => $mime, 'size' => $size];
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
        return $this->gdWebpAvailable() || $this->ffmpegAvailable();
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

    private function convertToWebp(string $sourcePath, string $destination): void
    {
        if ($this->gdWebpAvailable()) {
            try {
                $this->convertToWebpWithGd($sourcePath, $destination);

                return;
            } catch (MediaUploadException) {
                // Fallback ffmpeg si GD échoue sur un fichier particulier.
            }
        }

        if ($this->ffmpegAvailable()) {
            $this->convertToWebpWithFfmpeg($sourcePath, $destination);

            return;
        }

        throw new MediaUploadException('Échec de la conversion WebP (GD et ffmpeg indisponibles).');
    }

    private function convertToWebpWithGd(string $sourcePath, string $destination): void
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

    private function convertToWebpWithFfmpeg(string $sourcePath, string $destination): void
    {
        if (is_file($destination)) {
            @unlink($destination);
        }

        $result = $this->processes->run([
            $this->resolveFfmpegBin(),
            '-y',
            '-i', $sourcePath,
            '-c:v', 'libwebp',
            '-quality', '82',
            '-frames:v', '1',
            $destination,
        ], null, 120);

        if (!$result->successful() || !is_file($destination) || filesize($destination) === 0) {
            @unlink($destination);

            throw new MediaUploadException('Échec de la conversion WebP.');
        }
    }

    private function gdWebpAvailable(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromstring');
    }

    private function ffmpegAvailable(): bool
    {
        return $this->processes->isExecutable($this->resolveFfmpegBin());
    }

    private function resolveFfmpegBin(): string
    {
        $fromEnv = $_ENV['VIDEO_IMPORT_FFMPEG_BIN'] ?? getenv('VIDEO_IMPORT_FFMPEG_BIN');
        if (is_string($fromEnv) && $fromEnv !== '') {
            return $fromEnv;
        }

        return $this->ffmpegBin;
    }
}

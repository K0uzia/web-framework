<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\FontNameReader;

final class FontUploadException extends \RuntimeException
{
}

/**
 * Gère l'import de fichiers de police personnalisés (.woff2, .woff, .ttf, .otf),
 * hébergés localement, avec détection automatique du nom de famille quand le
 * format le permet.
 */
final class FontUploader
{
    private const MAX_BYTES = 5 * 1024 * 1024;

    /** @var array<string, string> */
    private const EXTENSION_BY_FORMAT = [
        'woff2' => 'woff2',
        'woff' => 'woff',
        'truetype' => 'ttf',
        'opentype' => 'otf',
    ];

    public function __construct(
        private readonly string $uploadsDir,
        private readonly string $publicBasePath = '/uploads/fonts',
    ) {
    }

    /**
     * @param array{name?: string, type?: string, tmp_name?: string, error?: int, size?: int} $file
     *
     * @return array{name: string, url: string, format: string}
     */
    public function store(array $file, string $customName = ''): array
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new FontUploadException('Aucun fichier reçu.');
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new FontUploadException('Échec du transfert du fichier.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new FontUploadException('Fichier invalide.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_BYTES) {
            throw new FontUploadException('Le fichier dépasse la taille maximale autorisée (5 Mo).');
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        $format = array_search($extension, self::EXTENSION_BY_FORMAT, true);
        if ($format === false) {
            throw new FontUploadException('Format non pris en charge. Utilisez .woff2, .woff, .ttf ou .otf.');
        }

        $binary = file_get_contents($tmpName);
        if ($binary === false) {
            throw new FontUploadException('Impossible de lire le fichier envoyé.');
        }

        $detected = FontNameReader::detectFamilyName($binary);
        $name = trim($customName) !== '' ? trim($customName) : $detected;
        if ($name === null || $name === '') {
            $name = $this->nameFromFilename($originalName);
        }

        if (!is_dir($this->uploadsDir) && !mkdir($this->uploadsDir, 0775, true) && !is_dir($this->uploadsDir)) {
            throw new FontUploadException('Impossible de créer le dossier de stockage.');
        }

        $filename = 'font-' . bin2hex(random_bytes(8)) . '.' . $extension;
        $destination = $this->uploadsDir . '/' . $filename;

        if (!move_uploaded_file($tmpName, $destination)) {
            throw new FontUploadException('Impossible d\'enregistrer le fichier.');
        }

        return [
            'name' => $name,
            'url' => rtrim($this->publicBasePath, '/') . '/' . $filename,
            'format' => (string) $format,
        ];
    }

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

    private function nameFromFilename(string $filename): string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $base = preg_replace('/[-_]+/', ' ', $base) ?? $base;
        $base = trim((string) preg_replace('/\s+/', ' ', $base));

        return $base !== '' ? ucwords($base) : 'Police personnalisée';
    }
}

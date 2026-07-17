<?php

declare(strict_types=1);

namespace App\Http\Dev;

/**
 * Uploads de la galerie client (/uploads/library), séparée de /uploads/media (dev).
 */
final class ClientMediaUploader extends MediaUploader
{
    public function __construct(string $uploadsDir)
    {
        parent::__construct($uploadsDir, '/uploads/library');
    }

    public function storeImage(array $file, string $label = ''): string
    {
        return $this->store('library_image', $file);
    }

    public function storeVideo(array $file, string $label = ''): string
    {
        return $this->store('library_video', $file);
    }
}

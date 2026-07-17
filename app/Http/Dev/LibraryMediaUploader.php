<?php

declare(strict_types=1);

namespace App\Http\Dev;

/**
 * Uploads vers la bibliothèque développeur (/uploads/media).
 */
final class LibraryMediaUploader extends MediaUploader
{
    public function __construct(string $uploadsDir)
    {
        parent::__construct($uploadsDir, '/uploads/media');
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

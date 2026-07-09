<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Supprime les fichiers et entrées liés à un import.
 */
final class VideoImportCleaner
{
    public function __construct(
        private readonly VideoImportConfig $config,
        private readonly MediaRepository $media,
    ) {
    }

    /**
     * @param array<string, mixed> $job
     */
    public function remove(array $job): void
    {
        $id = (string) $job['id'];
        $dir = $this->config->jobDir($id);
        if (is_dir($dir)) {
            $this->removeTree($dir);
        }

        $mediaId = (string) ($job['media_id'] ?? '');
        if ($mediaId !== '') {
            $this->media->delete($mediaId);
        }
    }

    private function removeTree(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->removeTree($path);
            } elseif (is_file($path)) {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}

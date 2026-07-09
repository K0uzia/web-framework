<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Lance le worker CLI en arrière-plan (développement).
 */
final class VideoImportWorkerDispatcher
{
    public function __construct(
        private readonly string $projectRoot,
        private readonly ProcessRunner $runner,
        private readonly bool $enabled,
    ) {
    }

    public function dispatchOnce(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $script = $this->projectRoot . '/bin/video-import-worker.php';
        if (!is_file($script)) {
            return false;
        }

        return $this->runner->startDetached([PHP_BINARY, $script, '--once'], $this->projectRoot);
    }
}

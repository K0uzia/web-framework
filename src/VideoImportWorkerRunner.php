<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Exécute un job de la file (partagé entre le worker CLI et l'API dev).
 */
final class VideoImportWorkerRunner
{
    public function __construct(
        private readonly VideoImportRepository $imports,
        private readonly VideoImportProcessor $processor,
    ) {
    }

    public function processNext(): bool
    {
        $job = $this->imports->claimNext();
        if ($job === null) {
            return false;
        }

        $id = (string) $job['id'];

        try {
            $current = $this->imports->findById($id);
            if ($current === null) {
                return true;
            }

            $this->processor->process($current);
        } catch (\Throwable $e) {
            $this->imports->markFailed($id, $e->getMessage());
        }

        return true;
    }

    public function processQueue(int $maxJobs = 1): int
    {
        $processed = 0;
        while ($processed < $maxJobs && $this->processNext()) {
            $processed++;
        }

        return $processed;
    }

    public function queuedCount(): int
    {
        return $this->imports->countByStatus('queued');
    }
}

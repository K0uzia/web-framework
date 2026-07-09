<?php

declare(strict_types=1);

namespace Capsule;

final class SiteExportResult
{
    /**
     * @param list<string> $writtenPaths
     */
    public function __construct(
        public readonly string $outputDir,
        public readonly int $pageCount,
        public readonly array $writtenPaths,
    ) {
    }
}

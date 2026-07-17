<?php

declare(strict_types=1);

namespace App\Http\Admin;

final class PageListRow
{
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly string $path,
        public readonly bool $published,
        public readonly string $updatedAt,
        public readonly string $authorLabel,
        public readonly string $editUrl,
        public readonly bool $isDemo = false,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Capsule;

final class Page
{
    /**
     * @param list<array<string, mixed>> $sections
     * @param array<string, mixed>       $meta
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly string $layout,
        public readonly string $description,
        public readonly array $sections,
        public readonly array $meta,
        public readonly bool $published,
        public readonly string $updatedAt,
    ) {
    }

    public function routePath(): string
    {
        return $this->slug === '' ? '/' : '/' . $this->slug;
    }
}

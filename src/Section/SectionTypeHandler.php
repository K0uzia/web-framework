<?php

declare(strict_types=1);

namespace Capsule\Section;

interface SectionTypeHandler
{
    public function type(): string;

    public function normalizeVariant(string $variant): string;

    /**
     * @param array<string, mixed> $style
     *
     * @return array<string, string>
     */
    public function resolveStyle(array $style, string $variant): array;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public function enrich(array $data, array $content, string $variant, SectionEnrichContext $context): array;

    /**
     * @return list<string>
     */
    public function cssFamilies(string $variant): array;

    /**
     * @return list<string>
     */
    public function cssModules(string $variant): array;

    /**
     * @return list<string>
     */
    public function jsModules(string $variant): array;
}

<?php

declare(strict_types=1);

namespace Capsule\Section;

/**
 * Contexte passé aux handlers pour l'enrichissement (boutons hero, etc.).
 */
final class SectionEnrichContext
{
    /**
     * @param callable(array<string, mixed>): string $renderHeroButtons
     */
    public function __construct(
        private readonly mixed $renderHeroButtons = null,
    ) {
    }

    /**
     * @param array<string, mixed> $content
     */
    public function renderHeroButtons(array $content): string
    {
        if (!is_callable($this->renderHeroButtons)) {
            return '';
        }

        return (string) ($this->renderHeroButtons)($content);
    }
}

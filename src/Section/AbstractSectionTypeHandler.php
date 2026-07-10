<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\SectionLayoutFamilies;

/**
 * Handler générique déléguant aux classes *Style et *VariantRenderer existantes.
 */
abstract class AbstractSectionTypeHandler implements SectionTypeHandler
{
    public const TYPE = '';

    /** @var class-string */
    protected string $styleClass = '';

    /** @var class-string */
    protected string $rendererClass = '';

    public function type(): string
    {
        return static::TYPE;
    }

    public function normalizeVariant(string $variant): string
    {
        $class = $this->styleClass;

        return $class::normalizeVariant($variant);
    }

    public function resolveStyle(array $style, string $variant): array
    {
        $class = $this->styleClass;

        return $class::resolve($style, $variant);
    }

    public function enrich(array $data, array $content, string $variant, SectionEnrichContext $context): array
    {
        $class = $this->rendererClass;

        return $class::enrich($data, $content, $variant);
    }

    public function cssFamilies(string $variant): array
    {
        return SectionLayoutFamilies::cssFamilies($variant);
    }

    public function jsModules(string $variant): array
    {
        return [];
    }
}

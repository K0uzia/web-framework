<?php

declare(strict_types=1);

namespace Capsule\Section\Feature;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Feature\FeatureStyle;
use Capsule\Section\Feature\FeatureVariantRenderer;

final class FeaturesSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'features';

    protected string $styleClass = FeatureStyle::class;
    protected string $rendererClass = FeatureVariantRenderer::class;

    public function jsModules(string $variant): array
    {
        return ['sections/features.js'];
    }
}

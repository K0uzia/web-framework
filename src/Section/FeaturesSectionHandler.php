<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\FeatureStyle;
use Capsule\FeatureVariantRenderer;

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

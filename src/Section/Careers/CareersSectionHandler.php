<?php

declare(strict_types=1);

namespace Capsule\Section\Careers;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Careers\CareersStyle;
use Capsule\Section\Careers\CareersVariantRenderer;

final class CareersSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'careers';

    protected string $styleClass = CareersStyle::class;
    protected string $rendererClass = CareersVariantRenderer::class;
}

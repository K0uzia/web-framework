<?php

declare(strict_types=1);

namespace Capsule\Section\Industry;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Industry\IndustryStyle;
use Capsule\Section\Industry\IndustryVariantRenderer;

final class IndustrySectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'industry';

    protected string $styleClass = IndustryStyle::class;
    protected string $rendererClass = IndustryVariantRenderer::class;
}

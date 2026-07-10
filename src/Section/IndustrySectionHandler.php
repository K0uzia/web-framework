<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\IndustryStyle;
use Capsule\IndustryVariantRenderer;

final class IndustrySectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'industry';

    protected string $styleClass = IndustryStyle::class;
    protected string $rendererClass = IndustryVariantRenderer::class;
}

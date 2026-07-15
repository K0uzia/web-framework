<?php

declare(strict_types=1);

namespace Capsule\Section\Timeline;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Timeline\TimelineStyle;
use Capsule\Section\Timeline\TimelineVariantRenderer;

final class TimelineSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'timeline';

    protected string $styleClass = TimelineStyle::class;
    protected string $rendererClass = TimelineVariantRenderer::class;
}

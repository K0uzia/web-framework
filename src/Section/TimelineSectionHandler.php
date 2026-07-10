<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\TimelineStyle;
use Capsule\TimelineVariantRenderer;

final class TimelineSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'timeline';

    protected string $styleClass = TimelineStyle::class;
    protected string $rendererClass = TimelineVariantRenderer::class;
}

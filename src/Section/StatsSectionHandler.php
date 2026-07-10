<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\StatsStyle;
use Capsule\StatsVariantRenderer;

final class StatsSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'stats';

    protected string $styleClass = StatsStyle::class;
    protected string $rendererClass = StatsVariantRenderer::class;
}

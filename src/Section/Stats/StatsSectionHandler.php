<?php

declare(strict_types=1);

namespace Capsule\Section\Stats;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Stats\StatsStyle;
use Capsule\Section\Stats\StatsVariantRenderer;

final class StatsSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'stats';

    protected string $styleClass = StatsStyle::class;
    protected string $rendererClass = StatsVariantRenderer::class;
}

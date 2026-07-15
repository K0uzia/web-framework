<?php

declare(strict_types=1);

namespace Capsule\Section\Team;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Team\TeamStyle;
use Capsule\Section\Team\TeamVariantRenderer;

final class TeamSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'team';

    protected string $styleClass = TeamStyle::class;
    protected string $rendererClass = TeamVariantRenderer::class;
}

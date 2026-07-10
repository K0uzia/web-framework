<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\TeamStyle;
use Capsule\TeamVariantRenderer;

final class TeamSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'team';

    protected string $styleClass = TeamStyle::class;
    protected string $rendererClass = TeamVariantRenderer::class;
}

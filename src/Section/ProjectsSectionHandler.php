<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\ProjectsStyle;
use Capsule\ProjectsVariantRenderer;

final class ProjectsSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'projects';

    protected string $styleClass = ProjectsStyle::class;
    protected string $rendererClass = ProjectsVariantRenderer::class;
}

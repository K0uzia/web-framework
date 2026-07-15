<?php

declare(strict_types=1);

namespace Capsule\Section\Projects;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Projects\ProjectsStyle;
use Capsule\Section\Projects\ProjectsVariantRenderer;

final class ProjectsSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'projects';

    protected string $styleClass = ProjectsStyle::class;
    protected string $rendererClass = ProjectsVariantRenderer::class;
}

<?php

declare(strict_types=1);

namespace Capsule\Section\Experience;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Experience\ExperienceStyle;
use Capsule\Section\Experience\ExperienceVariantRenderer;

final class ExperienceSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'experience';

    protected string $styleClass = ExperienceStyle::class;
    protected string $rendererClass = ExperienceVariantRenderer::class;
}

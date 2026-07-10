<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\ExperienceStyle;
use Capsule\ExperienceVariantRenderer;

final class ExperienceSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'experience';

    protected string $styleClass = ExperienceStyle::class;
    protected string $rendererClass = ExperienceVariantRenderer::class;
}

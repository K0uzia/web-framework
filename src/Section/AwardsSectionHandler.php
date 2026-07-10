<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\AwardsStyle;
use Capsule\AwardsVariantRenderer;

final class AwardsSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'awards';

    protected string $styleClass = AwardsStyle::class;
    protected string $rendererClass = AwardsVariantRenderer::class;
}

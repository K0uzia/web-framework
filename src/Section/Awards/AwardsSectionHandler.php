<?php

declare(strict_types=1);

namespace Capsule\Section\Awards;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Awards\AwardsStyle;
use Capsule\Section\Awards\AwardsVariantRenderer;

final class AwardsSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'awards';

    protected string $styleClass = AwardsStyle::class;
    protected string $rendererClass = AwardsVariantRenderer::class;
}

<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\CareersStyle;
use Capsule\CareersVariantRenderer;

final class CareersSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'careers';

    protected string $styleClass = CareersStyle::class;
    protected string $rendererClass = CareersVariantRenderer::class;
}

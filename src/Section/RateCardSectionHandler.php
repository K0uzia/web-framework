<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\RateCardStyle;
use Capsule\RateCardVariantRenderer;

final class RateCardSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'rate-card';

    protected string $styleClass = RateCardStyle::class;
    protected string $rendererClass = RateCardVariantRenderer::class;
}

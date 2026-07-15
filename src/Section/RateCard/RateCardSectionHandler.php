<?php

declare(strict_types=1);

namespace Capsule\Section\RateCard;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\RateCard\RateCardStyle;
use Capsule\Section\RateCard\RateCardVariantRenderer;

final class RateCardSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'rate-card';

    protected string $styleClass = RateCardStyle::class;
    protected string $rendererClass = RateCardVariantRenderer::class;
}

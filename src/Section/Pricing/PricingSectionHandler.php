<?php

declare(strict_types=1);

namespace Capsule\Section\Pricing;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Pricing\PricingStyle;
use Capsule\Section\Pricing\PricingVariantRenderer;

final class PricingSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'pricing';

    protected string $styleClass = PricingStyle::class;
    protected string $rendererClass = PricingVariantRenderer::class;

    public function jsModules(string $variant): array
    {
        return ['sections/pricing.js'];
    }
}

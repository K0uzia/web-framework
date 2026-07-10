<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\PricingStyle;
use Capsule\PricingVariantRenderer;

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

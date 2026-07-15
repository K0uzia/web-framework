<?php

declare(strict_types=1);

namespace Capsule\Section\Cta;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Cta\CtaStyle;
use Capsule\Section\Cta\CtaVariantRenderer;

final class CtaSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'cta';

    protected string $styleClass = CtaStyle::class;
    protected string $rendererClass = CtaVariantRenderer::class;
}

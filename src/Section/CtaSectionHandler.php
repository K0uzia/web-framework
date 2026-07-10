<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\CtaStyle;
use Capsule\CtaVariantRenderer;

final class CtaSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'cta';

    protected string $styleClass = CtaStyle::class;
    protected string $rendererClass = CtaVariantRenderer::class;
}

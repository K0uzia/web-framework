<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\FaqStyle;
use Capsule\FaqVariantRenderer;

final class FaqSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'faq';

    protected string $styleClass = FaqStyle::class;
    protected string $rendererClass = FaqVariantRenderer::class;
}

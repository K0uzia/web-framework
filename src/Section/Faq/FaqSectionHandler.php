<?php

declare(strict_types=1);

namespace Capsule\Section\Faq;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Faq\FaqStyle;
use Capsule\Section\Faq\FaqVariantRenderer;

final class FaqSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'faq';

    protected string $styleClass = FaqStyle::class;
    protected string $rendererClass = FaqVariantRenderer::class;
}

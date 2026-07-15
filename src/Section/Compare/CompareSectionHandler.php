<?php

declare(strict_types=1);

namespace Capsule\Section\Compare;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Compare\CompareStyle;
use Capsule\Section\Compare\CompareVariantRenderer;

final class CompareSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'compare';

    protected string $styleClass = CompareStyle::class;
    protected string $rendererClass = CompareVariantRenderer::class;
}

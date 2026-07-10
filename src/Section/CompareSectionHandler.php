<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\CompareStyle;
use Capsule\CompareVariantRenderer;

final class CompareSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'compare';

    protected string $styleClass = CompareStyle::class;
    protected string $rendererClass = CompareVariantRenderer::class;
}

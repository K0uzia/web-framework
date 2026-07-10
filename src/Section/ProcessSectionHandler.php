<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\ProcessStyle;
use Capsule\ProcessVariantRenderer;

final class ProcessSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'process';

    protected string $styleClass = ProcessStyle::class;
    protected string $rendererClass = ProcessVariantRenderer::class;
}

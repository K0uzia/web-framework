<?php

declare(strict_types=1);

namespace Capsule\Section\Process;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Process\ProcessStyle;
use Capsule\Section\Process\ProcessVariantRenderer;

final class ProcessSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'process';

    protected string $styleClass = ProcessStyle::class;
    protected string $rendererClass = ProcessVariantRenderer::class;
}

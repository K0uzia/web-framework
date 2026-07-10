<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\CodeStyle;
use Capsule\CodeVariantRenderer;

final class CodeSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'code';

    protected string $styleClass = CodeStyle::class;
    protected string $rendererClass = CodeVariantRenderer::class;

    public function jsModules(string $variant): array
    {
        return ['sections/code.js'];
    }
}

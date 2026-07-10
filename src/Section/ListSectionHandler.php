<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\ListStyle;
use Capsule\ListVariantRenderer;

final class ListSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'list';

    protected string $styleClass = ListStyle::class;
    protected string $rendererClass = ListVariantRenderer::class;
}

<?php

declare(strict_types=1);

namespace Capsule\Section\List;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\List\ListStyle;
use Capsule\Section\List\ListVariantRenderer;

final class ListSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'list';

    protected string $styleClass = ListStyle::class;
    protected string $rendererClass = ListVariantRenderer::class;
}

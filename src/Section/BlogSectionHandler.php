<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\BlogStyle;
use Capsule\BlogVariantRenderer;

final class BlogSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'blog';

    protected string $styleClass = BlogStyle::class;
    protected string $rendererClass = BlogVariantRenderer::class;
}

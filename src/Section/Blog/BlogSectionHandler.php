<?php

declare(strict_types=1);

namespace Capsule\Section\Blog;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Blog\BlogStyle;
use Capsule\Section\Blog\BlogVariantRenderer;

final class BlogSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'blog';

    protected string $styleClass = BlogStyle::class;
    protected string $rendererClass = BlogVariantRenderer::class;
}

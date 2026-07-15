<?php

declare(strict_types=1);

namespace Capsule\Section\Gallery;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Gallery\GalleryStyle;
use Capsule\Section\Gallery\GalleryVariantRenderer;

final class GallerySectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'gallery';

    protected string $styleClass = GalleryStyle::class;
    protected string $rendererClass = GalleryVariantRenderer::class;

    public function jsModules(string $variant): array
    {
        return ['sections/gallery.js'];
    }
}

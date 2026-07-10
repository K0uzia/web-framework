<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\GalleryStyle;
use Capsule\GalleryVariantRenderer;

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

<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\DownloadStyle;
use Capsule\DownloadVariantRenderer;

final class DownloadSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'download';

    protected string $styleClass = DownloadStyle::class;
    protected string $rendererClass = DownloadVariantRenderer::class;
}

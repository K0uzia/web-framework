<?php

declare(strict_types=1);

namespace Capsule\Section\Download;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Download\DownloadStyle;
use Capsule\Section\Download\DownloadVariantRenderer;

final class DownloadSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'download';

    protected string $styleClass = DownloadStyle::class;
    protected string $rendererClass = DownloadVariantRenderer::class;
}

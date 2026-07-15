<?php

declare(strict_types=1);

namespace Capsule\Section\Services;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Services\ServicesStyle;
use Capsule\Section\Services\ServicesVariantRenderer;

final class ServicesSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'services';

    protected string $styleClass = ServicesStyle::class;
    protected string $rendererClass = ServicesVariantRenderer::class;
}

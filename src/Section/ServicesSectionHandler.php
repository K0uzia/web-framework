<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\ServicesStyle;
use Capsule\ServicesVariantRenderer;

final class ServicesSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'services';

    protected string $styleClass = ServicesStyle::class;
    protected string $rendererClass = ServicesVariantRenderer::class;
}

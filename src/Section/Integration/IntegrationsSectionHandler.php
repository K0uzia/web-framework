<?php

declare(strict_types=1);

namespace Capsule\Section\Integration;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Integration\IntegrationStyle;
use Capsule\Section\Integration\IntegrationVariantRenderer;

final class IntegrationsSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'integrations';

    protected string $styleClass = IntegrationStyle::class;
    protected string $rendererClass = IntegrationVariantRenderer::class;
}

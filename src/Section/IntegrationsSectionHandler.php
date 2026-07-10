<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\IntegrationStyle;
use Capsule\IntegrationVariantRenderer;

final class IntegrationsSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'integrations';

    protected string $styleClass = IntegrationStyle::class;
    protected string $rendererClass = IntegrationVariantRenderer::class;
}

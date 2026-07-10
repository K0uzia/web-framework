<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\ComplianceStyle;
use Capsule\ComplianceVariantRenderer;

final class ComplianceSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'compliance';

    protected string $styleClass = ComplianceStyle::class;
    protected string $rendererClass = ComplianceVariantRenderer::class;
}

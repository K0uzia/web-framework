<?php

declare(strict_types=1);

namespace Capsule\Section\Compliance;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Compliance\ComplianceStyle;
use Capsule\Section\Compliance\ComplianceVariantRenderer;

final class ComplianceSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'compliance';

    protected string $styleClass = ComplianceStyle::class;
    protected string $rendererClass = ComplianceVariantRenderer::class;
}

<?php

declare(strict_types=1);

namespace Capsule\Section\CaseStudy;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\CaseStudy\CaseStudyStyle;
use Capsule\Section\CaseStudy\CaseStudyVariantRenderer;

final class CaseStudySectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'case-study';

    protected string $styleClass = CaseStudyStyle::class;
    protected string $rendererClass = CaseStudyVariantRenderer::class;
}

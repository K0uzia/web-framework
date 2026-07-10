<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\CaseStudyStyle;
use Capsule\CaseStudyVariantRenderer;

final class CaseStudySectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'case-study';

    protected string $styleClass = CaseStudyStyle::class;
    protected string $rendererClass = CaseStudyVariantRenderer::class;
}

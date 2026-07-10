<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\TestimonialStyle;
use Capsule\TestimonialVariantRenderer;

final class TestimonialsSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'testimonials';

    protected string $styleClass = TestimonialStyle::class;
    protected string $rendererClass = TestimonialVariantRenderer::class;
}

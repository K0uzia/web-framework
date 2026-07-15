<?php

declare(strict_types=1);

namespace Capsule\Section\Testimonial;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Testimonial\TestimonialStyle;
use Capsule\Section\Testimonial\TestimonialVariantRenderer;

final class TestimonialsSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'testimonials';

    protected string $styleClass = TestimonialStyle::class;
    protected string $rendererClass = TestimonialVariantRenderer::class;
}

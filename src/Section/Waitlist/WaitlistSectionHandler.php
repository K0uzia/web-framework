<?php

declare(strict_types=1);

namespace Capsule\Section\Waitlist;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Waitlist\WaitlistStyle;
use Capsule\Section\Waitlist\WaitlistVariantRenderer;

final class WaitlistSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'waitlist';

    protected string $styleClass = WaitlistStyle::class;
    protected string $rendererClass = WaitlistVariantRenderer::class;

    public function jsModules(string $variant): array
    {
        return ['sections/contact.js'];
    }
}

<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\WaitlistStyle;
use Capsule\WaitlistVariantRenderer;

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

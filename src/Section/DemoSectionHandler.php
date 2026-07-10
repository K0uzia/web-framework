<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\DemoStyle;
use Capsule\DemoVariantRenderer;

final class DemoSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'demo';

    protected string $styleClass = DemoStyle::class;
    protected string $rendererClass = DemoVariantRenderer::class;

    public function jsModules(string $variant): array
    {
        return ['sections/contact.js', 'sections/demo.js'];
    }
}

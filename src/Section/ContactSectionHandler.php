<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\ContactStyle;
use Capsule\ContactVariantRenderer;

final class ContactSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'contact';

    protected string $styleClass = ContactStyle::class;
    protected string $rendererClass = ContactVariantRenderer::class;

    public function jsModules(string $variant): array
    {
        return ['sections/contact.js'];
    }
}

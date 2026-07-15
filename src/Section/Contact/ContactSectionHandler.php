<?php

declare(strict_types=1);

namespace Capsule\Section\Contact;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Contact\ContactStyle;
use Capsule\Section\Contact\ContactVariantRenderer;

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

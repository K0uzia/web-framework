<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\LogosStyle;
use Capsule\LogosVariantRenderer;

final class LogosSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'logos';

    protected string $styleClass = LogosStyle::class;
    protected string $rendererClass = LogosVariantRenderer::class;
}

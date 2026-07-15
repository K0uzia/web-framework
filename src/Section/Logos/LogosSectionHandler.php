<?php

declare(strict_types=1);

namespace Capsule\Section\Logos;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Logos\LogosStyle;
use Capsule\Section\Logos\LogosVariantRenderer;

final class LogosSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'logos';

    protected string $styleClass = LogosStyle::class;
    protected string $rendererClass = LogosVariantRenderer::class;
}

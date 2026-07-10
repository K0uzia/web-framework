<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\ChangelogStyle;
use Capsule\ChangelogVariantRenderer;

final class ChangelogSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'changelog';

    protected string $styleClass = ChangelogStyle::class;
    protected string $rendererClass = ChangelogVariantRenderer::class;
}

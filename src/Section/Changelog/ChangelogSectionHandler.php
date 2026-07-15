<?php

declare(strict_types=1);

namespace Capsule\Section\Changelog;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Changelog\ChangelogStyle;
use Capsule\Section\Changelog\ChangelogVariantRenderer;

final class ChangelogSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'changelog';

    protected string $styleClass = ChangelogStyle::class;
    protected string $rendererClass = ChangelogVariantRenderer::class;
}

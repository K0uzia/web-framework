<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\CommunityStyle;
use Capsule\CommunityVariantRenderer;

final class CommunitySectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'community';

    protected string $styleClass = CommunityStyle::class;
    protected string $rendererClass = CommunityVariantRenderer::class;
}

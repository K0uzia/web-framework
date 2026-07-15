<?php

declare(strict_types=1);

namespace Capsule\Section\Community;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Community\CommunityStyle;
use Capsule\Section\Community\CommunityVariantRenderer;

final class CommunitySectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'community';

    protected string $styleClass = CommunityStyle::class;
    protected string $rendererClass = CommunityVariantRenderer::class;
}

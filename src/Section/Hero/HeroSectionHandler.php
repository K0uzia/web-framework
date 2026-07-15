<?php

declare(strict_types=1);

namespace Capsule\Section\Hero;

use Capsule\Section\AbstractSectionTypeHandler;
use Capsule\Section\SectionEnrichContext;

use Capsule\Section\Hero\HeroStyle;
use Capsule\Section\Hero\HeroVariantRenderer;

final class HeroSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'hero';

    protected string $styleClass = HeroStyle::class;
    protected string $rendererClass = HeroVariantRenderer::class;

    public function enrich(array $data, array $content, string $variant, SectionEnrichContext $context): array
    {
        $style = [];
        foreach ($data as $key => $value) {
            if (str_starts_with((string) $key, 'style_')) {
                $style[substr((string) $key, 6)] = $value;
            }
        }

        $data['hero_modifiers'] = HeroStyle::modifierClasses($style, $variant);
        $badgeText = trim((string) ($content['badge'] ?? ''));
        $data['hero_badge_html'] = $badgeText !== ''
            ? '<span class="section-hero__badge">' . htmlspecialchars($badgeText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
            : '';
        $subheading = trim((string) ($content['subheading'] ?? ''));
        $data['hero_subheading_html'] = $subheading !== ''
            ? ' <span class="section-hero__subheading">' . htmlspecialchars($subheading, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
            : '';

        return HeroVariantRenderer::enrich($data, $content, $variant, $context->renderHeroButtons($content));
    }

    public function jsModules(string $variant): array
    {
        return ['sections/hero.js'];
    }
}

<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\BlogStyle;
use Capsule\BlogVariantRenderer;
use Capsule\ChangelogStyle;
use Capsule\ChangelogVariantRenderer;
use Capsule\ContactStyle;
use Capsule\ContactVariantRenderer;
use Capsule\DownloadStyle;
use Capsule\DownloadVariantRenderer;
use Capsule\FeatureStyle;
use Capsule\FeatureVariantRenderer;
use Capsule\GalleryStyle;
use Capsule\GalleryVariantRenderer;
use Capsule\HeroStyle;
use Capsule\HeroVariantRenderer;
use Capsule\IntegrationStyle;
use Capsule\IntegrationVariantRenderer;
use Capsule\PricingStyle;
use Capsule\PricingVariantRenderer;
use Capsule\ProjectsStyle;
use Capsule\ProjectsVariantRenderer;
use Capsule\TeamStyle;
use Capsule\TeamVariantRenderer;
use Capsule\TimelineStyle;
use Capsule\TimelineVariantRenderer;
use Capsule\TestimonialStyle;
use Capsule\TestimonialVariantRenderer;

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

final class FeaturesSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'features';

    protected string $styleClass = FeatureStyle::class;
    protected string $rendererClass = FeatureVariantRenderer::class;

    public function jsModules(string $variant): array
    {
        return ['sections/features.js'];
    }
}

final class IntegrationsSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'integrations';

    protected string $styleClass = IntegrationStyle::class;
    protected string $rendererClass = IntegrationVariantRenderer::class;
}

final class PricingSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'pricing';

    protected string $styleClass = PricingStyle::class;
    protected string $rendererClass = PricingVariantRenderer::class;

    public function jsModules(string $variant): array
    {
        return ['sections/pricing.js'];
    }
}

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

final class TestimonialsSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'testimonials';

    protected string $styleClass = TestimonialStyle::class;
    protected string $rendererClass = TestimonialVariantRenderer::class;
}

final class GallerySectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'gallery';

    protected string $styleClass = GalleryStyle::class;
    protected string $rendererClass = GalleryVariantRenderer::class;

    public function jsModules(string $variant): array
    {
        return ['sections/gallery.js'];
    }
}

final class BlogSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'blog';

    protected string $styleClass = BlogStyle::class;
    protected string $rendererClass = BlogVariantRenderer::class;
}

final class DownloadSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'download';

    protected string $styleClass = DownloadStyle::class;
    protected string $rendererClass = DownloadVariantRenderer::class;
}

final class TeamSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'team';

    protected string $styleClass = TeamStyle::class;
    protected string $rendererClass = TeamVariantRenderer::class;
}

final class ProjectsSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'projects';

    protected string $styleClass = ProjectsStyle::class;
    protected string $rendererClass = ProjectsVariantRenderer::class;
}

final class TimelineSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'timeline';

    protected string $styleClass = TimelineStyle::class;
    protected string $rendererClass = TimelineVariantRenderer::class;
}

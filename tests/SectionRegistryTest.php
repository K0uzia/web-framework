<?php

declare(strict_types=1);

namespace Tests;

use Capsule\SectionAssets;
use Capsule\SectionRegistry;
use PHPUnit\Framework\TestCase;

final class SectionRegistryTest extends TestCase
{
    public function testLoadsAllHeroVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $types = $registry->getTypes();
        $variants = array_keys($registry->getVariants('hero'));

        $this->assertContains('hero', $types);
        $this->assertSame(SectionAssets::heroVariantIds(), $variants);
        $this->assertArrayHasKey('title', $registry->getContentFields('hero'));
        $this->assertArrayHasKey('review_avatars', $registry->getContentFields('hero'));
    }

    public function testLoadsAllFeatureVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('features'));

        $this->assertSame(SectionAssets::featureVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('features'));
    }

    public function testLoadsBlockGroups(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');

        $this->assertSame('hero', $registry->getGroup('hero'));
        $this->assertSame('feature', $registry->getGroup('features'));
        $this->assertSame('integration', $registry->getGroup('integrations'));
        $this->assertContains('hero', $registry->getGroups());
        $this->assertContains('feature', $registry->getGroups());
        $this->assertContains('integration', $registry->getGroups());
    }

    public function testLoadsAllIntegrationVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('integrations'));

        $this->assertSame(SectionAssets::integrationVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('integrations'));
    }

    public function testLoadsAllPricingVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('pricing'));

        $this->assertSame(SectionAssets::pricingVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('pricing'));
        $this->assertSame('pricing', $registry->getGroup('pricing'));
    }

    public function testLoadsAllContactVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('contact'));

        $this->assertSame(SectionAssets::contactVariantIds(), $variants);
        $this->assertArrayHasKey('email', $registry->getContentFields('contact'));
        $this->assertSame('contact', $registry->getGroup('contact'));
    }

    public function testLoadsAllTestimonialVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('testimonials'));

        $this->assertSame(SectionAssets::testimonialVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('testimonials'));
        $this->assertSame('testimonial', $registry->getGroup('testimonials'));
    }

    public function testLoadsAllGalleryVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('gallery'));

        $this->assertSame(SectionAssets::galleryVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('gallery'));
        $this->assertSame('gallery', $registry->getGroup('gallery'));
    }

    public function testLoadsAllBlogVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('blog'));

        $this->assertSame(SectionAssets::blogVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('blog'));
        $this->assertArrayHasKey('tagline', $registry->getContentFields('blog'));
        $this->assertSame('blog', $registry->getGroup('blog'));
    }

    public function testLoadsAllDownloadVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('download'));

        $this->assertSame(SectionAssets::downloadVariantIds(), $variants);
        $this->assertArrayHasKey('desktop_href', $registry->getContentFields('download'));
        $this->assertArrayHasKey('ios_href', $registry->getContentFields('download'));
        $this->assertSame('download', $registry->getGroup('download'));
    }

    public function testLoadsAllTeamVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('team'));

        $this->assertSame(SectionAssets::teamVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('team'));
        $this->assertSame('team', $registry->getGroup('team'));
    }

    public function testLoadsAllProjectsVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('projects'));

        $this->assertSame(SectionAssets::projectsVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('projects'));
        $this->assertSame('project', $registry->getGroup('projects'));
    }

    public function testLoadsAllTimelineVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('timeline'));

        $this->assertSame(SectionAssets::timelineVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('timeline'));
        $this->assertSame('timeline', $registry->getGroup('timeline'));
    }

    public function testLoadsAllChangelogVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('changelog'));

        $this->assertSame(SectionAssets::changelogVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('changelog'));
        $this->assertSame('changelog', $registry->getGroup('changelog'));
    }

    public function testLoadsAllProcessVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('process'));

        $this->assertSame(SectionAssets::processVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('process'));
        $this->assertSame('process', $registry->getGroup('process'));
    }

    public function testLoadsAllListVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('list'));

        $this->assertSame(SectionAssets::listVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('list'));
        $this->assertSame('list', $registry->getGroup('list'));
    }

    public function testLoadsAllIndustryVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('industry'));

        $this->assertSame(SectionAssets::industryVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('industry'));
        $this->assertSame('industry', $registry->getGroup('industry'));
    }

    public function testLoadsAllRateCardVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('rate-card'));

        $this->assertSame(SectionAssets::rateCardVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('rate-card'));
        $this->assertSame('rate-card', $registry->getGroup('rate-card'));
    }

    public function testLoadsAllLogosVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('logos'));

        $this->assertSame(SectionAssets::logosVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('logos'));
        $this->assertSame('logos', $registry->getGroup('logos'));
    }

    public function testLoadsAllServicesVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('services'));

        $this->assertSame(SectionAssets::servicesVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('services'));
        $this->assertSame('service', $registry->getGroup('services'));
    }

    public function testLoadsAllCompareVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('compare'));

        $this->assertSame(SectionAssets::compareVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('compare'));
        $this->assertSame('compare', $registry->getGroup('compare'));
    }

    public function testLoadsAllCtaVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('cta'));

        $this->assertSame(SectionAssets::ctaVariantIds(), $variants);
        $this->assertArrayHasKey('buttons', $registry->getContentFields('cta'));
        $this->assertArrayHasKey('items', $registry->getContentFields('cta'));
        $this->assertSame('cta', $registry->getGroup('cta'));
    }

    public function testLoadsAllAwardsVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('awards'));

        $this->assertSame(SectionAssets::awardsVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('awards'));
        $this->assertSame('award', $registry->getGroup('awards'));
    }

    public function testLoadsAllCommunityVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('community'));

        $this->assertSame(SectionAssets::communityVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('community'));
        $this->assertSame('community', $registry->getGroup('community'));
    }

    public function testLoadsAllStatsVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('stats'));

        $this->assertSame(SectionAssets::statsVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('stats'));
        $this->assertSame('stats', $registry->getGroup('stats'));
    }

    public function testLoadsAllCareersVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('careers'));

        $this->assertSame(SectionAssets::careersVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('careers'));
        $this->assertSame('career', $registry->getGroup('careers'));
    }

    public function testLoadsAllFaqVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('faq'));

        $this->assertSame(SectionAssets::faqVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('faq'));
        $this->assertSame('faq', $registry->getGroup('faq'));
    }

    public function testLoadsAllCodeVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('code'));

        $this->assertSame(SectionAssets::codeVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('code'));
        $this->assertSame('code', $registry->getGroup('code'));
    }

    public function testLoadsAllComplianceVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('compliance'));

        $this->assertSame(SectionAssets::complianceVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('compliance'));
        $this->assertArrayHasKey('logos', $registry->getContentFields('compliance'));
        $this->assertSame('compliance', $registry->getGroup('compliance'));
    }

    public function testLoadsAllCaseStudyVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('case-study'));

        $this->assertSame(SectionAssets::caseStudyVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('case-study'));
        $this->assertArrayHasKey('featured_title', $registry->getContentFields('case-study'));
        $this->assertSame('case-study', $registry->getGroup('case-study'));
    }

    public function testLoadsAllDemoVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('demo'));

        $this->assertSame(SectionAssets::demoVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('demo'));
        $this->assertArrayHasKey('logos', $registry->getContentFields('demo'));
        $this->assertSame('demo', $registry->getGroup('demo'));
    }

    public function testLoadsAllExperienceVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('experience'));

        $this->assertSame(SectionAssets::experienceVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('experience'));
        $this->assertArrayHasKey('link_label', $registry->getContentFields('experience'));
        $this->assertSame('experience', $registry->getGroup('experience'));
    }

    public function testLoadsAllWaitlistVariants(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $variants = array_keys($registry->getVariants('waitlist'));

        $this->assertSame(SectionAssets::waitlistVariantIds(), $variants);
        $this->assertArrayHasKey('items', $registry->getContentFields('waitlist'));
        $this->assertArrayHasKey('tagline', $registry->getContentFields('waitlist'));
        $this->assertSame('waitlist', $registry->getGroup('waitlist'));
    }

    public function testGetClientEditableFieldsReadsYamlTrueAsString(): void
    {
        $registry = new SectionRegistry(dirname(__DIR__) . '/resources/sections/registry.yaml');
        $editable = $registry->getClientEditableFields('hero');

        $this->assertArrayHasKey('title', $editable);
        $this->assertArrayHasKey('subtitle', $editable);
        $this->assertArrayNotHasKey('style', $editable);
    }
}

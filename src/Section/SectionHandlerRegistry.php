<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\Section\Awards\AwardsSectionHandler;
use Capsule\Section\Blog\BlogSectionHandler;
use Capsule\Section\Careers\CareersSectionHandler;
use Capsule\Section\CaseStudy\CaseStudySectionHandler;
use Capsule\Section\Changelog\ChangelogSectionHandler;
use Capsule\Section\Code\CodeSectionHandler;
use Capsule\Section\Community\CommunitySectionHandler;
use Capsule\Section\Compare\CompareSectionHandler;
use Capsule\Section\Compliance\ComplianceSectionHandler;
use Capsule\Section\Contact\ContactSectionHandler;
use Capsule\Section\Cta\CtaSectionHandler;
use Capsule\Section\Demo\DemoSectionHandler;
use Capsule\Section\Download\DownloadSectionHandler;
use Capsule\Section\Experience\ExperienceSectionHandler;
use Capsule\Section\Faq\FaqSectionHandler;
use Capsule\Section\Feature\FeaturesSectionHandler;
use Capsule\Section\Gallery\GallerySectionHandler;
use Capsule\Section\Hero\HeroSectionHandler;
use Capsule\Section\Industry\IndustrySectionHandler;
use Capsule\Section\Integration\IntegrationsSectionHandler;
use Capsule\Section\List\ListSectionHandler;
use Capsule\Section\Logos\LogosSectionHandler;
use Capsule\Section\Pricing\PricingSectionHandler;
use Capsule\Section\Process\ProcessSectionHandler;
use Capsule\Section\Projects\ProjectsSectionHandler;
use Capsule\Section\RateCard\RateCardSectionHandler;
use Capsule\Section\Services\ServicesSectionHandler;
use Capsule\Section\Stats\StatsSectionHandler;
use Capsule\Section\Team\TeamSectionHandler;
use Capsule\Section\Testimonial\TestimonialsSectionHandler;
use Capsule\Section\Timeline\TimelineSectionHandler;
use Capsule\Section\Login\LoginSectionHandler;
use Capsule\Section\Signup\SignupSectionHandler;
use Capsule\Section\Waitlist\WaitlistSectionHandler;

final class SectionHandlerRegistry
{
    /** @var array<string, SectionTypeHandler> */
    private array $handlers;

    public function __construct()
    {
        $this->handlers = [];
        foreach ($this->defaultHandlers() as $handler) {
            $this->handlers[$handler->type()] = $handler;
        }
    }

    public function has(string $type): bool
    {
        return isset($this->handlers[$type]);
    }

    public function get(string $type): ?SectionTypeHandler
    {
        return $this->handlers[$type] ?? null;
    }

    public function normalizeVariant(string $type, string $variant): string
    {
        $handler = $this->get($type);

        return $handler !== null ? $handler->normalizeVariant($variant) : $variant;
    }

    /**
     * @return list<SectionTypeHandler>
     */
    private function defaultHandlers(): array
    {
        return [
            new HeroSectionHandler(),
            new FeaturesSectionHandler(),
            new IntegrationsSectionHandler(),
            new PricingSectionHandler(),
            new RateCardSectionHandler(),
            new ContactSectionHandler(),
            new TestimonialsSectionHandler(),
            new GallerySectionHandler(),
            new BlogSectionHandler(),
            new ChangelogSectionHandler(),
            new ProcessSectionHandler(),
            new ListSectionHandler(),
            new IndustrySectionHandler(),
            new DownloadSectionHandler(),
            new TeamSectionHandler(),
            new ProjectsSectionHandler(),
            new TimelineSectionHandler(),
            new LogosSectionHandler(),
            new ServicesSectionHandler(),
            new CompareSectionHandler(),
            new CtaSectionHandler(),
            new AwardsSectionHandler(),
            new CommunitySectionHandler(),
            new StatsSectionHandler(),
            new CareersSectionHandler(),
            new FaqSectionHandler(),
            new CodeSectionHandler(),
            new ComplianceSectionHandler(),
            new CaseStudySectionHandler(),
            new DemoSectionHandler(),
            new ExperienceSectionHandler(),
            new WaitlistSectionHandler(),
            new LoginSectionHandler(),
            new SignupSectionHandler(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Capsule\Section;

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
        ];
    }
}

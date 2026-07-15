<?php

declare(strict_types=1);

/**
 * Corrige les imports des handlers migrés et met à jour SectionHandlerRegistry.
 */

$root = dirname(__DIR__);
$sectionDir = $root . '/src/Section';

$handlerMap = [
    'Hero/HeroSectionHandler.php' => 'Capsule\\Section\\Hero\\HeroSectionHandler',
    'Feature/FeaturesSectionHandler.php' => 'Capsule\\Section\\Feature\\FeaturesSectionHandler',
    'Integration/IntegrationsSectionHandler.php' => 'Capsule\\Section\\Integration\\IntegrationsSectionHandler',
    'Pricing/PricingSectionHandler.php' => 'Capsule\\Section\\Pricing\\PricingSectionHandler',
    'RateCard/RateCardSectionHandler.php' => 'Capsule\\Section\\RateCard\\RateCardSectionHandler',
    'Contact/ContactSectionHandler.php' => 'Capsule\\Section\\Contact\\ContactSectionHandler',
    'Testimonial/TestimonialsSectionHandler.php' => 'Capsule\\Section\\Testimonial\\TestimonialsSectionHandler',
    'Gallery/GallerySectionHandler.php' => 'Capsule\\Section\\Gallery\\GallerySectionHandler',
    'Blog/BlogSectionHandler.php' => 'Capsule\\Section\\Blog\\BlogSectionHandler',
    'Changelog/ChangelogSectionHandler.php' => 'Capsule\\Section\\Changelog\\ChangelogSectionHandler',
    'Process/ProcessSectionHandler.php' => 'Capsule\\Section\\Process\\ProcessSectionHandler',
    'List/ListSectionHandler.php' => 'Capsule\\Section\\List\\ListSectionHandler',
    'Industry/IndustrySectionHandler.php' => 'Capsule\\Section\\Industry\\IndustrySectionHandler',
    'Download/DownloadSectionHandler.php' => 'Capsule\\Section\\Download\\DownloadSectionHandler',
    'Team/TeamSectionHandler.php' => 'Capsule\\Section\\Team\\TeamSectionHandler',
    'Projects/ProjectsSectionHandler.php' => 'Capsule\\Section\\Projects\\ProjectsSectionHandler',
    'Timeline/TimelineSectionHandler.php' => 'Capsule\\Section\\Timeline\\TimelineSectionHandler',
    'Logos/LogosSectionHandler.php' => 'Capsule\\Section\\Logos\\LogosSectionHandler',
    'Services/ServicesSectionHandler.php' => 'Capsule\\Section\\Services\\ServicesSectionHandler',
    'Compare/CompareSectionHandler.php' => 'Capsule\\Section\\Compare\\CompareSectionHandler',
    'Cta/CtaSectionHandler.php' => 'Capsule\\Section\\Cta\\CtaSectionHandler',
    'Awards/AwardsSectionHandler.php' => 'Capsule\\Section\\Awards\\AwardsSectionHandler',
    'Community/CommunitySectionHandler.php' => 'Capsule\\Section\\Community\\CommunitySectionHandler',
    'Stats/StatsSectionHandler.php' => 'Capsule\\Section\\Stats\\StatsSectionHandler',
    'Careers/CareersSectionHandler.php' => 'Capsule\\Section\\Careers\\CareersSectionHandler',
    'Faq/FaqSectionHandler.php' => 'Capsule\\Section\\Faq\\FaqSectionHandler',
    'Code/CodeSectionHandler.php' => 'Capsule\\Section\\Code\\CodeSectionHandler',
    'Compliance/ComplianceSectionHandler.php' => 'Capsule\\Section\\Compliance\\ComplianceSectionHandler',
    'CaseStudy/CaseStudySectionHandler.php' => 'Capsule\\Section\\CaseStudy\\CaseStudySectionHandler',
    'Demo/DemoSectionHandler.php' => 'Capsule\\Section\\Demo\\DemoSectionHandler',
    'Experience/ExperienceSectionHandler.php' => 'Capsule\\Section\\Experience\\ExperienceSectionHandler',
    'Waitlist/WaitlistSectionHandler.php' => 'Capsule\\Section\\Waitlist\\WaitlistSectionHandler',
];

foreach ($handlerMap as $relative => $class) {
    $file = $sectionDir . '/' . $relative;
    if (!is_file($file)) {
        continue;
    }
    $code = file_get_contents($file);
    if ($code === false) {
        continue;
    }
    if (!str_contains($code, 'use Capsule\\Section\\AbstractSectionTypeHandler;')) {
        $code = preg_replace(
            '/(namespace [^;]+;\n\n)/',
            "$1use Capsule\\Section\\AbstractSectionTypeHandler;\nuse Capsule\\Section\\SectionEnrichContext;\n\n",
            $code,
            1,
        ) ?? $code;
        file_put_contents($file, $code);
    }
}

$stubFiles = glob($sectionDir . '/*SectionHandler.php') ?: [];
foreach ($stubFiles as $stub) {
    if (is_file($stub)) {
        unlink($stub);
    }
}

$registryFile = $sectionDir . '/SectionHandlerRegistry.php';
$registry = file_get_contents($registryFile);
if ($registry !== false) {
    $uses = '';
    $news = '';
    foreach ($handlerMap as $relative => $class) {
        $short = basename($class);
        $uses .= "use {$class};\n";
        $news .= "            new {$short}(),\n";
    }
    $registry = preg_replace('/private function defaultHandlers\(\): array\s*\{[\s\S]*?\}/', '', $registry) ?? $registry;
    $registry = rtrim($registry) . "\n\n    private function defaultHandlers(): array\n    {\n        return [\n{$news}        ];\n    }\n}\n";
    $registry = preg_replace('/(\nuse Capsule[^\n]*;\n)+/', "\n", $registry, 1) ?? $registry;
    $registry = str_replace(
        "namespace Capsule\\Section;\n\nfinal class SectionHandlerRegistry",
        "namespace Capsule\\Section;\n\n{$uses}\nfinal class SectionHandlerRegistry",
        $registry,
    );
    file_put_contents($registryFile, $registry);
}

fwrite(STDOUT, "Handlers corrigés.\n");

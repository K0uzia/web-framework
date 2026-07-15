<?php

declare(strict_types=1);

$root = dirname(__DIR__);

/** @var array<string, string> */
$map = [
    'HeroStyle' => 'Capsule\\Section\\Hero\\HeroStyle',
    'FeatureStyle' => 'Capsule\\Section\\Feature\\FeatureStyle',
    'IntegrationStyle' => 'Capsule\\Section\\Integration\\IntegrationStyle',
    'PricingStyle' => 'Capsule\\Section\\Pricing\\PricingStyle',
    'RateCardStyle' => 'Capsule\\Section\\RateCard\\RateCardStyle',
    'ContactStyle' => 'Capsule\\Section\\Contact\\ContactStyle',
    'TestimonialStyle' => 'Capsule\\Section\\Testimonial\\TestimonialStyle',
    'GalleryStyle' => 'Capsule\\Section\\Gallery\\GalleryStyle',
    'BlogStyle' => 'Capsule\\Section\\Blog\\BlogStyle',
    'ChangelogStyle' => 'Capsule\\Section\\Changelog\\ChangelogStyle',
    'ProcessStyle' => 'Capsule\\Section\\Process\\ProcessStyle',
    'ListStyle' => 'Capsule\\Section\\List\\ListStyle',
    'IndustryStyle' => 'Capsule\\Section\\Industry\\IndustryStyle',
    'DownloadStyle' => 'Capsule\\Section\\Download\\DownloadStyle',
    'TeamStyle' => 'Capsule\\Section\\Team\\TeamStyle',
    'ProjectsStyle' => 'Capsule\\Section\\Projects\\ProjectsStyle',
    'TimelineStyle' => 'Capsule\\Section\\Timeline\\TimelineStyle',
    'LogosStyle' => 'Capsule\\Section\\Logos\\LogosStyle',
    'ServicesStyle' => 'Capsule\\Section\\Services\\ServicesStyle',
    'CompareStyle' => 'Capsule\\Section\\Compare\\CompareStyle',
    'CtaStyle' => 'Capsule\\Section\\Cta\\CtaStyle',
    'AwardsStyle' => 'Capsule\\Section\\Awards\\AwardsStyle',
    'CommunityStyle' => 'Capsule\\Section\\Community\\CommunityStyle',
    'StatsStyle' => 'Capsule\\Section\\Stats\\StatsStyle',
    'CareersStyle' => 'Capsule\\Section\\Careers\\CareersStyle',
    'FaqStyle' => 'Capsule\\Section\\Faq\\FaqStyle',
    'CodeStyle' => 'Capsule\\Section\\Code\\CodeStyle',
    'ComplianceStyle' => 'Capsule\\Section\\Compliance\\ComplianceStyle',
    'CaseStudyStyle' => 'Capsule\\Section\\CaseStudy\\CaseStudyStyle',
    'DemoStyle' => 'Capsule\\Section\\Demo\\DemoStyle',
    'ExperienceStyle' => 'Capsule\\Section\\Experience\\ExperienceStyle',
    'WaitlistStyle' => 'Capsule\\Section\\Waitlist\\WaitlistStyle',
    'HeroVariantRenderer' => 'Capsule\\Section\\Hero\\HeroVariantRenderer',
    'FeatureVariantRenderer' => 'Capsule\\Section\\Feature\\FeatureVariantRenderer',
    'IntegrationVariantRenderer' => 'Capsule\\Section\\Integration\\IntegrationVariantRenderer',
    'PricingVariantRenderer' => 'Capsule\\Section\\Pricing\\PricingVariantRenderer',
    'RateCardVariantRenderer' => 'Capsule\\Section\\RateCard\\RateCardVariantRenderer',
    'ContactVariantRenderer' => 'Capsule\\Section\\Contact\\ContactVariantRenderer',
    'TestimonialVariantRenderer' => 'Capsule\\Section\\Testimonial\\TestimonialVariantRenderer',
    'GalleryVariantRenderer' => 'Capsule\\Section\\Gallery\\GalleryVariantRenderer',
    'BlogVariantRenderer' => 'Capsule\\Section\\Blog\\BlogVariantRenderer',
    'ChangelogVariantRenderer' => 'Capsule\\Section\\Changelog\\ChangelogVariantRenderer',
    'ProcessVariantRenderer' => 'Capsule\\Section\\Process\\ProcessVariantRenderer',
    'ListVariantRenderer' => 'Capsule\\Section\\List\\ListVariantRenderer',
    'IndustryVariantRenderer' => 'Capsule\\Section\\Industry\\IndustryVariantRenderer',
    'DownloadVariantRenderer' => 'Capsule\\Section\\Download\\DownloadVariantRenderer',
    'TeamVariantRenderer' => 'Capsule\\Section\\Team\\TeamVariantRenderer',
    'ProjectsVariantRenderer' => 'Capsule\\Section\\Projects\\ProjectsVariantRenderer',
    'TimelineVariantRenderer' => 'Capsule\\Section\\Timeline\\TimelineVariantRenderer',
    'LogosVariantRenderer' => 'Capsule\\Section\\Logos\\LogosVariantRenderer',
    'ServicesVariantRenderer' => 'Capsule\\Section\\Services\\ServicesVariantRenderer',
    'CompareVariantRenderer' => 'Capsule\\Section\\Compare\\CompareVariantRenderer',
    'CtaVariantRenderer' => 'Capsule\\Section\\Cta\\CtaVariantRenderer',
    'AwardsVariantRenderer' => 'Capsule\\Section\\Awards\\AwardsVariantRenderer',
    'CommunityVariantRenderer' => 'Capsule\\Section\\Community\\CommunityVariantRenderer',
    'StatsVariantRenderer' => 'Capsule\\Section\\Stats\\StatsVariantRenderer',
    'CareersVariantRenderer' => 'Capsule\\Section\\Careers\\CareersVariantRenderer',
    'FaqVariantRenderer' => 'Capsule\\Section\\Faq\\FaqVariantRenderer',
    'CodeVariantRenderer' => 'Capsule\\Section\\Code\\CodeVariantRenderer',
    'ComplianceVariantRenderer' => 'Capsule\\Section\\Compliance\\ComplianceVariantRenderer',
    'CaseStudyVariantRenderer' => 'Capsule\\Section\\CaseStudy\\CaseStudyVariantRenderer',
    'DemoVariantRenderer' => 'Capsule\\Section\\Demo\\DemoVariantRenderer',
    'ExperienceVariantRenderer' => 'Capsule\\Section\\Experience\\ExperienceVariantRenderer',
    'WaitlistVariantRenderer' => 'Capsule\\Section\\Waitlist\\WaitlistVariantRenderer',
];

foreach ($map as $short => $target) {
    $suffix = str_contains($short, 'VariantRenderer') ? 'VariantRenderer.php' : 'Style.php';
    $file = $root . '/src/' . $short . '.php';
    if (!is_file($file)) {
        continue;
    }
    $stub = <<<PHP
<?php

declare(strict_types=1);

namespace Capsule;

class_alias(\\{$target}::class, \\Capsule\\{$short}::class);

PHP;
    file_put_contents($file, $stub);
}

echo "Stubs régénérés.\n";

<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections;

use App\Http\Dev\Sections\Defaults\AwardsDefaults;
use App\Http\Dev\Sections\Defaults\BlogDefaults;
use App\Http\Dev\Sections\Defaults\CareersDefaults;
use App\Http\Dev\Sections\Defaults\CaseStudyDefaults;
use App\Http\Dev\Sections\Defaults\ChangelogDefaults;
use App\Http\Dev\Sections\Defaults\CodeDefaults;
use App\Http\Dev\Sections\Defaults\ComplianceDefaults;
use App\Http\Dev\Sections\Defaults\CommunityDefaults;
use App\Http\Dev\Sections\Defaults\CompareDefaults;
use App\Http\Dev\Sections\Defaults\CtaDefaults;
use App\Http\Dev\Sections\Defaults\ContactDefaults;
use App\Http\Dev\Sections\Defaults\DemoDefaults;
use App\Http\Dev\Sections\Defaults\DownloadDefaults;
use App\Http\Dev\Sections\Defaults\ExperienceDefaults;
use App\Http\Dev\Sections\Defaults\FaqDefaults;
use App\Http\Dev\Sections\Defaults\FeaturesDefaults;
use App\Http\Dev\Sections\Defaults\GalleryDefaults;
use App\Http\Dev\Sections\Defaults\HeroDefaults;
use App\Http\Dev\Sections\Defaults\IndustryDefaults;
use App\Http\Dev\Sections\Defaults\IntegrationsDefaults;
use App\Http\Dev\Sections\Defaults\PricingDefaults;
use App\Http\Dev\Sections\Defaults\ListDefaults;
use App\Http\Dev\Sections\Defaults\LoginDefaults;
use App\Http\Dev\Sections\Defaults\SignupDefaults;
use App\Http\Dev\Sections\Defaults\LogosDefaults;
use App\Http\Dev\Sections\Defaults\ProcessDefaults;
use App\Http\Dev\Sections\Defaults\ProjectsDefaults;
use App\Http\Dev\Sections\Defaults\RateCardDefaults;
use App\Http\Dev\Sections\Defaults\SharedDefaults;
use App\Http\Dev\Sections\Defaults\ServicesDefaults;
use App\Http\Dev\Sections\Defaults\StatsDefaults;
use App\Http\Dev\Sections\Defaults\TeamDefaults;
use App\Http\Dev\Sections\Defaults\TestimonialsDefaults;
use App\Http\Dev\Sections\Defaults\TimelineDefaults;
use App\Http\Dev\Sections\Defaults\WaitlistDefaults;
use Capsule\AwardsStyle;
use Capsule\BlogStyle;
use Capsule\CareersStyle;
use Capsule\CaseStudyStyle;
use Capsule\ChangelogStyle;
use Capsule\CodeStyle;
use Capsule\ComplianceStyle;
use Capsule\CommunityStyle;
use Capsule\CompareStyle;
use Capsule\CtaStyle;
use Capsule\ContactStyle;
use Capsule\DemoStyle;
use Capsule\DownloadStyle;
use Capsule\ExperienceStyle;
use Capsule\FaqStyle;
use Capsule\FeatureStyle;
use Capsule\GalleryStyle;
use Capsule\HeroStyle;
use Capsule\IndustryStyle;
use Capsule\IntegrationStyle;
use Capsule\PricingStyle;
use Capsule\ProcessStyle;
use Capsule\Section\List\ListStyle;
use Capsule\Section\Login\LoginStyle;
use Capsule\Section\Signup\SignupStyle;
use Capsule\LogosStyle;
use Capsule\RateCardStyle;
use Capsule\ProjectsStyle;
use Capsule\ServicesStyle;
use Capsule\StatsStyle;
use Capsule\TeamStyle;
use Capsule\TestimonialStyle;
use Capsule\TimelineStyle;
use Capsule\WaitlistStyle;

/**
 * Façade des contenus et styles par défaut des blocs section.
 */
final class SectionDefaults
{
    use SharedDefaults;
    use HeroDefaults;
    use FaqDefaults;
    use FeaturesDefaults;
    use IndustryDefaults;
    use IntegrationsDefaults;
    use PricingDefaults;
    use ContactDefaults;
    use TestimonialsDefaults;
    use GalleryDefaults;
    use AwardsDefaults;
    use BlogDefaults;
    use ChangelogDefaults;
    use DownloadDefaults;
    use TeamDefaults;
    use ProcessDefaults;
    use ProjectsDefaults;
    use RateCardDefaults;
    use TimelineDefaults;
    use LoginDefaults;
    use SignupDefaults;
    use ListDefaults;
    use LogosDefaults;
    use ServicesDefaults;
    use StatsDefaults;
    use CareersDefaults;
    use CaseStudyDefaults;
    use DemoDefaults;
    use ExperienceDefaults;
    use WaitlistDefaults;
    use CodeDefaults;
    use ComplianceDefaults;
    use CommunityDefaults;
    use CompareDefaults;
    use CtaDefaults;

    /**
     * @return array<string, mixed>
     */
    public static function content(string $type, string $variant = ''): array
    {
        return self::resolveContent($type, $variant);
    }

    /**
     * @return array<string, mixed>
     */
    private static function resolveContent(string $type, string $variant): array
    {
        if ($type === 'hero') {
            $variant = HeroStyle::normalizeVariant($variant !== '' ? $variant : 'hero3');

            return self::heroContent($variant);
        }
        if ($type === 'features') {
            $variant = FeatureStyle::normalizeVariant($variant !== '' ? $variant : 'feature3');

            return self::featuresContent($variant);
        }
        if ($type === 'integrations') {
            $variant = IntegrationStyle::normalizeVariant($variant !== '' ? $variant : 'integration3');

            return self::integrationsContent($variant);
        }
        if ($type === 'pricing') {
            $variant = PricingStyle::normalizeVariant($variant !== '' ? $variant : 'pricing2');

            return self::pricingContent($variant);
        }
        if ($type === 'rate-card') {
            $variant = RateCardStyle::normalizeVariant($variant !== '' ? $variant : 'rate-card2');

            return self::rateCardContent($variant);
        }
        if ($type === 'contact') {
            $variant = ContactStyle::normalizeVariant($variant !== '' ? $variant : 'contact2');

            return self::contactContent($variant);
        }
        if ($type === 'testimonials') {
            $variant = TestimonialStyle::normalizeVariant($variant !== '' ? $variant : 'testimonial4');

            return self::testimonialsContent($variant);
        }
        if ($type === 'gallery') {
            $variant = GalleryStyle::normalizeVariant($variant !== '' ? $variant : 'gallery4');

            return self::galleryContent($variant);
        }
        if ($type === 'blog') {
            $variant = BlogStyle::normalizeVariant($variant !== '' ? $variant : 'blog7');

            return self::blogContent($variant);
        }
        if ($type === 'changelog') {
            $variant = ChangelogStyle::normalizeVariant($variant !== '' ? $variant : 'changelog1');

            return self::changelogContent($variant);
        }
        if ($type === 'process') {
            $variant = ProcessStyle::normalizeVariant($variant !== '' ? $variant : 'process1');

            return self::processContent($variant);
        }
        if ($type === 'list') {
            $variant = ListStyle::normalizeVariant($variant !== '' ? $variant : 'list2');

            return self::listContent($variant);
        }
        if ($type === 'industry') {
            $variant = IndustryStyle::normalizeVariant($variant !== '' ? $variant : 'industries1');

            return self::industryContent($variant);
        }
        if ($type === 'download') {
            $variant = DownloadStyle::normalizeVariant($variant !== '' ? $variant : 'download1');

            return self::downloadContent($variant);
        }
        if ($type === 'team') {
            $variant = TeamStyle::normalizeVariant($variant !== '' ? $variant : 'team1');

            return self::teamContent($variant);
        }
        if ($type === 'projects') {
            $variant = ProjectsStyle::normalizeVariant($variant !== '' ? $variant : 'projects5');

            return self::projectsContent($variant);
        }
        if ($type === 'timeline') {
            $variant = TimelineStyle::normalizeVariant($variant !== '' ? $variant : 'timeline3');

            return self::timelineContent($variant);
        }
        if ($type === 'logos') {
            $variant = LogosStyle::normalizeVariant($variant !== '' ? $variant : 'logos3');

            return self::logosContent($variant);
        }
        if ($type === 'services') {
            $variant = ServicesStyle::normalizeVariant($variant !== '' ? $variant : 'services4');

            return self::servicesContent($variant);
        }
        if ($type === 'compare') {
            $variant = CompareStyle::normalizeVariant($variant !== '' ? $variant : 'compare7');

            return self::compareContent($variant);
        }
        if ($type === 'cta') {
            $variant = CtaStyle::normalizeVariant($variant !== '' ? $variant : 'cta4');

            return self::ctaContent($variant);
        }
        if ($type === 'awards') {
            $variant = AwardsStyle::normalizeVariant($variant !== '' ? $variant : 'awards1');

            return self::awardsContent($variant);
        }
        if ($type === 'community') {
            $variant = CommunityStyle::normalizeVariant($variant !== '' ? $variant : 'community1');

            return self::communityContent($variant);
        }
        if ($type === 'stats') {
            $variant = StatsStyle::normalizeVariant($variant !== '' ? $variant : 'stats6');

            return self::statsContent($variant);
        }
        if ($type === 'careers') {
            $variant = CareersStyle::normalizeVariant($variant !== '' ? $variant : 'careers1');

            return self::careersContent($variant);
        }
        if ($type === 'faq') {
            $variant = FaqStyle::normalizeVariant($variant !== '' ? $variant : 'faq1');

            return self::faqContent($variant);
        }
        if ($type === 'code') {
            $variant = CodeStyle::normalizeVariant($variant !== '' ? $variant : 'codeexample1');

            return self::codeContent($variant);
        }
        if ($type === 'compliance') {
            $variant = ComplianceStyle::normalizeVariant($variant !== '' ? $variant : 'compliance1');

            return self::complianceContent($variant);
        }
        if ($type === 'case-study') {
            $variant = CaseStudyStyle::normalizeVariant($variant !== '' ? $variant : 'casestudies2');

            return self::caseStudyContent($variant);
        }
        if ($type === 'demo') {
            $variant = DemoStyle::normalizeVariant($variant !== '' ? $variant : 'bookademo1');

            return self::demoContent($variant);
        }
        if ($type === 'experience') {
            $variant = ExperienceStyle::normalizeVariant($variant !== '' ? $variant : 'experience1');

            return self::experienceContent($variant);
        }
        if ($type === 'waitlist') {
            $variant = WaitlistStyle::normalizeVariant($variant !== '' ? $variant : 'waitlist1');

            return self::waitlistContent($variant);
        }
        if ($type === 'login') {
            $variant = LoginStyle::normalizeVariant($variant !== '' ? $variant : 'login1');

            return self::loginContent($variant);
        }
        if ($type === 'signup') {
            $variant = SignupStyle::normalizeVariant($variant !== '' ? $variant : 'signup1');

            return self::signupContent($variant);
        }

        return [];
    }

    /**
     * @return array<string, string>
     */
    public static function style(string $type): array
    {
        return match ($type) {
            'hero' => ['bg' => 'background', 'padding' => 'xl'],
            'features' => ['bg' => 'background', 'padding' => 'xl'],
            'integrations' => IntegrationStyle::defaults('integration3'),
            'pricing' => PricingStyle::defaults('pricing2'),
            'rate-card' => RateCardStyle::defaults('rate-card2'),
            'contact' => ContactStyle::defaults('contact2'),
            'testimonials' => TestimonialStyle::defaults('testimonial4'),
            'gallery' => GalleryStyle::defaults('gallery4'),
            'blog' => BlogStyle::defaults('blog7'),
            'changelog' => ChangelogStyle::defaults('changelog1'),
            'process' => ProcessStyle::defaults('process1'),
            'list' => ListStyle::defaults('list2'),
            'industry' => IndustryStyle::defaults('industries1'),
            'download' => DownloadStyle::defaults('download1'),
            'team' => TeamStyle::defaults('team1'),
            'projects' => ProjectsStyle::defaults('projects5'),
            'timeline' => TimelineStyle::defaults('timeline3'),
            'logos' => LogosStyle::defaults('logos3'),
            'services' => ServicesStyle::defaults('services4'),
            'compare' => CompareStyle::defaults('compare7'),
            'cta' => CtaStyle::defaults('cta4'),
            'awards' => AwardsStyle::defaults('awards1'),
            'community' => CommunityStyle::defaults('community1'),
            'stats' => StatsStyle::defaults('stats6'),
            'careers' => CareersStyle::defaults('careers1'),
            'faq' => FaqStyle::defaults('faq1'),
            'code' => CodeStyle::defaults('codeexample1'),
            'compliance' => ComplianceStyle::defaults('compliance1'),
            'case-study' => CaseStudyStyle::defaults('casestudies2'),
            'demo' => DemoStyle::defaults('bookademo1'),
            'experience' => ExperienceStyle::defaults('experience1'),
            'waitlist' => WaitlistStyle::defaults('waitlist1'),
            'login' => LoginStyle::defaults('login1'),
            'signup' => SignupStyle::defaults('signup1'),
            default => ['padding' => 'md'],
        };
    }
}

<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\SectionLayoutFamilies;

final class SectionCssModules
{
    /**
     * @param list<array<string, mixed>> $sections
     *
     * @return list<string>
     */
    public static function forType(string $type, string $variant, array $sections = []): array
    {
        $modules = match ($type) {
            'hero' => ['sections/hero/base.css', 'sections/hero/variants.css'],
            'features' => ['sections/features/base.css'],
            'integrations' => ['sections/integrations/base.css'],
            'pricing' => ['sections/pricing/base.css'],
            'rate-card' => ['sections/rate-card/base.css'],
            'contact' => ['sections/contact/base.css'],
            'testimonials' => ['sections/testimonials/base.css'],
            'gallery' => ['sections/gallery/base.css'],
            'blog' => ['sections/blog/base.css'],
            'changelog' => ['sections/changelog/base.css'],
            'process' => ['sections/process/base.css'],
            'list' => ['sections/list/base.css'],
            'industry' => ['sections/industry/base.css'],
            'download' => ['sections/download/base.css'],
            'team' => ['sections/team/base.css'],
            'projects' => ['sections/projects/base.css'],
            'timeline' => ['sections/timeline/base.css'],
            'logos' => ['sections/logos/base.css'],
            'services' => ['sections/services/base.css'],
            'compare' => ['sections/compare/base.css'],
            'cta' => ['sections/cta/base.css'],
            'awards' => ['sections/awards/base.css'],
            'community' => ['sections/community/base.css'],
            'stats' => ['sections/stats/base.css'],
            'careers' => ['sections/careers/base.css'],
            'faq' => ['sections/faq/base.css'],
            'code' => ['sections/code/base.css'],
            'compliance' => ['sections/compliance/base.css'],
            'case-study' => ['sections/case-study/base.css'],
            'demo' => ['sections/demo/base.css'],
            'experience' => ['sections/experience/base.css'],
            'waitlist' => ['sections/waitlist/base.css'],
            'login' => ['sections/login/base.css', 'sections/auth-switch.css'],
            'signup' => ['sections/signup/base.css', 'sections/auth-switch.css'],
            default => $type !== '' ? ['sections/' . $type . '/base.css'] : [],
        };

        if ($type === 'hero' && self::heroNeedsCustomizeCss($sections)) {
            $modules[] = 'sections/hero/customize.css';
        }

        foreach (SectionLayoutFamilies::cssFamilies($variant) as $family) {
            $modules[] = 'sections/' . $type . '/' . $family . '.css';
        }
        $modules[] = 'sections/' . $type . '/' . $variant . '.css';

        return $modules;
    }

    /**
     * @param list<array<string, mixed>> $sections
     */
    public static function sectionsNeedAppearanceCss(array $sections): bool
    {
        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }
            $style = is_array($section['style'] ?? null) ? $section['style'] : [];
            foreach (['text_align', 'title_size', 'subtitle_size', 'text_color'] as $key) {
                $value = trim((string) ($style[$key] ?? ''));
                if ($value === '') {
                    continue;
                }
                if (in_array($key, ['title_size', 'subtitle_size'], true) && $value === 'inherit') {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array<string, mixed>> $sections
     */
    private static function heroNeedsCustomizeCss(array $sections): bool
    {
        foreach ($sections as $section) {
            if (!is_array($section) || ($section['type'] ?? '') !== 'hero') {
                continue;
            }
            $style = is_array($section['style'] ?? null) ? $section['style'] : [];
            foreach ($style as $key => $value) {
                if (!in_array((string) $key, ['bg', 'padding'], true) && (string) $value !== '') {
                    return true;
                }
            }
        }

        return false;
    }
}

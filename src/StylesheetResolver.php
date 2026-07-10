<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Résout les feuilles de style à charger pour une page rendue.
 */
final class StylesheetResolver
{
    private readonly string $assetsRoot;

    public function __construct(
        private readonly string $publicCssDir,
        private readonly string $urlPrefix = '/assets/css',
        private readonly string $assetsUrlPrefix = '/assets',
    ) {
        $this->assetsRoot = dirname($publicCssDir);
    }

    /**
     * @param array<string, mixed>                          $meta
     * @param list<array{type: string, variant: string}>    $sectionRefs
     *
     * @return list<string> Chemins publics (/assets/css/… ou /assets/vendor/…)
     */
    public function resolve(
        string $layout,
        string $pageSlug,
        string $pageBody,
        array $meta = [],
        array $sectionRefs = [],
        array $sections = [],
    ): array {
        $candidates = [];

        $slug = $this->safeName($pageSlug);

        $this->push($candidates, 'base.css');
        $this->push($candidates, 'sections/shared.css');
        $this->push($candidates, 'sections/shared/media-fit.css');
        $this->push($candidates, 'layouts/' . $this->safeName($layout) . '.css');
        $this->push($candidates, 'partials/site-header.css');
        $this->push($candidates, 'partials/site-chrome-buttons.css');
        $this->push($candidates, 'partials/site-footer.css');
        $this->push($candidates, 'pages/' . $slug . '/' . $slug . '.css');

            foreach ($sectionRefs as $ref) {
            $type = $this->safeName($ref['type'] ?? '');
            $variant = $this->safeName($ref['variant'] ?? 'default');
            if ($type === '') {
                continue;
            }
            if ($type === 'hero') {
                $this->push($candidates, 'sections/hero/base.css');
                $this->push($candidates, 'sections/hero/variants.css');
                if ($this->heroNeedsCustomizeCss($sections)) {
                    $this->push($candidates, 'sections/hero/customize.css');
                }
            }
            if ($type === 'features') {
                $this->push($candidates, 'sections/features/base.css');
            }
            if ($type === 'integrations') {
                $this->push($candidates, 'sections/integrations/base.css');
            }
            if ($type === 'pricing') {
                $this->push($candidates, 'sections/pricing/base.css');
            }
            if ($type === 'rate-card') {
                $this->push($candidates, 'sections/rate-card/base.css');
            }
            if ($type === 'contact') {
                $this->push($candidates, 'sections/contact/base.css');
            }
            if ($type === 'testimonials') {
                $this->push($candidates, 'sections/testimonials/base.css');
            }
            if ($type === 'gallery') {
                $this->push($candidates, 'sections/gallery/base.css');
            }
            if ($type === 'blog') {
                $this->push($candidates, 'sections/blog/base.css');
            }
            if ($type === 'changelog') {
                $this->push($candidates, 'sections/changelog/base.css');
            }
            if ($type === 'process') {
                $this->push($candidates, 'sections/process/base.css');
            }
            if ($type === 'list') {
                $this->push($candidates, 'sections/list/base.css');
            }
            if ($type === 'industry') {
                $this->push($candidates, 'sections/industry/base.css');
            }
            if ($type === 'download') {
                $this->push($candidates, 'sections/download/base.css');
            }
            if ($type === 'team') {
                $this->push($candidates, 'sections/team/base.css');
            }
            if ($type === 'projects') {
                $this->push($candidates, 'sections/projects/base.css');
            }
            if ($type === 'timeline') {
                $this->push($candidates, 'sections/timeline/base.css');
            }
            if ($type === 'logos') {
                $this->push($candidates, 'sections/logos/base.css');
            }
            if ($type === 'services') {
                $this->push($candidates, 'sections/services/base.css');
            }
            if ($type === 'compare') {
                $this->push($candidates, 'sections/compare/base.css');
            }
            if ($type === 'cta') {
                $this->push($candidates, 'sections/cta/base.css');
            }
            if ($type === 'awards') {
                $this->push($candidates, 'sections/awards/base.css');
            }
            if ($type === 'community') {
                $this->push($candidates, 'sections/community/base.css');
            }
            if ($type === 'stats') {
                $this->push($candidates, 'sections/stats/base.css');
            }
            if ($type === 'careers') {
                $this->push($candidates, 'sections/careers/base.css');
            }
            if ($type === 'faq') {
                $this->push($candidates, 'sections/faq/base.css');
            }
            if ($type === 'code') {
                $this->push($candidates, 'sections/code/base.css');
            }
            if ($type === 'compliance') {
                $this->push($candidates, 'sections/compliance/base.css');
            }
            if ($type === 'case-study') {
                $this->push($candidates, 'sections/case-study/base.css');
            }
            if ($type === 'demo') {
                $this->push($candidates, 'sections/demo/base.css');
            }
            if ($type === 'experience') {
                $this->push($candidates, 'sections/experience/base.css');
            }
            if ($type === 'waitlist') {
                $this->push($candidates, 'sections/waitlist/base.css');
            }
            foreach (SectionLayoutFamilies::cssFamilies($variant) as $family) {
                $this->push($candidates, 'sections/' . $type . '/' . $family . '.css');
            }
            $this->push($candidates, 'sections/' . $type . '/' . $variant . '.css');
        }

        foreach ($this->extractSections($meta) as $section) {
            $this->push($candidates, 'pages/' . $slug . '/' . $this->safeName($section) . '.css');
        }

        foreach ($this->extractPartials($meta, $pageBody) as $partial) {
            $this->push($candidates, 'partials/' . $this->safeName($partial) . '.css');
        }

        $existing = [];
        foreach ($candidates as $relative) {
            $file = $this->publicCssDir . '/' . $relative;
            if (is_file($file) && !in_array($relative, $existing, true)) {
                $existing[] = $relative;
            }
        }

        $hrefs = [];
        $fontawesome = $this->assetsRoot . '/vendor/fontawesome/css/all.min.css';
        if (is_file($fontawesome)) {
            $hrefs[] = rtrim($this->assetsUrlPrefix, '/') . '/vendor/fontawesome/css/all.min.css';
        }

        foreach ($existing as $relative) {
            $hrefs[] = rtrim($this->urlPrefix, '/') . '/' . $relative;
        }

        return $hrefs;
    }

    /**
     * @param list<string> $hrefs
     */
    public function toHtml(array $hrefs): string
    {
        if ($hrefs === []) {
            return '';
        }

        $lines = array_map(
            static fn (string $href): string => '    <link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" />',
            $hrefs,
        );

        return implode("\n", $lines) . "\n";
    }

    /**
     * @param array<string, mixed> $meta
     *
     * @return list<string>
     */
    private function extractSections(array $meta): array
    {
        $sections = [];

        $styles = $meta['styles'] ?? null;
        if (is_array($styles) && is_array($styles['sections'] ?? null)) {
            $sections = array_merge($sections, array_keys($styles['sections']));
        }

        foreach (['styles_sections', 'sections'] as $key) {
            $value = $meta[$key] ?? null;
            if (!is_string($value) || trim($value) === '') {
                continue;
            }
            foreach (explode(',', $value) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $sections[] = $part;
                }
            }
        }

        return array_values(array_unique($sections));
    }

    /**
     * @param array<string, mixed> $meta
     *
     * @return list<string>
     */
    private function extractPartials(array $meta, string $pageBody): array
    {
        $partials = [];

        $styles = $meta['styles'] ?? null;
        if (is_array($styles) && is_array($styles['partials'] ?? null)) {
            $partials = array_merge($partials, array_keys($styles['partials']));
        }

        if (is_string($meta['styles_partials'] ?? null) && trim((string) $meta['styles_partials']) !== '') {
            foreach (explode(',', (string) $meta['styles_partials']) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $partials[] = pathinfo($part, PATHINFO_FILENAME);
                }
            }
        }

        if (preg_match_all('/\{\{\>\s*([a-zA-Z0-9_\/\-.]+)\s*\}\}/', $pageBody, $matches)) {
            foreach ($matches[1] as $include) {
                $partials[] = pathinfo($include, PATHINFO_FILENAME);
            }
        }

        return array_values(array_unique(array_filter($partials)));
    }

    /**
     * @param list<string> $list
     */
    private function push(array &$list, string $relative): void
    {
        $list[] = str_replace('\\', '/', $relative);
    }

    /**
     * @param list<array<string, mixed>> $sections
     */
    private function heroNeedsCustomizeCss(array $sections): bool
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

    private function safeName(string $name): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $base) ?? '';

        return $sanitized !== '' ? $sanitized : 'default';
    }
}

<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Résout les feuilles de style à charger pour une page rendue.
 */
final class StylesheetResolver
{
    public function __construct(
        private readonly string $publicCssDir,
        private readonly string $urlPrefix = '/assets/css',
    ) {
    }

    /**
     * @param array<string, mixed>                          $meta
     * @param list<array{type: string, variant: string}>    $sectionRefs
     *
     * @return list<string> Chemins publics (/assets/css/…)
     */
    public function resolve(
        string $layout,
        string $pageSlug,
        string $pageBody,
        array $meta = [],
        array $sectionRefs = [],
    ): array {
        $candidates = [];

        $slug = $this->safeName($pageSlug);

        $this->push($candidates, 'base.css');
        $this->push($candidates, 'sections/shared.css');
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

        return array_map(
            fn (string $relative): string => rtrim($this->urlPrefix, '/') . '/' . $relative,
            $existing,
        );
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

    private function safeName(string $name): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $base) ?? '';

        return $sanitized !== '' ? $sanitized : 'default';
    }
}

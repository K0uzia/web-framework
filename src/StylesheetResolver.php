<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Section\SectionCssModules;

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
        $this->push($candidates, 'partials/site-login-modal.css');
        $this->push($candidates, 'pages/' . $slug . '/' . $slug . '.css');

            foreach ($sectionRefs as $ref) {
            $type = $this->safeName($ref['type'] ?? '');
            $variant = $this->safeName($ref['variant'] ?? 'default');
            if ($type === '') {
                continue;
            }
            foreach (SectionCssModules::forType($type, $variant, $sections) as $cssModule) {
                $this->push($candidates, $cssModule);
            }
        }

        $modalSection = $meta['login_modal_section'] ?? null;
        if (is_array($modalSection)) {
            $modalType = $this->safeName((string) ($modalSection['type'] ?? ''));
            $modalVariant = $this->safeName((string) ($modalSection['variant'] ?? 'default'));
            if ($modalType !== '') {
                foreach (SectionCssModules::forType($modalType, $modalVariant, [$modalSection]) as $cssModule) {
                    $this->push($candidates, $cssModule);
                }
            }
        }

        $authRefs = is_array($meta['login_modal_auth_refs'] ?? null) ? $meta['login_modal_auth_refs'] : [];
        foreach ($authRefs as $ref) {
            if (!is_array($ref)) {
                continue;
            }
            $type = $this->safeName((string) ($ref['type'] ?? ''));
            $variant = $this->safeName((string) ($ref['variant'] ?? 'default'));
            if ($type === '') {
                continue;
            }
            foreach (SectionCssModules::forType($type, $variant, []) as $cssModule) {
                $this->push($candidates, $cssModule);
            }
        }

        foreach ($this->extractSections($meta) as $section) {
            $this->push($candidates, 'pages/' . $slug . '/' . $this->safeName($section) . '.css');
        }

        foreach ($this->extractPartials($meta, $pageBody) as $partial) {
            $this->push($candidates, 'partials/' . $this->safeName($partial) . '.css');
        }

        if (SectionCssModules::sectionsNeedAppearanceCss($sections)) {
            $this->push($candidates, 'sections/appearance.css');
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
    public function toHtml(array $hrefs, string $assetRoot = ''): string
    {
        if ($hrefs === []) {
            return '';
        }

        $root = rtrim($assetRoot, '/');
        $lines = array_map(
            static function (string $href) use ($root): string {
                if ($root !== '' && str_starts_with($href, '/')) {
                    $href = $root . $href;
                }

                return '    <link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" />';
            },
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

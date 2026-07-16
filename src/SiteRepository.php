<?php

declare(strict_types=1);

namespace Capsule;

use PDO;

final class SiteRepository
{
    private const THEME_KEY = 'theme';
    private const SITE_KEY = 'site';
    private const CLIENT_DASHBOARD_KEY = 'client_dashboard';

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array{medias_enabled: bool, pages: array<string, array{sections: array<string, array{fields: list<string>}>}>}
     */
    public function getClientDashboard(): array
    {
        return ClientDashboardConfig::normalize(
            $this->getJson(self::CLIENT_DASHBOARD_KEY, ClientDashboardConfig::empty()),
        );
    }

    /**
     * @param array<string, mixed> $config
     */
    public function setClientDashboard(array $config): void
    {
        $this->setJson(self::CLIENT_DASHBOARD_KEY, ClientDashboardConfig::normalize($config));
    }

    public function isClientPageEditable(string $slug): bool
    {
        return ClientDashboardConfig::isPageEditable($this->getClientDashboard(), $slug);
    }

    public function isClientMediasEnabled(): bool
    {
        return ClientDashboardConfig::isMediasEnabled($this->getClientDashboard());
    }

    /**
     * @return list<string>
     */
    public function clientAllowedFields(string $slug, string $sectionId): array
    {
        return ClientDashboardConfig::allowedFields($this->getClientDashboard(), $slug, $sectionId);
    }

    /**
     * @return array<string, mixed>
     */
    public function getSite(): array
    {
        return $this->getJson(self::SITE_KEY, $this->defaultSite());
    }

    /**
     * @param array<string, mixed> $site
     */
    public function setSite(array $site): void
    {
        $this->setJson(self::SITE_KEY, $site);
    }

    /**
     * @return array<string, mixed>
     */
    public function defaultSite(): array
    {
        return [
            'name' => 'CapsulePHP',
            'tagline' => 'Framework PHP minimal pour sites composables.',
            'home_label' => 'Accueil',
            'footer_text' => '© {year} {name}. Tous droits réservés.',
            'partials' => [
                'header' => true,
                'footer' => true,
            ],
            'nav_items' => [],
            'nav_mode' => 'auto',
            'header_cta' => [
                'enabled' => false,
                'label' => '',
                'href' => '',
            ],
            'show_tagline_in_header' => false,
            'show_tagline_in_footer' => true,
            'show_brand_in_footer' => true,
            'show_nav_in_footer' => true,
            'show_nav_in_header' => true,
            'header_brand' => [
                'show_logo' => true,
                'show_name' => true,
            ],
            'header_login' => [
                'enabled' => false,
                'label' => 'Connexion',
                'href' => '/login',
            ],
            'header_layout' => [
                'brand' => 'left',
                'nav' => 'right',
                'cta' => 'right',
                'login' => 'right',
            ],
            'footer_login' => [
                'enabled' => false,
                'label' => 'Connexion',
                'href' => '/login',
            ],
            'footer_layout' => [
                'brand' => 'left',
                'nav' => 'right',
                'login' => 'right',
            ],
            'logo_url' => '',
            'favicon_url' => '',
            'og_image_url' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getTheme(): array
    {
        return $this->getJson(self::THEME_KEY, $this->defaultTheme());
    }

    /**
     * @param array<string, mixed> $theme
     */
    public function setTheme(array $theme): void
    {
        $this->setJson(self::THEME_KEY, $theme);
    }

    public function persistTheme(array $theme, string $publicCssDir): void
    {
        $this->setTheme($theme);
        $this->writeThemeCssFile($theme, $publicCssDir);
    }

    public function ensureThemeCssFile(string $publicCssDir): void
    {
        $theme = $this->getTheme();
        $css = $this->fullThemeCssFrom($theme, $publicCssDir);
        $path = $this->themeCssPath($publicCssDir);
        if (is_file($path)) {
            $existing = file_get_contents($path);
            if ($existing === $css) {
                return;
            }
        }

        $dir = rtrim($publicCssDir, '/\\');
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException('Impossible de créer le dossier CSS thème : ' . $dir);
        }

        $written = file_put_contents($path, $css);
        if ($written === false) {
            throw new \RuntimeException('Impossible d\'écrire le fichier CSS thème.');
        }
    }

    /**
     * @param array<string, mixed> $theme
     */
    public function writeThemeCssFile(array $theme, string $publicCssDir): void
    {
        $dir = rtrim($publicCssDir, '/\\');
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException('Impossible de créer le dossier CSS thème : ' . $dir);
        }

        $written = file_put_contents($this->themeCssPath($publicCssDir), $this->fullThemeCssFrom($theme, $publicCssDir));
        if ($written === false) {
            throw new \RuntimeException('Impossible d\'écrire le fichier CSS thème.');
        }
    }

    /**
     * @param array<string, mixed> $theme
     */
    public function themeCssVersion(array $theme, string $publicCssDir): string
    {
        $colors = is_array($theme['colors'] ?? null) ? $theme['colors'] : [];
        $fonts = is_array($theme['fonts'] ?? null) ? $theme['fonts'] : [];
        $spacing = is_array($theme['spacing'] ?? null) ? $theme['spacing'] : [];
        $layout = is_array($theme['layout'] ?? null) ? $theme['layout'] : [];
        $bindingsHash = hash('xxh128', $this->themeBindingsCss($publicCssDir));

        return substr(hash('xxh128', json_encode([$colors, $fonts, $spacing, $layout, $bindingsHash], JSON_THROW_ON_ERROR)), 0, 12);
    }

    public function themeCssLinkHtml(string $assetRoot, ?array $theme = null, string $publicCssDir = ''): string
    {
        $theme ??= $this->getTheme();
        $href = rtrim($assetRoot, '/') . '/assets/css/theme-generated.css?v='
            . rawurlencode($this->themeCssVersion($theme, $publicCssDir));

        return '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" />';
    }

    public function themeCssInlineHtml(?array $theme = null, string $publicCssDir = '', string $assetRoot = ''): string
    {
        return $this->themeHeadHtml($assetRoot, $theme, $publicCssDir);
    }

    public function themeCssVarsInlineHtml(?array $theme = null): string
    {
        $theme ??= $this->getTheme();

        return '<style>' . trim($this->themeCssFrom($theme)) . '</style>';
    }

    public function themeBindingsLinkHtml(string $assetRoot, ?array $theme = null, string $publicCssDir = ''): string
    {
        $theme ??= $this->getTheme();
        if ($publicCssDir === '' || !is_file($this->themeBindingsPath($publicCssDir))) {
            return '';
        }

        $href = rtrim($assetRoot, '/') . '/assets/css/theme-bindings.css?v='
            . rawurlencode($this->themeCssVersion($theme, $publicCssDir));

        return '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" />';
    }

    /**
     * Variables :root inline + theme-bindings.css en dernier (après les CSS de blocs).
     */
    public function themeHeadHtml(string $assetRoot, ?array $theme = null, string $publicCssDir = ''): string
    {
        $parts = [trim($this->themeCssVarsInlineHtml($theme))];
        $bindings = trim($this->themeBindingsLinkHtml($assetRoot, $theme, $publicCssDir));
        if ($bindings !== '') {
            $parts[] = $bindings;
        }

        return implode("\n", $parts);
    }

    /**
     * @param array<string, mixed> $theme
     */
    public function fullThemeCssFrom(array $theme, string $publicCssDir): string
    {
        $core = trim($this->themeCssFrom($theme));
        $bindings = $this->themeBindingsCss($publicCssDir);
        if ($bindings === '') {
            return $core;
        }

        return $core . "\n\n" . $bindings;
    }

    public function themeBindingsCss(string $publicCssDir): string
    {
        $path = $this->themeBindingsPath($publicCssDir);
        if (!is_file($path)) {
            return '';
        }

        $content = file_get_contents($path);

        return is_string($content) ? trim($content) : '';
    }

    private function themeCssPath(string $publicCssDir): string
    {
        return rtrim($publicCssDir, '/\\') . '/theme-generated.css';
    }

    private function themeBindingsPath(string $publicCssDir): string
    {
        return rtrim($publicCssDir, '/\\') . '/theme-bindings.css';
    }

    /**
     * @return array<string, mixed>
     */
    public function defaultTheme(): array
    {
        return [
            'colors' => ThemePalette::defaults(),
            'fonts' => [
                'heading' => 'Inter, system-ui, sans-serif',
                'body' => 'system-ui, sans-serif',
            ],
            'spacing' => [
                'section' => '4rem',
            ],
            'layout' => [
                'radius' => '10px',
                'container' => '72rem',
            ],
            'custom_fonts' => [],
        ];
    }

    public function themeCss(): string
    {
        return $this->themeCssFrom($this->getTheme());
    }

    /**
     * @param array<string, mixed> $theme
     */
    public function themeCssFrom(array $theme): string
    {
        $colors = is_array($theme['colors'] ?? null) ? $theme['colors'] : [];
        $fonts = is_array($theme['fonts'] ?? null) ? $theme['fonts'] : [];
        $spacing = is_array($theme['spacing'] ?? null) ? $theme['spacing'] : [];
        $layout = is_array($theme['layout'] ?? null) ? $theme['layout'] : [];
        $customFonts = is_array($theme['custom_fonts'] ?? null) ? $theme['custom_fonts'] : [];

        $lines = [];
        foreach ($customFonts as $font) {
            $lines = array_merge($lines, $this->fontFaceRule($font));
        }

        $colors = ThemePalette::normalize($colors);

        $lines[] = ':root {';
        foreach ($colors as $key => $value) {
            $lines[] = '    --color-' . $this->cssVarName((string) $key) . ': ' . $value . ';';
        }
        $lines[] = '    --color-muted: var(--color-surface);';
        $lines[] = '    --color-elevated: var(--color-surface);';
        $lines[] = '    --color-foreground: var(--color-text);';
        $lines[] = '    --color-muted-foreground: var(--color-text-muted);';
        $lines[] = '    --color-primary-foreground: var(--color-button-primary-text);';
        $lines[] = '    --color-accent: var(--color-surface);';
        $lines[] = '    --color-destructive: var(--color-error);';
        $lines[] = '    --color-ring: var(--color-focus-ring);';
        foreach ($fonts as $key => $value) {
            if (is_string($value) && $value !== '') {
                $lines[] = '    --font-' . $this->cssVarName((string) $key) . ': ' . $value . ';';
            }
        }
        foreach ($spacing as $key => $value) {
            if (is_string($value) && $value !== '') {
                $lines[] = '    --spacing-' . $this->cssVarName((string) $key) . ': ' . $value . ';';
            }
        }
        if (is_string($layout['radius'] ?? null) && $layout['radius'] !== '') {
            $lines[] = '    --radius-md: ' . $layout['radius'] . ';';
        }
        if (is_string($layout['container'] ?? null) && $layout['container'] !== '') {
            $lines[] = '    --container: ' . $layout['container'] . ';';
        }
        $lines[] = '}';

        return implode("\n", $lines);
    }

    /**
     * @param mixed $font
     *
     * @return list<string>
     */
    private function fontFaceRule(mixed $font): array
    {
        if (!is_array($font)) {
            return [];
        }

        $name = is_string($font['name'] ?? null) ? trim($font['name']) : '';
        $url = is_string($font['url'] ?? null) ? trim($font['url']) : '';
        if ($name === '' || $url === '') {
            return [];
        }

        $format = is_string($font['format'] ?? null) ? $font['format'] : '';
        $formatDecl = $format !== '' ? ' format(\'' . addslashes($format) . '\')' : '';

        return [
            '@font-face {',
            '    font-family: "' . addslashes($name) . '";',
            '    src: url("' . str_replace('"', '', $url) . '")' . $formatDecl . ';',
            '    font-weight: 100 900;',
            '    font-style: normal;',
            '    font-display: swap;',
            '}',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getJson(string $key, array $default): array
    {
        $stmt = $this->pdo->prepare('SELECT value FROM site_settings WHERE key = :key');
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();
        if ($value === false) {
            return $default;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : $default;
    }

    /**
     * @param array<string, mixed> $value
     */
    private function setJson(string $key, array $value): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO site_settings (key, value, updated_at) VALUES (:key, :value, datetime(\'now\'))
             ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = datetime(\'now\')',
        );
        $stmt->execute([
            'key' => $key,
            'value' => json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function cssVarName(string $name): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $name) ?? $name;

        return str_replace('_', '-', $sanitized);
    }
}

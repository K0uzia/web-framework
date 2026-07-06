<?php

declare(strict_types=1);

namespace Capsule;

use PDO;

final class SiteRepository
{
    private const THEME_KEY = 'theme';
    private const SITE_KEY = 'site';

    public function __construct(private readonly PDO $pdo)
    {
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

    /**
     * @return array<string, mixed>
     */
    public function defaultTheme(): array
    {
        return [
            'colors' => [
                'primary' => '#3b82f6',
                'secondary' => '#64748b',
                'background' => '#ffffff',
                'text' => '#0f172a',
                'muted' => '#f1f5f9',
                'border' => '#e2e8f0',
            ],
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
        $theme = $this->getTheme();
        $colors = is_array($theme['colors'] ?? null) ? $theme['colors'] : [];
        $fonts = is_array($theme['fonts'] ?? null) ? $theme['fonts'] : [];
        $spacing = is_array($theme['spacing'] ?? null) ? $theme['spacing'] : [];
        $layout = is_array($theme['layout'] ?? null) ? $theme['layout'] : [];
        $customFonts = is_array($theme['custom_fonts'] ?? null) ? $theme['custom_fonts'] : [];

        $lines = [];
        foreach ($customFonts as $font) {
            $lines = array_merge($lines, $this->fontFaceRule($font));
        }

        $lines[] = ':root {';
        foreach ($colors as $key => $value) {
            if (is_string($value) && $value !== '') {
                $lines[] = '    --color-' . $this->cssVarName((string) $key) . ': ' . $value . ';';
            }
        }
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
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $name) ?? $name;
    }
}

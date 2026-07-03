<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\DevDashboard;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\FormData;
use Capsule\SiteRepository;

final class ThemeController
{
    use DevHx;

    /** @var array<string, string> */
    private const FONT_PRESETS = [
        'Inter (si installée), sinon système' => 'Inter, system-ui, sans-serif',
        'Système (system-ui)' => 'system-ui, sans-serif',
        'Système (natif étendu)' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
        'Arial' => 'Arial, Helvetica, sans-serif',
        'Verdana' => 'Verdana, Geneva, sans-serif',
        'Trebuchet MS' => '"Trebuchet MS", sans-serif',
        'Georgia (serif)' => 'Georgia, "Times New Roman", Times, serif',
        'Times New Roman (serif)' => '"Times New Roman", Times, serif',
        'Courier New (monospace)' => '"Courier New", Courier, monospace',
    ];

    public function __construct(
        private readonly DevDashboard $ui,
        private readonly SiteRepository $site,
        private readonly FontUploader $fonts,
    ) {
    }

    public function edit(Request $request): Response
    {
        $theme = $this->site->getTheme();
        $colors = is_array($theme['colors'] ?? null) ? $theme['colors'] : [];
        $fonts = is_array($theme['fonts'] ?? null) ? $theme['fonts'] : [];
        $spacing = is_array($theme['spacing'] ?? null) ? $theme['spacing'] : [];
        $layout = is_array($theme['layout'] ?? null) ? $theme['layout'] : [];
        $customFonts = is_array($theme['custom_fonts'] ?? null) ? $theme['custom_fonts'] : [];

        $heading = $this->buildFontOptions((string) ($fonts['heading'] ?? ''), $customFonts);
        $body = $this->buildFontOptions((string) ($fonts['body'] ?? ''), $customFonts);

        return $this->ui->render('theme-edit.html', [
            'title' => 'Thème',
            'crumb_html' => Breadcrumb::render([['label' => 'Thème']]),
            'color_primary' => (string) ($colors['primary'] ?? '#3b82f6'),
            'color_secondary' => (string) ($colors['secondary'] ?? '#64748b'),
            'color_background' => (string) ($colors['background'] ?? '#ffffff'),
            'color_text' => (string) ($colors['text'] ?? '#0f172a'),
            'color_muted' => (string) ($colors['muted'] ?? '#f1f5f9'),
            'color_border' => (string) ($colors['border'] ?? '#e2e8f0'),
            'font_heading' => (string) ($fonts['heading'] ?? 'Inter, system-ui, sans-serif'),
            'font_body' => (string) ($fonts['body'] ?? 'system-ui, sans-serif'),
            'font_heading_options' => $heading['html'],
            'font_heading_custom_hidden' => $heading['show_custom'] ? '' : 'hidden',
            'font_body_options' => $body['html'],
            'font_body_custom_hidden' => $body['show_custom'] ? '' : 'hidden',
            'font_manager_html' => $this->buildFontManagerHtml($customFonts),
            'spacing_section' => (string) ($spacing['section'] ?? '4rem'),
            'layout_radius' => (string) ($layout['radius'] ?? '10px'),
            'layout_container' => (string) ($layout['container'] ?? '72rem'),
            'preview_url' => '/dev/preview/_',
            'flash' => $this->ui->flashFromRequest($request),
        ]);
    }

    public function uploadFont(Request $request): Response
    {
        $data = FormData::fromRequest($request);
        $file = $request->files['file'] ?? null;
        $theme = $this->site->getTheme();
        $customFonts = is_array($theme['custom_fonts'] ?? null) ? $theme['custom_fonts'] : [];

        try {
            if (!is_array($file)) {
                throw new FontUploadException('Aucun fichier reçu.');
            }
            $stored = $this->fonts->store($file, trim($data['font_name'] ?? ''));
            $customFonts[] = [
                'id' => 'font-' . bin2hex(random_bytes(4)),
                'name' => $stored['name'],
                'url' => $stored['url'],
                'format' => $stored['format'],
            ];
            $theme['custom_fonts'] = $customFonts;
            $this->site->setTheme($theme);
            $message = 'Police « ' . $stored['name'] . ' » importée.';
        } catch (FontUploadException $e) {
            $message = $e->getMessage();
        }

        return $this->ui->withFlash($this->ui->redirect('/dev/theme'), $message);
    }

    public function removeFont(Request $request, string $id): Response
    {
        $theme = $this->site->getTheme();
        $customFonts = is_array($theme['custom_fonts'] ?? null) ? $theme['custom_fonts'] : [];

        $remaining = [];
        foreach ($customFonts as $font) {
            if (is_array($font) && ($font['id'] ?? '') === $id) {
                $this->fonts->delete((string) ($font['url'] ?? ''));

                continue;
            }
            $remaining[] = $font;
        }

        $theme['custom_fonts'] = $remaining;
        $this->site->setTheme($theme);

        return $this->ui->withFlash($this->ui->redirect('/dev/theme'), 'Police supprimée.');
    }

    /**
     * @param list<array<string, mixed>> $customFonts
     *
     * @return array{html: string, show_custom: bool}
     */
    private function buildFontOptions(string $current, array $customFonts): array
    {
        $options = [];
        $matched = false;

        foreach (self::FONT_PRESETS as $label => $stack) {
            $selected = $stack === $current;
            $matched = $matched || $selected;
            $options[] = '<option value="' . htmlspecialchars($stack, ENT_QUOTES) . '"' . ($selected ? ' selected' : '') . '>'
                . htmlspecialchars($label, ENT_QUOTES) . '</option>';
        }

        foreach ($customFonts as $font) {
            if (!is_array($font)) {
                continue;
            }
            $name = is_string($font['name'] ?? null) ? trim($font['name']) : '';
            if ($name === '') {
                continue;
            }
            $stack = '"' . $name . '", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            $selected = $stack === $current;
            $matched = $matched || $selected;
            $options[] = '<option value="' . htmlspecialchars($stack, ENT_QUOTES) . '"' . ($selected ? ' selected' : '') . '>'
                . htmlspecialchars($name, ENT_QUOTES) . ' (importée)</option>';
        }

        $options[] = '<option value="__custom__"' . (!$matched ? ' selected' : '') . '>Personnalisé (CSS)…</option>';

        return ['html' => implode('', $options), 'show_custom' => !$matched];
    }

    /**
     * @param list<array<string, mixed>> $customFonts
     */
    private function buildFontManagerHtml(array $customFonts): string
    {
        if ($customFonts === []) {
            return '<p class="dev-hint">Aucune police importée. Ajoutez un fichier .woff2, .woff, .ttf ou .otf ci-dessous.</p>';
        }

        $rows = [];
        foreach ($customFonts as $font) {
            if (!is_array($font)) {
                continue;
            }
            $id = (string) ($font['id'] ?? '');
            $name = (string) ($font['name'] ?? '');
            $url = (string) ($font['url'] ?? '');

            $rows[] = '<div class="dev-font-row">'
                . '<span class="dev-font-row__name" style="font-family:&quot;' . htmlspecialchars($name, ENT_QUOTES) . '&quot;, sans-serif;">' . htmlspecialchars($name, ENT_QUOTES) . '</span>'
                . '<code class="dev-font-row__file">' . htmlspecialchars(basename($url), ENT_QUOTES) . '</code>'
                . '<form class="dev-inline-form" method="post" action="/dev/theme/fonts/' . rawurlencode($id) . '/remove" data-confirm="Supprimer cette police ? Les blocs qui l\'utilisent reviendront à la police système.">'
                . '<button type="submit" class="dev-icon-btn dev-icon-btn--danger" aria-label="Supprimer cette police" title="Supprimer"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>'
                . '</form></div>';
        }

        return '<div class="dev-font-list">' . implode('', $rows) . '</div>';
    }

    public function update(Request $request): Response
    {
        $data = FormData::fromRequest($request);
        $this->site->setTheme($this->buildThemeFromForm($data));

        if ($this->isHx($request)) {
            return $this->ui->partial('saved.html', ['message' => 'Thème enregistré']);
        }

        return $this->ui->withFlash($this->ui->redirect('/dev/theme'), 'Thème enregistré.');
    }

    public function reset(Request $request): Response
    {
        $this->site->setTheme($this->site->defaultTheme());

        if ($this->isHx($request)) {
            return $this->ui->partial('saved.html', ['message' => 'Thème réinitialisé']);
        }

        return $this->ui->withFlash($this->ui->redirect('/dev/theme'), 'Thème réinitialisé.');
    }

    /**
     * @param array<string, string> $data
     *
     * @return array<string, mixed>
     */
    private function buildThemeFromForm(array $data): array
    {
        $theme = $this->site->getTheme();
        $colors = is_array($theme['colors'] ?? null) ? $theme['colors'] : [];
        $fonts = is_array($theme['fonts'] ?? null) ? $theme['fonts'] : [];
        $spacing = is_array($theme['spacing'] ?? null) ? $theme['spacing'] : [];
        $layout = is_array($theme['layout'] ?? null) ? $theme['layout'] : [];

        $colors['primary'] = $data['color_primary'] ?? $colors['primary'] ?? '#3b82f6';
        $colors['secondary'] = $data['color_secondary'] ?? $colors['secondary'] ?? '#64748b';
        $colors['background'] = $data['color_background'] ?? $colors['background'] ?? '#ffffff';
        $colors['text'] = $data['color_text'] ?? $colors['text'] ?? '#0f172a';
        $colors['muted'] = $data['color_muted'] ?? $colors['muted'] ?? '#f1f5f9';
        $colors['border'] = $data['color_border'] ?? $colors['border'] ?? '#e2e8f0';
        $fonts['heading'] = trim($data['font_heading'] ?? (string) ($fonts['heading'] ?? ''));
        $fonts['body'] = trim($data['font_body'] ?? (string) ($fonts['body'] ?? ''));
        $spacing['section'] = trim($data['spacing_section'] ?? (string) ($spacing['section'] ?? '4rem'));
        $layout['radius'] = trim($data['layout_radius'] ?? (string) ($layout['radius'] ?? '10px'));
        $layout['container'] = trim($data['layout_container'] ?? (string) ($layout['container'] ?? '72rem'));

        $theme['colors'] = $colors;
        $theme['fonts'] = $fonts;
        $theme['spacing'] = $spacing;
        $theme['layout'] = $layout;

        return $theme;
    }
}

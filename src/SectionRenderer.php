<?php

declare(strict_types=1);

namespace Capsule;

final class SectionRenderer
{
    public function __construct(
        private readonly View $view,
        private readonly string $sectionsDir,
        private readonly bool $isDev = true,
    ) {
    }

    /**
     * @param list<array<string, mixed>> $sections
     */
    public function renderAll(array $sections): string
    {
        $html = [];
        foreach ($sections as $section) {
            $rendered = $this->renderOne($section);
            if ($rendered !== '') {
                $html[] = $rendered;
            }
        }

        return implode("\n", $html);
    }

    /**
     * @param array<string, mixed> $section
     */
    public function renderOne(array $section): string
    {
        if (array_key_exists('visible', $section) && $section['visible'] === false) {
            return '';
        }

        $type = is_string($section['type'] ?? null) ? $section['type'] : '';
        $variant = is_string($section['variant'] ?? null) ? $section['variant'] : 'default';
        if ($type === '') {
            return '';
        }

        $template = $this->resolveTemplate($type, $variant);
        if ($template === null) {
            if ($this->isDev) {
                return '<!-- section missing: ' . htmlspecialchars($type . '/' . $variant, ENT_QUOTES) . ' -->';
            }

            return '';
        }

        $data = $this->buildTemplateData($section);

        return $this->view->renderString($template, $data);
    }

    /**
     * @return list<array{type: string, variant: string}>
     */
    public function extractSectionRefs(array $sections): array
    {
        $refs = [];
        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }
            $type = is_string($section['type'] ?? null) ? $section['type'] : '';
            $variant = is_string($section['variant'] ?? null) ? $section['variant'] : 'default';
            if ($type !== '') {
                $refs[] = ['type' => $type, 'variant' => $variant];
            }
        }

        return $refs;
    }

    private function resolveTemplate(string $type, string $variant): ?string
    {
        $safeType = $this->safeName($type);
        $safeVariant = $this->safeName($variant);
        $candidates = [
            $this->sectionsDir . '/' . $safeType . '/' . $safeVariant . '.html',
            $this->sectionsDir . '/' . $safeType . '/default.html',
        ];

        foreach ($candidates as $file) {
            if (!is_file($file)) {
                continue;
            }
            $content = file_get_contents($file);

            return $content !== false ? $content : null;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $section
     *
     * @return array<string, mixed>
     */
    private function buildTemplateData(array $section): array
    {
        $data = [];
        $data['section_id'] = is_string($section['id'] ?? null) ? $section['id'] : '';
        $data['type'] = is_string($section['type'] ?? null) ? $section['type'] : '';
        $data['variant'] = is_string($section['variant'] ?? null) ? $section['variant'] : 'default';

        $content = is_array($section['content'] ?? null) ? $section['content'] : [];
        foreach ($content as $key => $value) {
            if (is_scalar($value)) {
                $data['content_' . $key] = (string) $value;
            }
        }

        $style = is_array($section['style'] ?? null) ? $section['style'] : [];
        foreach ($style as $key => $value) {
            if (is_scalar($value)) {
                $data['style_' . $key] = (string) $value;
            }
        }

        if (isset($content['items']) && is_array($content['items'])) {
            $data['items_html'] = $this->renderItems($content['items']);
        }

        $data['buttons_html'] = $this->resolveButtonsHtml($content);

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private function resolveButtonsHtml(array $content): string
    {
        if (isset($content['buttons']) && is_array($content['buttons'])) {
            return $this->renderButtons($content['buttons']);
        }

        // Compatibilité ascendante : anciennes sections enregistrées avant l'introduction
        // du répéteur de boutons (un seul lien plat en contenu).
        $legacyLabel = $content['cta_label'] ?? $content['button_label'] ?? null;
        $legacyHref = $content['cta_href'] ?? $content['button_href'] ?? null;
        if ($legacyLabel !== null || $legacyHref !== null) {
            return $this->renderButtons([[
                'label' => $legacyLabel ?? '',
                'href' => $legacyHref ?? '',
                'style' => 'primary',
            ]]);
        }

        return '';
    }

    /**
     * @param list<mixed> $buttons
     */
    private function renderButtons(array $buttons): string
    {
        $parts = [];
        foreach ($buttons as $button) {
            if (!is_array($button)) {
                continue;
            }
            $label = trim((string) ($button['label'] ?? ''));
            $href = trim((string) ($button['href'] ?? ''));
            if ($label === '' || $href === '') {
                continue;
            }
            $style = ($button['style'] ?? 'primary') === 'secondary' ? 'secondary' : 'primary';
            $parts[] = '<a class="section-button section-button--' . $style . '" href="'
                . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a>';
        }

        return implode("\n", $parts);
    }

    /**
     * @param list<mixed> $items
     */
    private function renderItems(array $items): string
    {
        $parts = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $title = htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $text = htmlspecialchars((string) ($item['text'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $parts[] = '<article class="section-features__item"><h3>' . $title . '</h3><p>' . $text . '</p></article>';
        }

        return implode("\n", $parts);
    }

    private function safeName(string $name): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $name) ?? '';

        return $sanitized !== '' ? $sanitized : 'default';
    }
}

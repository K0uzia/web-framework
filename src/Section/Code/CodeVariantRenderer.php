<?php

declare(strict_types=1);

namespace Capsule\Section\Code;

/**
 * Rendu HTML spécifique aux variantes code (conversion des blocs React).
 */
final class CodeVariantRenderer
{
    private const MAX_SNIPPETS = 6;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        if ($variant === 'codeexample1') {
            $sectionId = trim((string) ($data['section_id'] ?? 'code'));
            $safeSectionId = preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) ?: 'code';
            $data['code_panel_html'] = self::panelHtml($content, $safeSectionId);
            $data['buttons_html'] = self::primaryButtonWithArrow($content) ?: ($data['buttons_html'] ?? '');
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function panelHtml(array $content, string $sectionId): string
    {
        $snippets = self::snippets($content);
        if ($snippets === []) {
            return '';
        }

        $groupName = 'code-lang-' . $sectionId;
        $html = '<div class="section-code__switcher--codeexample1" data-code-example>';

        foreach ($snippets as $index => $snippet) {
            $inputId = $groupName . '-' . $index;
            $checked = $index === 0 ? ' checked' : '';
            $html .= '<input class="section-code__tab-input--codeexample1 visually-hidden" type="radio"'
                . ' name="' . htmlspecialchars($groupName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"'
                . ' id="' . htmlspecialchars($inputId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"'
                . $checked
                . ' />';
        }

        $html .= '<div class="section-code__tablist--codeexample1" role="tablist" aria-label="Langages de programmation">';
        foreach ($snippets as $index => $snippet) {
            $inputId = $groupName . '-' . $index;
            $html .= '<label class="section-code__tab-label--codeexample1" role="tab"'
                . ' for="' . htmlspecialchars($inputId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                . htmlspecialchars($snippet['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</label>';
        }
        $html .= '</div>';

        foreach ($snippets as $index => $snippet) {
            $html .= '<div class="section-code__panel--codeexample1 section-code__panel--codeexample1-' . $index
                . '" data-code-panel role="tabpanel">';
            $html .= '<div class="section-code__panel-head--codeexample1">'
                . '<span class="section-code__filename--codeexample1">'
                . htmlspecialchars($snippet['filename'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</span>'
                . '<button type="button" class="section-code__copy--codeexample1" data-code-copy'
                . ' aria-label="Copier le code">'
                . '<i class="fa-regular fa-copy" aria-hidden="true"></i>'
                . '</button>'
                . '</div>'
                . '<pre class="section-code__pre--codeexample1"><code class="section-code__code--codeexample1'
                . ' language-' . htmlspecialchars($snippet['language'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '">'
                . htmlspecialchars($snippet['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</code></pre>'
                . '</div>';
        }

        return $html . '</div>';
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array{label: string, filename: string, language: string, code: string}>
     */
    private static function snippets(array $content): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $snippets = [];
        foreach (array_slice(array_values(array_filter($raw, 'is_array')), 0, self::MAX_SNIPPETS) as $index => $item) {
            $code = (string) ($item['text'] ?? '');
            if (trim($code) === '') {
                continue;
            }
            $label = trim((string) ($item['label'] ?? ''));
            $filename = trim((string) ($item['title'] ?? ''));
            $language = trim((string) ($item['icon'] ?? $item['group'] ?? ''));
            if ($label === '') {
                $label = 'Snippet ' . ($index + 1);
            }
            if ($filename === '') {
                $filename = 'snippet.' . ($language !== '' ? $language : 'txt');
            }
            if ($language === '') {
                $language = 'text';
            }

            $snippets[] = [
                'label' => $label,
                'filename' => $filename,
                'language' => $language,
                'code' => $code,
            ];
        }

        return $snippets;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function primaryButtonWithArrow(array $content): string
    {
        $buttons = $content['buttons'] ?? null;
        if (!is_array($buttons)) {
            $label = trim((string) ($content['cta_label'] ?? $content['button_label'] ?? ''));
            $href = trim((string) ($content['cta_href'] ?? $content['button_href'] ?? ''));
            if ($label === '' || $href === '') {
                return '';
            }

            return self::buttonHtml($label, $href);
        }

        foreach ($buttons as $button) {
            if (!is_array($button)) {
                continue;
            }
            if (($button['style'] ?? 'primary') === 'secondary') {
                continue;
            }
            $label = trim((string) ($button['label'] ?? ''));
            $href = trim((string) ($button['href'] ?? ''));
            if ($label === '' || $href === '') {
                continue;
            }

            return self::buttonHtml($label, $href);
        }

        return '';
    }

    private static function buttonHtml(string $label, string $href): string
    {
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeHref = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<a class="section-button section-button--primary" href="' . $safeHref . '">'
            . $safeLabel
            . ' <i class="fa-solid fa-arrow-up-right" aria-hidden="true"></i></a>';
    }
}

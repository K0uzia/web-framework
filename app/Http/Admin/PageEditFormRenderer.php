<?php

declare(strict_types=1);

namespace App\Http\Admin;

use Capsule\ClientDashboardConfig;
use Capsule\MediaLibrary;
use Capsule\MediaRepository;
use Capsule\Page;
use Capsule\Section\SectionFieldSchema;
use Capsule\Section\Support\SectionButtonStyle;
use Capsule\SectionRegistry;

/**
 * Formulaire d'édition client : uniquement les champs autorisés dans client_dashboard.
 * Les noms de champs sont préfixés par section pour éviter les collisions.
 */
final class PageEditFormRenderer
{
    public function __construct(
        private readonly SectionRegistry $registry,
        private readonly SectionFieldSchema $fieldSchema,
        private readonly MediaLibrary $mediaLibrary,
        private readonly MediaRepository $media,
    ) {
    }

    public static function fieldPrefix(string $sectionId): string
    {
        return 's_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) . '__';
    }

    /**
     * @param array{medias_enabled?: bool, pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     */
    public function render(Page $page, array $config): string
    {
        $parts = [];
        foreach ($page->sections as $section) {
            if (!is_array($section)) {
                continue;
            }
            $html = $this->renderSection($page->slug, $section, $config);
            if ($html !== '') {
                $parts[] = $html;
            }
        }

        if ($parts === []) {
            return '<p class="admin-empty-inline">Aucun champ autorisé sur cette page.</p>';
        }

        return '<div class="admin-edit-sections">' . implode('', $parts) . '</div>';
    }

    /**
     * @param array<string, mixed> $section
     * @param array{medias_enabled?: bool, pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     */
    private function renderSection(string $pageSlug, array $section, array $config): string
    {
        $sectionId = is_string($section['id'] ?? null) ? $section['id'] : '';
        $type = is_string($section['type'] ?? null) ? $section['type'] : '';
        $variant = is_string($section['variant'] ?? null) ? $section['variant'] : '';
        if ($sectionId === '' || $type === '') {
            return '';
        }

        $allowedKeys = ClientDashboardConfig::allowedFields($config, $pageSlug, $sectionId);
        if ($allowedKeys === []) {
            return '';
        }

        $defs = $this->fieldSchema->contentFieldsForVariant($type, $variant);
        $fields = [];
        foreach ($allowedKeys as $key) {
            if (isset($defs[$key]) && is_array($defs[$key])) {
                $fields[$key] = $defs[$key];
            }
        }
        if ($fields === []) {
            return '';
        }

        $content = is_array($section['content'] ?? null) ? $section['content'] : [];
        $typeDef = $this->registry->getTypeDefinition($type);
        $typeLabel = is_string($typeDef['label'] ?? null) ? $typeDef['label'] : $type;
        $variants = $this->registry->getVariants($type);
        $variantLabel = is_string($variants[$variant]['label'] ?? null) ? $variants[$variant]['label'] : $variant;
        $mediasEnabled = ClientDashboardConfig::isMediasEnabled($config);
        $prefix = self::fieldPrefix($sectionId);

        $fieldsHtml = [];
        foreach ($fields as $key => $field) {
            $fieldsHtml[] = $this->renderField(
                $sectionId,
                $prefix,
                $key,
                $field,
                $content,
                $mediasEnabled,
            );
        }

        $safeId = htmlspecialchars($sectionId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $title = htmlspecialchars($typeLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $meta = htmlspecialchars($variantLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<section class="admin-edit-section" aria-labelledby="admin-sec-' . $safeId . '">'
            . '<header class="admin-edit-section__head">'
            . '<h2 id="admin-sec-' . $safeId . '">' . $title . '</h2>'
            . '<p class="admin-edit-section__meta">' . $meta . '</p>'
            . '</header>'
            . '<div class="admin-edit-section__fields">' . implode('', $fieldsHtml) . '</div>'
            . '</section>';
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $content
     */
    private function renderField(
        string $sectionId,
        string $prefix,
        string $key,
        array $field,
        array $content,
        bool $mediasEnabled,
    ): string {
        $type = (string) ($field['type'] ?? 'text');
        $label = (string) ($field['label'] ?? $key);

        if ($type === 'buttons') {
            return $this->renderButtons($sectionId, $prefix, $label, $content);
        }
        if ($type === 'repeater') {
            return $this->renderRepeater($sectionId, $prefix, $key, $label, $field, $content, $mediasEnabled);
        }
        if ($type === 'image' || $type === 'video') {
            return $this->renderMedia($sectionId, $prefix, $key, $label, $type, $content, $mediasEnabled);
        }
        if ($type === 'textarea') {
            return $this->input($sectionId, $prefix, $key, $label, (string) ($content[$key] ?? ''), 'textarea');
        }
        if ($type === 'select') {
            return $this->renderSelect($sectionId, $prefix, $key, $label, $field, (string) ($content[$key] ?? ''));
        }

        $inputType = $type === 'url' ? 'url' : 'text';

        return $this->input($sectionId, $prefix, $key, $label, (string) ($content[$key] ?? ''), $inputType);
    }

    /**
     * @param array<string, mixed> $content
     */
    private function renderButtons(string $sectionId, string $prefix, string $label, array $content): string
    {
        $buttons = is_array($content['buttons'] ?? null) ? array_values($content['buttons']) : [];
        $rows = [];
        foreach ($buttons as $i => $button) {
            $rows[] = $this->buttonRow($sectionId, $prefix, (string) $i, is_array($button) ? $button : []);
        }
        if ($rows === []) {
            $rows[] = $this->buttonRow($sectionId, $prefix, '0', []);
        }

        return '<fieldset class="admin-edit-fieldset">'
            . '<legend>' . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</legend>'
            . '<input type="hidden" name="' . htmlspecialchars($prefix . 'content_buttons_count', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" value="' . count($rows) . '" />'
            . '<div class="admin-edit-repeater">' . implode('', $rows) . '</div>'
            . '</fieldset>';
    }

    /**
     * @param array<string, mixed> $button
     */
    private function buttonRow(string $sectionId, string $prefix, string $index, array $button): string
    {
        $label = (string) ($button['label'] ?? '');
        $href = (string) ($button['href'] ?? '');
        $style = SectionButtonStyle::normalize((string) ($button['style'] ?? 'primary'));
        $domId = 's' . preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) . '-btn' . $index;

        return '<div class="admin-edit-repeater__row">'
            . $this->namedInput($domId . '-label', $prefix . 'content_buttons_' . $index . '_label', 'Libellé', $label, 'text')
            . $this->namedInput($domId . '-href', $prefix . 'content_buttons_' . $index . '_href', 'Lien', $href, 'text')
            . '<div class="admin-field"><label class="admin-label" for="' . htmlspecialchars($domId . '-style', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">Style</label>'
            . '<select class="admin-input" id="' . htmlspecialchars($domId . '-style', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" name="' . htmlspecialchars($prefix . 'content_buttons_' . $index . '_style', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
            . '<option value="primary"' . ($style === 'primary' ? ' selected' : '') . '>Principal</option>'
            . '<option value="secondary"' . ($style === 'secondary' ? ' selected' : '') . '>Secondaire</option>'
            . '<option value="outline"' . ($style === 'outline' ? ' selected' : '') . '>Contour</option>'
            . '</select></div>'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $content
     */
    private function renderRepeater(
        string $sectionId,
        string $prefix,
        string $key,
        string $label,
        array $field,
        array $content,
        bool $mediasEnabled,
    ): string {
        $subFields = is_array($field['fields'] ?? null) ? $field['fields'] : [];
        $items = is_array($content[$key] ?? null) ? array_values($content[$key]) : [];
        if ($items === []) {
            $items = [[]];
        }

        $rows = [];
        foreach ($items as $i => $item) {
            if (!is_array($item)) {
                $item = [];
            }
            $cells = [];
            foreach ($subFields as $subKey => $subDef) {
                if (!is_array($subDef)) {
                    continue;
                }
                $subLabel = (string) ($subDef['label'] ?? $subKey);
                $subType = (string) ($subDef['type'] ?? 'text');
                $name = $prefix . 'content_' . $key . '_' . $i . '_' . $subKey;
                $id = 's' . preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) . '-' . $key . '-' . $i . '-' . $subKey;
                $value = (string) ($item[$subKey] ?? '');
                if (($subType === 'image' || $subType === 'video') && $mediasEnabled) {
                    $cells[] = $this->mediaSelect($id, $name, $subLabel, $subType, $value);
                } else {
                    $inputType = $subType === 'textarea' ? 'textarea' : ($subType === 'url' ? 'url' : 'text');
                    $cells[] = $this->namedInput($id, $name, $subLabel, $value, $inputType);
                }
            }
            $rows[] = '<div class="admin-edit-repeater__row">' . implode('', $cells) . '</div>';
        }

        return '<fieldset class="admin-edit-fieldset">'
            . '<legend>' . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</legend>'
            . '<div class="admin-edit-repeater">' . implode('', $rows) . '</div>'
            . '</fieldset>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private function renderMedia(
        string $sectionId,
        string $prefix,
        string $key,
        string $label,
        string $kind,
        array $content,
        bool $mediasEnabled,
    ): string {
        $value = (string) ($content[$key] ?? '');
        $id = 's' . preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) . '-' . $key;
        $name = $prefix . 'content_' . $key;

        if ($mediasEnabled) {
            return $this->mediaSelect($id, $name, $label, $kind, $value);
        }

        return $this->namedInput($id, $name, $label, $value, 'url')
            . '<p class="admin-hint">Collez une URL d\'image ou de vidéo. Activez les médias client dans /dev pour choisir depuis la bibliothèque.</p>';
    }

    private function mediaSelect(string $id, string $name, string $label, string $kind, string $value): string
    {
        $urls = $kind === 'video'
            ? $this->clientMediaUrls('video')
            : $this->clientMediaUrls('image');

        $safeId = htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $options = '<option value="">Choisir…</option>';
        if ($value !== '' && !in_array($value, $urls, true)) {
            $options .= '<option value="' . $safeValue . '" selected>Actuel</option>';
        }
        foreach ($urls as $url) {
            $sel = $url === $value ? ' selected' : '';
            $options .= '<option value="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' . $sel . '>'
                . htmlspecialchars(basename($url), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</option>';
        }

        return '<div class="admin-field">'
            . '<label class="admin-label" for="' . $safeId . '">' . $safeLabel . '</label>'
            . '<select class="admin-input" id="' . $safeId . '" name="' . $safeName . '">' . $options . '</select>'
            . '<p class="admin-hint"><a href="/admin/medias">Gérer la bibliothèque médias</a></p>'
            . '</div>';
    }

    /**
     * @return list<string>
     */
    private function clientMediaUrls(string $kind): array
    {
        $urls = [];
        foreach ($this->media->all($kind) as $record) {
            $url = (string) ($record['url'] ?? '');
            if ($url === '' || $this->mediaLibrary->isBundledAsset($url)) {
                continue;
            }
            $urls[] = $url;
        }

        return $urls;
    }

    /**
     * @param array<string, mixed> $field
     */
    private function renderSelect(
        string $sectionId,
        string $prefix,
        string $key,
        string $label,
        array $field,
        string $value,
    ): string {
        $raw = $field['options'] ?? [];
        $options = [];
        if (is_string($raw)) {
            $trimmed = trim($raw, "[] \t\n\r");
            foreach (explode(',', $trimmed) as $opt) {
                $opt = trim($opt);
                if ($opt !== '') {
                    $options[$opt] = $opt;
                }
            }
        } elseif (is_array($raw)) {
            foreach ($raw as $k => $v) {
                if (is_string($v)) {
                    $options[is_string($k) ? $k : $v] = $v;
                }
            }
        }

        $id = 's' . preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) . '-' . $key;
        $safeId = htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = '<div class="admin-field"><label class="admin-label" for="' . $safeId . '">'
            . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</label>'
            . '<select class="admin-input" id="' . $safeId . '" name="'
            . htmlspecialchars($prefix . 'content_' . $key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
        foreach ($options as $optValue => $optLabel) {
            $sel = (string) $optValue === $value ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars((string) $optValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' . $sel . '>'
                . htmlspecialchars((string) $optLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</option>';
        }
        $html .= '</select></div>';

        return $html;
    }

    private function input(string $sectionId, string $prefix, string $key, string $label, string $value, string $type): string
    {
        $id = 's' . preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) . '-' . $key;

        return $this->namedInput($id, $prefix . 'content_' . $key, $label, $value, $type);
    }

    private function namedInput(string $id, string $name, string $label, string $value, string $type): string
    {
        $safeId = htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if ($type === 'textarea') {
            return '<div class="admin-field admin-field--full"><label class="admin-label" for="' . $safeId . '">' . $safeLabel . '</label>'
                . '<textarea class="admin-input admin-textarea" id="' . $safeId . '" name="' . $safeName . '" rows="4">'
                . $safeValue . '</textarea></div>';
        }

        return '<div class="admin-field"><label class="admin-label" for="' . $safeId . '">' . $safeLabel . '</label>'
            . '<input class="admin-input" id="' . $safeId . '" type="' . htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" name="' . $safeName . '" value="' . $safeValue . '" /></div>';
    }
}

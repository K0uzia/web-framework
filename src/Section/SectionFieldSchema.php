<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\SectionRegistry;
use Capsule\YamlData;

final class SectionFieldSchema
{
    /** @var array<string, array<string, array<string, mixed>>> */
    private array $variantOverrides = [];

    public function __construct(
        private readonly SectionRegistry $registry,
        string $variantOverridesFile = '',
    ) {
        if ($variantOverridesFile !== '' && is_file($variantOverridesFile)) {
            $parsed = YamlData::loadFile($variantOverridesFile);
            $this->variantOverrides = is_array($parsed) ? $parsed : [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function contentFieldsForVariant(string $type, string $variant): array
    {
        $fields = [];
        foreach ($this->registry->getContentFields($type) as $key => $field) {
            if (!is_array($field)) {
                continue;
            }
            $field = $this->applyVariantOverride($type, (string) $key, $field);
            if (!$this->fieldAppliesToVariant($field, $variant)) {
                continue;
            }
            $fields[$key] = $field;
        }

        return $fields;
    }

    /**
     * @return array<string, mixed>
     */
    public function styleFields(string $type): array
    {
        return $this->registry->getStyleFields($type);
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return array<string, string>
     */
    public function flattenContent(array $content): array
    {
        $flat = [];
        foreach ($content as $key => $value) {
            if (is_scalar($value)) {
                $flat['content_' . $key] = (string) $value;
            }
        }

        return $flat;
    }

    /**
     * @param array<string, string> $data
     * @param array<string, mixed> $existingContent
     *
     * @return array<string, mixed>
     */
    public function unflattenForm(array $data, array $existingContent, string $type, string $variant): array
    {
        $content = $existingContent;
        $allowed = array_keys($this->contentFieldsForVariant($type, $variant));

        foreach ($data as $key => $value) {
            if (!str_starts_with($key, 'content_')) {
                continue;
            }
            $field = substr($key, 8);
            if (preg_match('/^[a-z0-9_]+_\d+_/', $field)) {
                continue;
            }
            if ($allowed !== [] && !in_array($field, $allowed, true)) {
                continue;
            }
            $content[$field] = $value;
        }

        foreach ($this->repeaterKeysForVariant($type, $variant) as $repeaterKey) {
            if ($this->hasRepeaterDataForKey($data, $repeaterKey)) {
                $content[$repeaterKey] = $this->parseRepeaterByKey($data, $repeaterKey);
            }
        }

        if (array_key_exists('content_buttons_count', $data)) {
            $content['buttons'] = $this->parseButtonsRepeater($data);
        }

        if (array_key_exists('content_flip_words', $data)) {
            $content['flip_words'] = $this->parseFlipWords($data['content_flip_words']);
        }

        return $content;
    }

    /**
     * @return list<string>
     */
    private function parseFlipWords(string $raw): array
    {
        $words = [];
        foreach (preg_split('/\r\n|\n|\r|,/', $raw) ?: [] as $word) {
            $word = trim($word);
            if ($word !== '') {
                $words[] = $word;
            }
        }

        return $words;
    }

    /**
     * @param array<string, mixed> $field
     *
     * @return array<string, mixed>
     */
    private function applyVariantOverride(string $type, string $key, array $field): array
    {
        $override = $this->variantOverrides[$type][$key] ?? null;
        if (!is_array($override)) {
            return $field;
        }

        return array_merge($field, $override);
    }

    /**
     * @param array<string, mixed> $style
     *
     * @return array<string, string>
     */
    public function unflattenStyleForm(array $data, array $style, string $type): array
    {
        $resolved = $style;
        $allowed = array_keys($this->styleFields($type));

        foreach ($data as $key => $value) {
            if (!str_starts_with($key, 'style_')) {
                continue;
            }
            $field = substr($key, 6);
            if ($allowed !== [] && !in_array($field, $allowed, true)) {
                continue;
            }
            $resolved[$field] = $value;
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed> $field
     */
    public function fieldAppliesToVariant(array $field, string $variant): bool
    {
        $show = $this->normalizeVariantList($field['show_for_variants'] ?? $field['variants'] ?? null);
        if ($show !== null) {
            return in_array($variant, $show, true);
        }

        $hide = $this->normalizeVariantList($field['hide_for_variants'] ?? null);
        if ($hide !== null && in_array($variant, $hide, true)) {
            return false;
        }

        $label = (string) ($field['label'] ?? '');
        if (preg_match('/\(([a-zA-Z0-9_-]+)\)\s*$/', $label, $matches) === 1) {
            return $matches[1] === $variant;
        }

        return true;
    }

    /**
     * @return list<string>|null
     */
    private function normalizeVariantList(mixed $value): ?array
    {
        if (is_array($value)) {
            if ($value === []) {
                return null;
            }

            return array_values(array_map('strval', $value));
        }

        if (!is_string($value)) {
            return null;
        }

        $raw = trim($value);
        if ($raw === '') {
            return null;
        }

        if (str_starts_with($raw, '[') && str_ends_with($raw, ']')) {
            $raw = substr($raw, 1, -1);
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $raw)), static fn (string $part): bool => $part !== ''));

        return $parts !== [] ? $parts : null;
    }

    /**
     * @return list<string>
     */
    private function repeaterKeysForVariant(string $type, string $variant): array
    {
        $keys = [];
        foreach ($this->contentFieldsForVariant($type, $variant) as $key => $field) {
            if (!is_array($field) || ($field['type'] ?? '') !== 'repeater' || $key === 'buttons') {
                continue;
            }
            $keys[] = (string) $key;
        }

        return $keys;
    }

    /**
     * @param array<string, string> $data
     */
    private function hasRepeaterDataForKey(array $data, string $key): bool
    {
        $prefix = 'content_' . $key . '_';
        foreach ($data as $dataKey => $_) {
            if (str_starts_with($dataKey, $prefix) && preg_match('/^' . preg_quote($prefix, '/') . '\d+_/', $dataKey) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, string> $data
     *
     * @return list<array<string, string>>
     */
    private function parseRepeaterByKey(array $data, string $key): array
    {
        $pattern = '/^content_' . preg_quote($key, '/') . '_(\d+)_([a-zA-Z0-9_]+)$/';
        $grouped = [];
        foreach ($data as $dataKey => $value) {
            if (!preg_match($pattern, $dataKey, $m)) {
                continue;
            }
            $grouped[(int) $m[1]][$m[2]] = $value;
        }

        ksort($grouped);

        $items = [];
        foreach ($grouped as $item) {
            $hasContent = false;
            foreach ($item as $itemValue) {
                if (trim($itemValue) !== '') {
                    $hasContent = true;
                    break;
                }
            }
            if ($hasContent) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param array<string, string> $data
     *
     * @return list<array{label: string, href: string, style: string}>
     */
    private function parseButtonsRepeater(array $data): array
    {
        $buttons = [];
        $i = 0;
        while (array_key_exists('content_buttons_' . $i . '_label', $data) || array_key_exists('content_buttons_' . $i . '_href', $data)) {
            $label = trim($data['content_buttons_' . $i . '_label'] ?? '');
            $href = trim($data['content_buttons_' . $i . '_href'] ?? '');
            $style = \Capsule\Section\Support\SectionButtonStyle::normalize(
                (string) ($data['content_buttons_' . $i . '_style'] ?? 'primary'),
            );
            if ($label !== '' || $href !== '') {
                $buttons[] = ['label' => $label, 'href' => $href, 'style' => $style];
            }
            $i++;
        }

        return $buttons;
    }
}

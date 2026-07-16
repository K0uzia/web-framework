<?php

declare(strict_types=1);

namespace App\Http\Admin;

use Capsule\ClientDashboardConfig;
use Capsule\Page;
use Capsule\Section\SectionFieldSchema;

/**
 * Applique le POST admin : ne touche que les champs content autorisés.
 */
final class PageEditContentApplier
{
    public function __construct(
        private readonly SectionFieldSchema $fieldSchema,
    ) {
    }

    /**
     * @param array{pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     * @param array<string, string> $formData
     *
     * @return list<array<string, mixed>>
     */
    public function apply(Page $page, array $config, array $formData): array
    {
        $sections = $page->sections;

        foreach ($sections as $i => $section) {
            if (!is_array($section)) {
                continue;
            }
            $sectionId = is_string($section['id'] ?? null) ? $section['id'] : '';
            if ($sectionId === '') {
                continue;
            }

            $allowed = ClientDashboardConfig::allowedFields($config, $page->slug, $sectionId);
            if ($allowed === []) {
                continue;
            }

            $type = is_string($section['type'] ?? null) ? $section['type'] : '';
            $variant = is_string($section['variant'] ?? null) ? $section['variant'] : '';
            if ($type === '') {
                continue;
            }

            $prefix = PageEditFormRenderer::fieldPrefix($sectionId);
            $sectionData = $this->extractPrefixed($formData, $prefix);
            if ($sectionData === []) {
                continue;
            }

            $existing = is_array($section['content'] ?? null) ? $section['content'] : [];
            $parsed = $this->fieldSchema->unflattenForm($sectionData, $existing, $type, $variant);
            $sections[$i]['content'] = $this->mergeAllowed($existing, $parsed, $allowed);
        }

        return $sections;
    }

    /**
     * @param array<string, string> $formData
     *
     * @return array<string, string>
     */
    private function extractPrefixed(array $formData, string $prefix): array
    {
        $out = [];
        $len = strlen($prefix);
        foreach ($formData as $key => $value) {
            if (!is_string($key) || !str_starts_with($key, $prefix)) {
                continue;
            }
            $out[substr($key, $len)] = $value;
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $existing
     * @param array<string, mixed> $parsed
     * @param list<string> $allowed
     *
     * @return array<string, mixed>
     */
    private function mergeAllowed(array $existing, array $parsed, array $allowed): array
    {
        $content = $existing;
        foreach ($allowed as $field) {
            if (array_key_exists($field, $parsed)) {
                $content[$field] = $parsed[$field];
            }
        }

        return $content;
    }
}

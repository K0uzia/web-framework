<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Document JSON client_dashboard (permissions /admin).
 *
 * {
 *   "medias_enabled": true,
 *   "site_enabled": true,
 *   "pages": {
 *     "contact": {
 *       "sections": {
 *         "hero-a1b2c3": { "fields": ["title", "subtitle"] }
 *       }
 *     }
 *   }
 * }
 */
final class ClientDashboardConfig
{
    public const FORM_FIELD_PREFIX = 'cd:';
    public const FORM_MEDIAS_KEY = 'cd_medias';
    public const FORM_SITE_KEY = 'cd_site';

    /**
     * @return array{medias_enabled: bool, site_enabled: bool, pages: array<string, array{sections: array<string, array{fields: list<string>}>}>}
     */
    public static function empty(): array
    {
        return ['medias_enabled' => false, 'site_enabled' => true, 'pages' => []];
    }

    /**
     * @param array<string, mixed> $raw
     *
     * @return array{medias_enabled: bool, site_enabled: bool, pages: array<string, array{sections: array<string, array{fields: list<string>}>}>}
     */
    public static function normalize(array $raw): array
    {
        $pagesIn = is_array($raw['pages'] ?? null) ? $raw['pages'] : [];
        $pages = [];

        foreach ($pagesIn as $slug => $page) {
            if (!is_string($slug) || !is_array($page)) {
                continue;
            }
            $sectionsIn = is_array($page['sections'] ?? null) ? $page['sections'] : [];
            $sections = [];
            foreach ($sectionsIn as $sectionId => $section) {
                if (!is_string($sectionId) || $sectionId === '' || !is_array($section)) {
                    continue;
                }
                $fields = self::normalizeFieldList($section['fields'] ?? null);
                if ($fields === []) {
                    continue;
                }
                $sections[$sectionId] = ['fields' => $fields];
            }
            if ($sections === []) {
                continue;
            }
            $pages[$slug] = ['sections' => $sections];
        }

        return [
            'medias_enabled' => self::isTruthy($raw['medias_enabled'] ?? false),
            // Absent = activé (configs existantes et espace client livré avec identité).
            'site_enabled' => array_key_exists('site_enabled', $raw)
                ? self::isTruthy($raw['site_enabled'])
                : true,
            'pages' => $pages,
        ];
    }

    /**
     * @param array<string, string>                                    $formData
     * @param array<string, array{type: string, fields: list<string>}> $sectionIndex
     * @param list<string>                                             $knownSlugs
     *
     * @return array{medias_enabled: bool, site_enabled: bool, pages: array<string, array{sections: array<string, array{fields: list<string>}>}>}
     */
    public static function fromFormData(array $formData, array $sectionIndex, array $knownSlugs): array
    {
        $knownSlugSet = array_fill_keys($knownSlugs, true);
        $pages = [];

        foreach ($formData as $key => $value) {
            if ($value !== '1' || !str_starts_with($key, self::FORM_FIELD_PREFIX)) {
                continue;
            }
            $parsed = self::parseFormFieldKey($key);
            if ($parsed === null) {
                continue;
            }
            [$slug, $sectionId, $field] = $parsed;
            if (!isset($knownSlugSet[$slug])) {
                continue;
            }
            $meta = $sectionIndex[$sectionId] ?? null;
            if ($meta === null) {
                continue;
            }
            if (!in_array($field, $meta['fields'], true)) {
                continue;
            }
            $pages[$slug]['sections'][$sectionId]['fields'][$field] = true;
        }

        $out = [];
        foreach ($pages as $slug => $page) {
            $sections = [];
            foreach ($page['sections'] as $sectionId => $section) {
                $fields = array_keys($section['fields']);
                sort($fields);
                $sections[$sectionId] = ['fields' => $fields];
            }
            if ($sections !== []) {
                $out[$slug] = ['sections' => $sections];
            }
        }

        return [
            'medias_enabled' => ($formData[self::FORM_MEDIAS_KEY] ?? '') === '1',
            'site_enabled' => ($formData[self::FORM_SITE_KEY] ?? '') === '1',
            'pages' => $out,
        ];
    }

    public static function formFieldKey(string $slug, string $sectionId, string $field): string
    {
        return self::FORM_FIELD_PREFIX . $slug . ':' . $sectionId . ':' . $field;
    }

    /**
     * @return array{0: string, 1: string, 2: string}|null
     */
    public static function parseFormFieldKey(string $key): ?array
    {
        if (!str_starts_with($key, self::FORM_FIELD_PREFIX)) {
            return null;
        }
        $rest = substr($key, strlen(self::FORM_FIELD_PREFIX));
        $parts = explode(':', $rest, 3);
        if (count($parts) !== 3) {
            return null;
        }
        [$slug, $sectionId, $field] = $parts;
        if ($sectionId === '' || $field === '') {
            return null;
        }

        return [$slug, $sectionId, $field];
    }

    /**
     * @param array{medias_enabled?: bool, site_enabled?: bool, pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     */
    public static function isMediasEnabled(array $config): bool
    {
        return ($config['medias_enabled'] ?? false) === true;
    }

    /**
     * @param array{medias_enabled?: bool, site_enabled?: bool, pages?: array<string, mixed>} $config
     */
    public static function isSiteEnabled(array $config): bool
    {
        return ($config['site_enabled'] ?? true) === true;
    }

    /**
     * @param array{pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     */
    public static function isPageEditable(array $config, string $slug): bool
    {
        $sections = $config['pages'][$slug]['sections'] ?? null;

        return is_array($sections) && $sections !== [];
    }

    /**
     * @param array{pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     *
     * @return list<string>
     */
    public static function allowedFields(array $config, string $slug, string $sectionId): array
    {
        $fields = $config['pages'][$slug]['sections'][$sectionId]['fields'] ?? null;

        return is_array($fields) ? array_values($fields) : [];
    }

    /**
     * @param array{pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     */
    public static function isFieldAllowed(array $config, string $slug, string $sectionId, string $field): bool
    {
        return in_array($field, self::allowedFields($config, $slug, $sectionId), true);
    }

    private static function isTruthy(mixed $value): bool
    {
        if ($value === true || $value === 1) {
            return true;
        }
        if (!is_string($value)) {
            return false;
        }

        return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @return list<string>
     */
    private static function normalizeFieldList(mixed $fields): array
    {
        if (!is_array($fields)) {
            return [];
        }
        $out = [];
        foreach ($fields as $field) {
            if (is_string($field) && $field !== '') {
                $out[$field] = true;
            }
        }

        return array_keys($out);
    }
}

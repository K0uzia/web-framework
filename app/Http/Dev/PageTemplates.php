<?php

declare(strict_types=1);

namespace App\Http\Dev;

use App\Http\Dev\Sections\SectionDefaults;

/**
 * Modèles de pages préassemblés (compositions de sections).
 */
final class PageTemplates
{
    /**
     * @return list<array{id: string, label: string, description: string, icon: string}>
     */
    public static function all(): array
    {
        return array_map(static fn (array $def): array => [
            'id' => $def['id'],
            'label' => $def['label'],
            'description' => $def['description'],
            'icon' => $def['icon'],
        ], self::definitions());
    }

    /**
     * @return list<array{id: string, category: string, label: string, description: string, icon: string, slug: string, title: string, seo: string, publish: bool}>
     */
    public static function definitions(): array
    {
        return PageTemplatesCatalog::definitions();
    }

    /**
     * @return list<string>
     */
    public static function categories(): array
    {
        return PageTemplatesCatalog::categories();
    }

    /**
     * @return array{id: string, category: string, label: string, description: string, icon: string, slug: string, title: string, seo: string, publish: bool}|null
     */
    public static function find(string $templateId): ?array
    {
        return PageTemplatesCatalog::find($templateId);
    }

    public static function presetTitle(string $templateId): string
    {
        return self::find($templateId)['title'] ?? '';
    }

    public static function presetDescription(string $templateId): string
    {
        return self::find($templateId)['seo'] ?? '';
    }

    public static function publishByDefault(string $templateId): bool
    {
        return (self::find($templateId)['publish'] ?? false) === true;
    }

    public static function resolveSlug(string $templateId, bool $homeExists): string
    {
        $def = self::find($templateId);
        if ($def === null || $templateId === 'blank') {
            return '';
        }

        $slug = $def['slug'];
        if ($slug === '' && str_starts_with($templateId, 'landing-') && $homeExists) {
            return 'accueil-alt';
        }

        if ($slug === '' && !str_starts_with($templateId, 'landing-')) {
            return PageSlug::fromTitle($def['title']);
        }

        return $slug;
    }

    public static function presetsJson(): string
    {
        $presets = [];
        foreach (self::definitions() as $def) {
            $presets[$def['id']] = [
                'title' => $def['title'],
                'slug' => $def['slug'],
                'description' => $def['seo'],
                'hint' => $def['description'],
                'publish' => $def['publish'],
            ];
        }

        return json_encode($presets, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function sections(string $templateId): array
    {
        $refs = PageTemplatesCatalog::sectionRefs($templateId);
        if ($refs === []) {
            return [];
        }

        $sections = [];
        foreach ($refs as $ref) {
            $sections[] = [
                'id' => $ref['type'] . '-' . bin2hex(random_bytes(3)),
                'type' => $ref['type'],
                'variant' => $ref['variant'],
                'visible' => true,
                'content' => SectionDefaults::content($ref['type'], $ref['variant']),
                'style' => SectionDefaults::style($ref['type']),
            ];
        }

        return $sections;
    }

    public static function buildOptionsHtml(string $selected = 'blank'): string
    {
        $options = [];
        foreach (self::all() as $template) {
            $id = $template['id'];
            $options[] = '<option value="' . htmlspecialchars($id, ENT_QUOTES) . '"'
                . ($id === $selected ? ' selected' : '') . '>'
                . htmlspecialchars($template['label'], ENT_QUOTES) . '</option>';
        }

        return implode('', $options);
    }
}

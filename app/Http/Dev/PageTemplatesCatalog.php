<?php

declare(strict_types=1);

namespace App\Http\Dev;

/**
 * Catalogue des modèles de page (compositions de sections, conversion progressive).
 */
final class PageTemplatesCatalog
{
    /** @var list<array{id: string, category: string, label: string, description: string, icon: string, slug: string, title: string, seo: string, publish: bool, sections: list<array{type: string, variant: string}>}>|null */
    private static ?array $cache = null;

    /**
     * @return list<array{id: string, category: string, label: string, description: string, icon: string, slug: string, title: string, seo: string, publish: bool}>
     */
    public static function definitions(): array
    {
        return array_map(static fn (array $t): array => [
            'id' => $t['id'],
            'category' => $t['category'],
            'label' => $t['label'],
            'description' => $t['description'],
            'icon' => $t['icon'],
            'slug' => $t['slug'],
            'title' => $t['title'],
            'seo' => $t['seo'],
            'publish' => $t['publish'],
        ], self::all());
    }

    /**
     * @return list<string>
     */
    public static function categories(): array
    {
        $cats = [];
        foreach (self::all() as $t) {
            $cats[$t['category']] = true;
        }

        return array_keys($cats);
    }

    /**
     * @return list<array{type: string, variant: string}>
     */
    public static function sectionRefs(string $templateId): array
    {
        foreach (self::all() as $t) {
            if ($t['id'] === $templateId) {
                return $t['sections'];
            }
        }

        return [];
    }

    /**
     * @return array{id: string, category: string, label: string, description: string, icon: string, slug: string, title: string, seo: string, publish: bool, sections: list<array{type: string, variant: string}>}|null
     */
    public static function find(string $id): ?array
    {
        foreach (self::all() as $t) {
            if ($t['id'] === $id) {
                return $t;
            }
        }

        return null;
    }

    /**
     * @return list<array{id: string, category: string, label: string, description: string, icon: string, slug: string, title: string, seo: string, publish: bool, sections: list<array{type: string, variant: string}>}>
     */
    private static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        self::$cache = [
            self::tpl('blank', 'starter', 'Page vide', 'Page sans bloc, à composer depuis le dashboard.', 'fa-solid fa-file', '', '', '', [], false),
            self::tpl(
                'landing-hero3',
                'landing',
                'Landing Hero 3',
                'Page d\'accueil avec le hero 3 (split et avis).',
                'fa-solid fa-rocket',
                '',
                'Accueil',
                'Découvrez notre produit et commencez en quelques minutes.',
                [['hero', 'hero3']],
            ),
        ];

        return self::$cache;
    }

    /**
     * @param list<array{0: string, 1: string}> $sections
     *
     * @return array{id: string, category: string, label: string, description: string, icon: string, slug: string, title: string, seo: string, publish: bool, sections: list<array{type: string, variant: string}>}
     */
    private static function tpl(
        string $id,
        string $category,
        string $label,
        string $description,
        string $icon,
        string $slug,
        string $title,
        string $seo,
        array $sections,
        bool $publish = true,
    ): array {
        $refs = [];
        foreach ($sections as $pair) {
            $refs[] = ['type' => $pair[0], 'variant' => $pair[1]];
        }

        return [
            'id' => $id,
            'category' => $category,
            'label' => $label,
            'description' => $description,
            'icon' => $icon,
            'slug' => $slug,
            'title' => $title,
            'seo' => $seo,
            'publish' => $publish,
            'sections' => $refs,
        ];
    }
}

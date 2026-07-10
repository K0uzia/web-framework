<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait BlogDefaults
{
    private static function blogContent(string $variant): array
    {
        return match ($variant) {
            'blog8' => [
                'title' => 'Articles de blog',
                'subtitle' => 'Découvrez nos derniers articles sur le développement web moderne, le design UI et l\'architecture par composants.',
                'items' => self::blog8Items(),
            ],
            default => [
                'tagline' => 'Dernières actualités',
                'title' => 'Blog',
                'subtitle' => 'Découvrez les dernières tendances, astuces et bonnes pratiques du développement web moderne. Des composants UI aux systèmes de design, restez informé avec nos analyses.',
                'items' => self::blog7Items(),
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function blog7Items(): array
    {
        $images = [
            SectionAssets::shared(self::FEATURES_SHARED, 'lummi/bw12.jpeg'),
            SectionAssets::shared(self::FEATURES_SHARED, 'lummi/bw15.jpeg'),
            SectionAssets::shared(self::FEATURES_SHARED, 'lummi/bw20.jpeg'),
        ];

        return [
            [
                'title' => 'Premiers pas avec shadcn/ui',
                'text' => 'Apprenez à intégrer et personnaliser rapidement les composants shadcn/ui dans vos projets. Installation, thème et bonnes pratiques pour des interfaces modernes.',
                'author' => 'Sarah Chen',
                'published' => '1 janv. 2024',
                'href' => '#',
                'url' => $images[0],
            ],
            [
                'title' => 'Créer des applications web accessibles',
                'text' => 'Découvrez comment concevoir des expériences inclusives avec des composants accessibles. Conseils pratiques sur les libellés ARIA, la navigation clavier et le HTML sémantique.',
                'author' => 'Marcus Rodriguez',
                'published' => '1 janv. 2024',
                'href' => '#',
                'url' => $images[1],
            ],
            [
                'title' => 'Systèmes de design avec Tailwind CSS',
                'text' => 'Plongez dans la création de systèmes de design évolutifs avec Tailwind CSS et shadcn/ui. Maintenez la cohérence tout en gardant des bibliothèques flexibles.',
                'author' => 'Emma Thompson',
                'published' => '1 janv. 2024',
                'href' => '#',
                'url' => $images[2],
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function blog8Items(): array
    {
        $image = SectionAssets::shared(self::FEATURES_SHARED, 'lummi/bw12.jpeg');

        return [
            [
                'title' => 'Interfaces modernes : plongée dans shadcn et React',
                'text' => 'Une exploration approfondie de la création d\'interfaces modernes avec shadcn/ui et React. Bonnes pratiques et techniques avancées.',
                'author' => 'Sarah Chen',
                'published' => '15 févr. 2024',
                'label' => 'Web Design, Développement UI',
                'href' => '#',
                'url' => $image,
            ],
            [
                'title' => 'Maîtriser Tailwind CSS : des bases aux techniques avancées',
                'text' => 'Découvrez comment exploiter toute la puissance de Tailwind CSS pour créer des sites beaux, responsives et maintenables.',
                'author' => 'Michael Park',
                'published' => '22 févr. 2024',
                'label' => 'Web Design, CSS',
                'href' => '#',
                'url' => $image,
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */}

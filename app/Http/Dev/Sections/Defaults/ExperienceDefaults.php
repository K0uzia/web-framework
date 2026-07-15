<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

use Capsule\SectionAssets;

trait ExperienceDefaults
{
    private static function experienceContent(string $variant): array
    {
        return match ($variant) {
            default => [
                'title' => 'Expérience',
                'link_label' => 'Télécharger le CV',
                'href' => '#',
                'items' => self::experience1Items(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function experience1Items(): array
    {
        $shared = 'experience';

        return [
            [
                'label' => 'sep. 2025 - aujourd\'hui',
                'title' => 'Ingénieur logiciel senior',
                'text' => 'Direction du développement d\'applications web évolutives avec React, TypeScript et Node.js. Mentorat des développeurs juniors et mise en place des bonnes pratiques.',
                'company' => 'Google',
                'logo' => SectionAssets::shared($shared, 'logos/google-icon.svg'),
            ],
            [
                'label' => 'mars 2023 - août 2025',
                'title' => 'Développeur full stack',
                'text' => 'Conception et maintenance de sites clients et plateformes e-commerce. Collaboration étroite avec les équipes design pour des interfaces fidèles aux maquettes.',
                'company' => 'Microsoft',
                'logo' => SectionAssets::shared($shared, 'logos/microsoft-icon.svg'),
            ],
            [
                'label' => 'janv. 2021 - févr. 2023',
                'title' => 'Développeur frontend',
                'text' => 'Développement d\'applications web responsives avec des frameworks JavaScript modernes. Optimisation des performances et de l\'accessibilité sur plusieurs projets.',
                'company' => 'Apple',
                'logo' => SectionAssets::shared($shared, 'logos/apple-icon.svg'),
            ],
            [
                'label' => 'juin 2019 - déc. 2020',
                'title' => 'Développeur junior',
                'text' => 'Participation à la création d\'applications web et apprentissage des pratiques modernes. Contribution aux projets d\'équipe et aux revues de code.',
                'company' => 'Netflix',
                'logo' => SectionAssets::shared($shared, 'logos/netflix-icon.svg'),
            ],
        ];
    }
}

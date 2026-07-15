<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

trait ListDefaults
{
    /**
     * @return array<string, mixed>
     */
    private static function listContent(string $variant): array
    {
        return match ($variant) {
            default => [
                'title' => 'Nos réalisations et distinctions',
                'read_more_label' => 'Voir le projet',
                'items' => self::list2Items(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function list2Items(): array
    {
        return [
            [
                'icon' => 'fa-star',
                'title' => 'Reconnaissance sectorielle',
                'label' => 'Distinction',
                'text' => 'Prix de la performance exceptionnelle.',
                'href' => '#',
            ],
            [
                'icon' => 'fa-circle-check',
                'title' => 'Prix d\'excellence',
                'label' => 'Récompense',
                'text' => 'Lauréat de la meilleure catégorie.',
                'href' => '#',
            ],
            [
                'icon' => 'fa-lightbulb',
                'title' => 'Prix de l\'innovation',
                'label' => 'Technologie',
                'text' => 'Solution révolutionnaire de l\'année.',
                'href' => '#',
            ],
            [
                'icon' => 'fa-handshake',
                'title' => 'Succès client',
                'label' => 'Service',
                'text' => 'Solution la mieux notée par nos clients.',
                'href' => '#',
            ],
            [
                'icon' => 'fa-briefcase',
                'title' => 'Leadership global',
                'label' => 'Management',
                'text' => 'Équipe dirigeante de l\'année.',
                'href' => '#',
            ],
            [
                'icon' => 'fa-leaf',
                'title' => 'Impact durable',
                'label' => 'Environnement',
                'text' => 'Excellence en initiative verte.',
                'href' => '#',
            ],
        ];
    }
}

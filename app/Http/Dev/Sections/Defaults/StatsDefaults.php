<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

trait StatsDefaults
{
    private static function statsContent(string $variant): array
    {
        return match ($variant) {
            'stats8' => [
                'title' => 'Insights sur les performances de la plateforme',
                'subtitle' => 'Stabilité et évolutivité pour tous les utilisateurs.',
                'link_label' => 'Lire le rapport d\'impact complet',
                'href' => 'https://www.shadcnblocks.com',
                'items' => self::stats8Items(),
            ],
            default => [
                'title' => 'Insights sur les performances de la plateforme',
                'buttons' => [
                    ['label' => 'Commencer', 'href' => '#', 'style' => 'primary'],
                    ['label' => 'En savoir plus', 'href' => '#', 'style' => 'secondary'],
                ],
                'items' => self::stats6Items(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function stats6Items(): array
    {
        return [
            ['title' => '90%', 'label' => 'Indicateur 1'],
            ['title' => '200+', 'label' => 'Indicateur 2'],
            ['title' => '99%', 'label' => 'Indicateur 3'],
            ['title' => '150+', 'label' => 'Indicateur 4'],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function stats8Items(): array
    {
        return [
            ['title' => '250%+', 'label' => 'croissance moyenne de l\'engagement utilisateur'],
            ['title' => '2,5 M€', 'label' => 'd\'économies annuelles par partenaire entreprise'],
            ['title' => '200+', 'label' => 'intégrations avec les principales plateformes'],
            ['title' => '99,9 %', 'label' => 'de satisfaction client sur la dernière année'],
        ];
    }
}

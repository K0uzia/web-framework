<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait ServicesDefaults
{
    private static function servicesContent(string $variant): array
    {
        return match ($variant) {
            'services12' => [
                'title' => 'Services à la une',
                'subtitle' => 'Nous proposons des solutions digitales complètes pour faire grandir votre activité. Du web au mobile, nous livrons des résultats de qualité.',
                'buttons' => [
                    ['label' => 'Voir tous les services', 'href' => '#', 'style' => 'secondary'],
                ],
                'items' => self::services12Items(),
            ],
            default => [
                'title' => 'Services',
                'subtitle' => 'Nous créons des expériences digitales qui captivent et convertissent, en donnant vie à votre vision.',
                'items' => self::services4Items(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function services4Items(): array
    {
        return [
            [
                'title' => 'Stratégie produit',
                'text' => 'Planification stratégique et positionnement marché pour aligner votre produit sur les besoins utilisateurs et vos objectifs business.',
                'icon' => 'fa-gear',
                'label' => 'Étude de marché, Personas, Analyse concurrentielle',
            ],
            [
                'title' => 'Design',
                'text' => 'Des interfaces soignées et centrées utilisateur pour des expériences engageantes sur tous les supports.',
                'icon' => 'fa-palette',
                'label' => 'UI/UX, Prototypage, Design d\'interaction',
            ],
            [
                'title' => 'Développement web',
                'text' => 'Applications web modernes et évolutives, construites avec les technologies et bonnes pratiques actuelles.',
                'icon' => 'fa-code',
                'label' => 'Frontend, Backend, Intégration API',
            ],
            [
                'title' => 'Marketing',
                'text' => 'Stratégies data-driven pour lancer efficacement et faire évoluer votre produit.',
                'icon' => 'fa-leaf',
                'label' => 'SEO, Analytics, Tests A/B',
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function services12Items(): array
    {
        $images = [
            SectionAssets::shared(self::FEATURES_SHARED, 'lummi/bw12.jpeg'),
            SectionAssets::shared(self::FEATURES_SHARED, 'lummi/bw15.jpeg'),
            SectionAssets::shared(self::FEATURES_SHARED, 'lummi/bw20.jpeg'),
            SectionAssets::shared(self::FEATURES_SHARED, 'lummi/bw21.jpeg'),
            SectionAssets::shared(self::FEATURES_SHARED, 'images/1-1x1.jpg'),
        ];
        $titles = [
            'Développement web',
            'Applications mobiles',
            'Design UI/UX',
            'Marketing digital',
            'Solutions cloud',
        ];

        $items = [];
        foreach ($titles as $index => $title) {
            $items[] = [
                'title' => $title,
                'url' => $images[$index],
                'href' => '#',
            ];
        }

        return $items;
    }
}

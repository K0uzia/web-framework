<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

trait CompareDefaults
{
    private static function compareContent(string $variant): array
    {
        $base = [
            'title' => $variant === 'compare8' ? 'Comparez-nous' : 'Comparer',
            'subtitle' => 'Un framework moderne pour créer des sites, plus performant que la concurrence.',
            'primary_label' => 'Notre solution',
            'secondary_label' => 'Alternative',
            'items' => $variant === 'compare8' ? self::compare8Items() : self::compare7Items(),
        ];

        return $base;
    }

    /**
     * @return list<array<string, string>>
     */
    private static function compare7Items(): array
    {
        return [
            [
                'title' => 'Système de design',
                'label' => 'Moderne, utility-first',
                'text' => 'Classique, orienté composants',
            ],
            [
                'title' => 'Personnalisation',
                'label' => 'Très personnalisable',
                'text' => 'Limitée par défaut',
            ],
            [
                'title' => 'Mode sombre',
                'label' => 'Intégré',
                'text' => 'Configuration supplémentaire',
            ],
            [
                'title' => 'Support TypeScript',
                'label' => 'Natif',
                'text' => 'Partiel',
            ],
            [
                'title' => 'Accessibilité',
                'label' => 'Priorité a11y',
                'text' => 'Basique',
            ],
            [
                'title' => 'Nombre de composants',
                'label' => '30+',
                'text' => '25+',
            ],
            [
                'title' => 'Licence',
                'label' => 'MIT',
                'text' => 'MIT',
            ],
            [
                'title' => 'Composants premium',
                'label' => 'Disponibles',
                'text' => 'Non inclus',
                'tooltip_title' => 'Réservé au premium',
                'tooltip_text' => 'Certains composants avancés ne sont disponibles que dans des offres payantes ou via des bibliothèques tierces.',
            ],
            [
                'title' => 'Kit Figma',
                'label' => 'Oui',
                'text' => 'Non',
                'tooltip_title' => 'Kit Figma indisponible',
                'tooltip_text' => 'L\'alternative ne fournit pas de kit Figma officiel, mais des kits communautaires peuvent exister.',
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function compare8Items(): array
    {
        return [
            [
                'title' => 'Système de design',
                'text' => 'Moderne et utility-first face à une approche classique par composants.',
                'icon' => 'fa-table-columns',
                'primary' => 'oui',
                'secondary' => 'oui',
            ],
            [
                'title' => 'Personnalisation',
                'text' => 'Très personnalisable face à des options limitées par défaut.',
                'icon' => 'fa-gear',
                'primary' => 'oui',
                'secondary' => 'non',
            ],
            [
                'title' => 'Mode sombre',
                'text' => 'Mode sombre intégré face à une configuration supplémentaire.',
                'icon' => 'fa-moon',
                'primary' => 'oui',
                'secondary' => 'non',
            ],
            [
                'title' => 'Support TypeScript',
                'text' => 'Support TypeScript natif face à un support partiel.',
                'icon' => 'fa-font',
                'primary' => 'oui',
                'secondary' => 'partiel',
            ],
            [
                'title' => 'Accessibilité',
                'text' => 'Focus accessibilité (a11y) face à un support basique.',
                'icon' => 'fa-universal-access',
                'primary' => 'oui',
                'secondary' => 'non',
            ],
            [
                'title' => 'Nombre de composants',
                'text' => 'Plus de 30 composants face à plus de 25.',
                'icon' => 'fa-list-check',
                'primary' => 'oui',
                'secondary' => 'oui',
            ],
            [
                'title' => 'Licence',
                'text' => 'Licence MIT pour les deux solutions.',
                'icon' => 'fa-certificate',
                'primary' => 'oui',
                'secondary' => 'oui',
            ],
            [
                'title' => 'Composants premium',
                'text' => 'Composants premium disponibles avec notre solution, pas avec l\'alternative.',
                'icon' => 'fa-gem',
                'primary' => 'oui',
                'secondary' => 'non',
                'tooltip_title' => 'Réservé au premium',
                'tooltip_text' => 'Certains composants avancés ne sont disponibles que dans des offres payantes.',
            ],
            [
                'title' => 'Kit Figma',
                'text' => 'Kit Figma officiel disponible pour notre solution, pas pour l\'alternative.',
                'icon' => 'fa-pen-ruler',
                'primary' => 'oui',
                'secondary' => 'non',
                'tooltip_title' => 'Kit Figma indisponible',
                'tooltip_text' => 'L\'alternative ne fournit pas de kit Figma officiel.',
            ],
        ];
    }
}

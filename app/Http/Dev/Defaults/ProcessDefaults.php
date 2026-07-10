<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

trait ProcessDefaults
{
    /**
     * @return array<string, mixed>
     */
    private static function processContent(string $variant): array
    {
        return match ($variant) {
            default => [
                'title' => 'Notre processus',
                'subtitle' => 'Une méthode claire pour comprendre vos enjeux, concevoir la bonne solution et l\'améliorer dans la durée.',
                'buttons' => [[
                    'label' => 'Nous contacter',
                    'href' => '#',
                    'style' => 'secondary',
                ]],
                'items' => self::process1Items(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function process1Items(): array
    {
        return [
            [
                'label' => '01',
                'title' => 'Découverte et analyse',
                'text' => 'Nous commençons par comprendre vos objectifs, votre audience et vos contraintes. Cette phase combine recherche, analyse et cadrage stratégique.',
            ],
            [
                'label' => '02',
                'title' => 'Stratégie et planification',
                'text' => 'À partir de nos conclusions, nous définissons une feuille de route alignée sur vos priorités, avec jalons et calendrier.',
            ],
            [
                'label' => '03',
                'title' => 'Exécution et développement',
                'text' => 'Nous mettons la stratégie en œuvre avec une attention constante aux détails et à la qualité attendue.',
            ],
            [
                'label' => '04',
                'title' => 'Optimisation et amélioration',
                'text' => 'Nous suivons les résultats, recueillons les retours et affinons la solution pour garantir une performance durable.',
            ],
        ];
    }
}

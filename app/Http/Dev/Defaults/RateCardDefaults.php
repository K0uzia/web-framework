<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

trait RateCardDefaults
{
    /**
     * @return array<string, mixed>
     */
    private static function rateCardContent(string $variant): array
    {
        return match ($variant) {
            default => [
                'features_heading' => 'Inclus :',
                'cta_label' => 'Commencer',
                'items' => self::rateCard2Items(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function rateCard2Items(): array
    {
        return [
            [
                'title' => 'Retainer mensuel',
                'text' => 'Accompagnement continu pour vos projets prioritaires, avec une équipe dédiée et un suivi régulier.',
                'price' => '3 499 €',
                'period' => '/mois',
                'features' => "Jusqu'à 2 demandes actives en parallèle\nRévisions illimitées incluses\nSupport prioritaire 24 h/24\nResponsable de compte dédié",
                'cta_label' => 'Commencer',
                'href' => '#',
            ],
            [
                'title' => 'Pack premium',
                'text' => 'Pour les équipes ambitieuses qui veulent accélérer la livraison et bénéficier d\'un accompagnement stratégique renforcé.',
                'price' => '5 999 €',
                'period' => '/mois',
                'features' => "Jusqu'à 5 demandes actives en parallèle\nTableau de bord analytique avancé\nIntégrations sur mesure\nConsultations stratégiques hebdomadaires",
                'cta_label' => 'Commencer',
                'href' => '#',
            ],
        ];
    }
}

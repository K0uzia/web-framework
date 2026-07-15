<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

use Capsule\SectionAssets;

trait IndustryDefaults
{
    /**
     * @return array<string, mixed>
     */
    private static function industryContent(string $variant): array
    {
        return match ($variant) {
            'industries2' => [
                'badge' => 'Secteurs',
                'title' => 'Transformer les secteurs grâce à des solutions technologiques innovantes qui favorisent l\'efficacité, la croissance et des opérations durables.',
                'items' => self::industries2Items(),
            ],
            default => [
                'title' => 'Secteurs',
                'overview_label' => 'Aperçu',
                'items' => self::industries1Items(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function industries1Items(): array
    {
        return [
            [
                'title' => 'Santé',
                'text' => 'Solutions médicales et plateformes de santé numérique qui améliorent les résultats patients et fluidifient les parcours de soins.',
                'url' => SectionAssets::shared('features', 'placeholder-1.svg'),
                'image_alt' => 'Illustration santé',
                'href' => '#',
            ],
            [
                'title' => 'Fintech',
                'text' => 'Technologies financières qui transforment la banque, les paiements et la gestion d\'investissement à l\'ère numérique.',
                'url' => SectionAssets::shared('features', 'placeholder-2.svg'),
                'image_alt' => 'Illustration fintech',
                'href' => '#',
            ],
            [
                'title' => 'E-commerce',
                'text' => 'Plateformes retail et marketplaces qui stimulent les ventes et enrichissent l\'expérience client.',
                'url' => SectionAssets::shared('features', 'placeholder-3.svg'),
                'image_alt' => 'Illustration e-commerce',
                'href' => '#',
            ],
            [
                'title' => 'Éducation',
                'text' => 'Systèmes d\'apprentissage et EdTech qui accompagnent étudiants et enseignants partout dans le monde.',
                'url' => SectionAssets::shared('features', 'placeholder-4.svg'),
                'image_alt' => 'Illustration éducation',
                'href' => '#',
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function industries2Items(): array
    {
        return [
            [
                'title' => 'Mines',
                'text' => 'Automatisation avancée, supervision en temps réel, sécurité et optimisation des ressources pour des opérations minières plus efficaces et durables.',
                'url' => SectionAssets::shared('features', 'placeholder-1.svg'),
                'image_alt' => 'Icône mines',
            ],
            [
                'title' => 'Finance',
                'text' => 'Plateformes bancaires digitales, paiements, gestion des risques et conformité réglementaire pour innover dans un secteur exigeant.',
                'url' => SectionAssets::shared('features', 'placeholder-2.svg'),
                'image_alt' => 'Icône finance',
            ],
            [
                'title' => 'Énergie',
                'text' => 'Smart grids, gestion des énergies renouvelables, maintenance prédictive et prévision de la demande pour optimiser les ressources.',
                'url' => SectionAssets::shared('features', 'placeholder-3.svg'),
                'image_alt' => 'Icône énergie',
            ],
            [
                'title' => 'Construction',
                'text' => 'Gestion de projet, intégration BIM, collaboration en temps réel et suivi sécurité pour réduire les coûts et les délais.',
                'url' => SectionAssets::shared('features', 'placeholder-4.svg'),
                'image_alt' => 'Icône construction',
            ],
        ];
    }
}
